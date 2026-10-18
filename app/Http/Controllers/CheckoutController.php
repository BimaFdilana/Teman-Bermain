<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Buat order baru dari halaman detail produk (PRD §5.4).
     */
    public function store(Request $request, string $slug): RedirectResponse
    {
        $product = Product::tersedia()->where('slug', $slug)->firstOrFail();
        $user = $request->user();

        if ($user->hasProduct($product->id)) {
            return redirect()->route('produk.show', $slug)
                ->with('info', 'Kamu sudah memiliki produk ini. Cek di Home ya!');
        }

        if ($product->stok !== null && $product->stok <= 0) {
            return redirect()->route('produk.show', $slug)
                ->with('info', 'Stok produk ini habis.');
        }

        // Cegah order ganda yang masih aktif untuk produk yang sama
        $aktif = Order::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->whereIn('status', [Order::ST_PEMBAYARAN, Order::ST_VERIFIKASI])
            ->first();
        if ($aktif) {
            return redirect()->route('checkout.bayar', $aktif->kode_order);
        }

        $total = $product->harga;
        $discount = null;
        if ($request->filled('kode_diskon')) {
            $discount = DiscountCode::where('kode', strtoupper(trim($request->kode_diskon)))->first();
            if (! $discount || ! $discount->berlaku()) {
                return redirect()->route('produk.show', $slug)
                    ->with('info', 'Kode diskon tidak valid atau sudah tidak berlaku.');
            }
            $total = $discount->hitungTotal($product->harga);
        }

        $order = Order::create([
            'kode_order' => self::generateKodeOrder(),
            'user_id' => $user->id,
            'product_id' => $product->id,
            'total_bayar' => $total,
            'discount_code_id' => $discount?->id,
            'batas_bayar' => now()->addDay(),
        ]);

        return redirect()->route('checkout.bayar', $order->kode_order);
    }

    /**
     * Halaman pembayaran: QRIS + nominal + upload bukti (PRD §5.4).
     */
    public function bayar(Request $request, string $kode_order): View
    {
        $order = $this->milikPengguna($request, $kode_order);

        return view('checkout.bayar', [
            'order' => $order,
            'qrisImage' => Setting::get('qris_image'),
        ]);
    }

    /**
     * Upload bukti pembayaran: JPG/PNG maks 2MB (PRD §5.4).
     */
    public function uploadBukti(Request $request, string $kode_order): RedirectResponse
    {
        $order = $this->milikPengguna($request, $kode_order);

        if (! $order->bolehUploadBukti()) {
            return back()->with('info', 'Bukti tidak dapat diunggah pada status ini.');
        }

        $request->validate([
            'bukti' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'bukti.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        if ($order->bukti_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($order->bukti_path);
        }

        $path = $request->file('bukti')->store('bukti', 'public');

        $order->update([
            'bukti_path' => $path,
            'upload_ke' => $order->upload_ke + 1,
            'status' => Order::ST_VERIFIKASI,
            'alasan_tolak' => null,
        ]);

        return redirect()->route('checkout.riwayat')
            ->with('sukses', 'Bukti pembayaran terkirim! Admin akan verifikasi maks. 1×24 jam.');
    }

    /**
     * Riwayat & lacak status pesanan pengguna.
     */
    public function riwayat(Request $request): View
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('product')
            ->latest()
            ->paginate(10);

        return view('checkout.riwayat', ['orders' => $orders]);
    }

    public function show(Request $request, string $kode_order): View
    {
        $order = $this->milikPengguna($request, $kode_order);

        return view('checkout.detail', [
            'order' => $order,
            'qrisImage' => Setting::get('qris_image'),
        ]);
    }

    /**
     * Pastikan order milik pengguna yang login (atau 404).
     */
    private function milikPengguna(Request $request, string $kode_order): Order
    {
        return Order::where('kode_order', $kode_order)
            ->where('user_id', $request->user()->id)
            ->with(['product', 'discountCode', 'redeemCode'])
            ->firstOrFail();
    }

    public static function generateKodeOrder(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        do {
            $rand = '';
            for ($i = 0; $i < 4; $i++) {
                $rand .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $kode = 'RCB-'.date('Ymd').'-'.$rand;
        } while (Order::where('kode_order', $kode)->exists());

        return $kode;
    }
}
