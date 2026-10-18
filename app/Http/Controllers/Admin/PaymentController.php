<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RedeemCode;
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

        $query = Order::with(['user', 'product'])->latest();
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
        $order->load(['user', 'product', 'discountCode', 'redeemCode']);

        $waText = null;
        if ($order->status === Order::ST_DISETUJUI && $order->redeemCode) {
            $waText = "Halo {$order->user->name}! Terima kasih sudah membeli \"{$order->product->judul}\" di Rumah Cahaya-bks. 🎉\n\nKode redeem kamu: *{$order->redeemCode->kode}*\n\nCara pakai:\n1. Masuk ke akunmu\n2. Buka Home, masukkan kode di kolom Redeem\n3. Klik Redeem — produk langsung jadi milikmu!\n\nSelamat belajar dan bermain! 📚";
        }

        return view('admin.pembayaran.show', [
            'order' => $order,
            'waLink' => $waText && $order->user->whatsapp
                ? 'https://wa.me/'.$order->user->whatsapp.'?text='.urlencode($waText)
                : null,
        ]);
    }

    /**
     * Setujui: generate redeem code + kurangi stok (PRD §6.6).
     */
    public function approve(Order $order): RedirectResponse
    {
        if ($order->status !== Order::ST_VERIFIKASI) {
            return back()->with('gagal', 'Hanya order menunggu verifikasi yang bisa disetujui.');
        }

        $redeem = RedeemCode::create([
            'kode' => RedeemCode::generate(),
            'order_id' => $order->id,
            'product_id' => $order->product_id,
        ]);

        $order->update(['status' => Order::ST_DISETUJUI]);

        $product = $order->product;
        if ($product->stok !== null && $product->stok > 0) {
            $product->decrement('stok');
        }

        if ($order->discountCode) {
            $order->discountCode->increment('terpakai');
        }

        \App\Models\ActivityLog::catat('verifikasi_order', "Order {$order->kode_order} ({$order->product->judul}) disetujui — kode {$redeem->kode}");

        return redirect()->route('admin.pembayaran.show', $order)
            ->with('sukses', "Order disetujui! Kode redeem {$redeem->kode} dibuat. Kirim ke pengguna via tombol WhatsApp di bawah.");
    }

    /**
     * Tolak + alasan; pengguna boleh upload ulang 1x (PRD §5.4).
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

        return redirect()->route('admin.pembayaran.show', $order)
            ->with('sukses', 'Order ditolak. Pengguna akan melihat alasan dan bisa upload ulang 1x.');
    }
}
