<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\KirimWaJob;
use App\Mail\KodeRedeemMail;
use App\Models\Order;
use App\Models\RedeemCode;
use App\Services\MetaConversionsApi;
use App\Services\WablasService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Daftar order, default yang menunggu verifikasi (PRD §6.6).
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'verifikasi');
        $valid = ['pembayaran', 'verifikasi', 'ditolak', 'disetujui', 'kadaluarsa', 'semua'];

        if (! in_array($status, $valid)) {
            $status = 'verifikasi';
        }

        $query = Order::with(['user', 'product', 'items.product'])->latest();
        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        $counts = [
            'verifikasi' => Order::where('status', 'verifikasi')->count(),
            'pembayaran' => Order::where('status', 'pembayaran')->count(),
        ];

        return view('admin.pembayaran.index', [
            'orders' => $query->paginate(15)->withQueryString(),
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items.product', 'discountCode', 'redeemCodes.product']);

        $waLink = null;
        $waGagalLink = null;
        $wa = $order->waPembeli();
        $nama = $order->namaPembeli();
        $email = $order->emailPembeli() ?? '-';

        if ($order->status === Order::ST_DISETUJUI && $order->redeemCodes->isNotEmpty() && $wa) {
            $barisKode = $order->redeemCodes->map(
                fn ($r) => $r->kode.' — untuk '.$r->product->judul
            )->implode("\n");
            $daftarProduk = $order->redeemCodes->map(fn ($r) => $r->product->judul)->implode(', ');
            $waText = "Halo {$nama}! Terima kasih sudah membeli di Rumah Cahaya-bks 🎉\n\n"
                ."📦 Detail pesanan:\n"
                ."• No. Order/Invoice: {$order->kode_order}\n"
                ."• Produk: {$daftarProduk}\n"
                ."• Tanggal pesanan: {$order->created_at->format('d M Y, H:i')}\n\n"
                ."🔑 Kode redeem kamu:\n{$barisKode}\n\n"
                ."Cara klaim:\n"
                ."1. Login di ".config('app.url')." pakai Google (email: {$email})\n"
                ."2. Buka Home, masukkan kode redeem di kolom Redeem\n"
                ."3. Klik Redeem — produk langsung jadi milikmu!\n\n"
                ."Jika ada kendala, support kami selalu standby membalas pertanyaanmu 💬";
            $waLink = 'https://wa.me/'.$wa.'?text='.urlencode($waText);
        }

        if ($order->status === Order::ST_DITOLAK && $wa) {
            $waGagal = "Halo {$nama}, terima kasih sudah memesan di Rumah Cahaya-bks.\n\n"
                ."Mohon maaf, pembayaran untuk No. Order {$order->kode_order} belum bisa kami verifikasi: {$order->alasan_tolak}\n\n"
                ."Silakan upload ulang bukti transfer yang benar, atau hubungi kami jika butuh bantuan 💬";
            $waGagalLink = 'https://wa.me/'.$wa.'?text='.urlencode($waGagal);
        }

        return view('admin.pembayaran.show', [
            'order' => $order,
            'waLink' => $waLink,
            'waGagalLink' => $waGagalLink,
        ]);
    }

    /**
     * Setujui: generate 1 redeem code per item + kurangi stok per item (Laporan Final 19 Okt 2026).
     * Menerima status verifikasi DAN ditolak: tolak tidak memaksa order ulang —
     * setelah chat WA, admin menyetujui order yang sama (keputusan 19 Okt 2026).
     */
    public function approve(Order $order): RedirectResponse
    {
        if (! in_array($order->status, [Order::ST_VERIFIKASI, Order::ST_DITOLAK])) {
            return back()->with('gagal', 'Hanya order menunggu verifikasi atau ditolak yang bisa disetujui.');
        }

        $order->loadMissing(['items.product']);

        $kodes = [];
        foreach ($order->items as $item) {
            $redeem = RedeemCode::create([
                'kode' => RedeemCode::generate(),
                'order_id' => $order->id,
                'product_id' => $item->product_id,
            ]);
            $kodes[] = $redeem->kode.' — '.$item->product->judul;

            $product = $item->product;
            if ($product->stok !== null && $product->stok > 0) {
                $product->decrement('stok');
            }
        }

        // Kompatibilitas: order lama tanpa items
        if ($order->items->isEmpty() && $order->product_id) {
            $redeem = RedeemCode::create([
                'kode' => RedeemCode::generate(),
                'order_id' => $order->id,
                'product_id' => $order->product_id,
            ]);
            $kodes[] = $redeem->kode.' — '.$order->product->judul;
            $product = $order->product;
            if ($product->stok !== null && $product->stok > 0) {
                $product->decrement('stok');
            }
        }

        $order->update(['status' => Order::ST_DISETUJUI]);

        // Conversions API: Purchase server (satu-satunya Purchase; browser
        // berhenti di InitiateCheckout). Gagal kirim tidak menggagalkan approval.
        try {
            app(MetaConversionsApi::class)->purchase($order);
        } catch (\Throwable $e) {
            Log::warning('Meta CAPI hook approve gagal: '.$e->getMessage(), [
                'kode_order' => $order->kode_order,
            ]);
        }

        if ($order->discountCode) {
            $order->discountCode->increment('terpakai');
        }

        \App\Models\ActivityLog::catat('verifikasi_order', "Order {$order->kode_order} disetujui (".count($kodes)." kode)");

        // Notifikasi otomatis: kode redeem via WA (+email antrean). Tombol
        // wa.me manual di halaman detail tetap jadi fallback bila Wablas gagal.
        $waPelanggan = $order->waPembeli();
        if (WablasService::enabled() && $waPelanggan) {
            $daftarKode = implode("\n", $kodes);
            KirimWaJob::dispatch($waPelanggan,
                "Halo {$order->namaPembeli()}! Terima kasih sudah membeli di Rumah Cahaya-bks 🎉\n\n"
                ."📦 No. Order/Invoice: {$order->kode_order}\n\n"
                ."🔑 Kode redeem kamu:\n{$daftarKode}\n\n"
                .'Cara klaim:'."\n"
                .'1. Login di '.config('app.url').' pakai Google (email: '.$order->emailPembeli().")\n"
                ."2. Buka Home, masukkan kode redeem di kolom Redeem\n"
                .'3. Klik Redeem — produk langsung jadi milikmu!');
        }
        if ($order->emailPembeli()) {
            try {
                Mail::to($order->emailPembeli())->queue(new KodeRedeemMail($order, $kodes));
            } catch (\Throwable $e) {
                Log::warning('Email kode redeem gagal diantrekan: '.$e->getMessage(), [
                    'kode_order' => $order->kode_order,
                ]);
            }
        }

        $pesan = 'Order disetujui! '.count($kodes).' kode redeem dibuat.';
        $pesan .= WablasService::enabled()
            ? ' Kode redeem dikirim otomatis via WA (+email) ke pelanggan. Tombol "Kirim Produk" tetap tersedia bila perlu kirim ulang manual.'
            : ' Kirim ke pelanggan via tombol "Kirim Produk" di bawah.';

        return redirect()->route('admin.pembayaran.show', $order)->with('sukses', $pesan);
    }

    /**
     * Tolak + alasan; alasan terkirim otomatis via WA pelanggan.
     * Pengguna boleh upload ulang 1x (PRD §5.4) ATAU admin menyetujui order
     * yang sama setelah chat WA (tidak perlu order ulang).
     */
    public function reject(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== Order::ST_VERIFIKASI) {
            return back()->with('gagal', 'Hanya order menunggu verifikasi yang bisa ditolak.');
        }

        $request->validate(['alasan_tolak' => ['required', 'string', 'max:500']]);

        $order->update([
            'status' => Order::ST_DITOLAK,
            'alasan_tolak' => $request->alasan_tolak,
        ]);

        \App\Models\ActivityLog::catat('tolak_order', "Order {$order->kode_order} ({$order->product->judul}) ditolak: {$request->alasan_tolak}");

        // Alasan penolakan terkirim otomatis via WA pelanggan (antrean).
        if (WablasService::enabled() && ($waPelanggan = $order->waPembeli())) {
            KirimWaJob::dispatch($waPelanggan,
                "Halo {$order->namaPembeli()}, terima kasih sudah memesan di Rumah Cahaya-bks.\n\n"
                ."Mohon maaf, pembayaran untuk No. Order {$order->kode_order} belum bisa kami verifikasi: {$request->alasan_tolak}\n\n"
                .'Silakan chat admin via WA ini untuk tindak lanjut — kamu tidak perlu order ulang, admin bisa menyetujui order yang sama setelah dikonfirmasi.');
        }

        $pesan = 'Order ditolak. Pengguna akan melihat alasan dan bisa upload ulang 1x.';
        if (WablasService::enabled()) {
            $pesan .= ' Alasan juga dikirim via WA pelanggan.';
        }

        return redirect()->route('admin.pembayaran.show', $order)
            ->with('sukses', $pesan);
    }
}
