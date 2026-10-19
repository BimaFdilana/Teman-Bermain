<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Guest checkout via Iklan (Laporan Final 19 Okt 2026):
 * cart session (tanpa login) -> form checkout -> bayar -> terima kasih.
 */
class CartController extends Controller
{
    /** Ambil isi cart dari session: [product_id, ...]. */
    public static function isi(Request $request): array
    {
        return array_values(array_unique($request->session()->get('cart', [])));
    }

    /** Halaman cart: produk dipilih + rekomendasi + ringkasan. */
    public function index(Request $request, string $slug): View|RedirectResponse
    {
        $produk = Product::tersedia()->where('slug', $slug)->firstOrFail();
        $this->tambahKeCart($request, $produk);

        $ids = self::isi($request);
        $items = Product::tersedia()->whereIn('id', $ids)->get();
        // Bersihkan id yang sudah tidak tersedia
        if ($items->count() !== count($ids)) {
            $request->session()->put('cart', $items->pluck('id')->all());
        }

        return view('cart.index', [
            'items' => $items,
            'rekomendasi' => $this->rekomendasi($request, $items),
            'ringkasan' => $this->ringkasan($request, $items),
        ]);
    }

    public function tambah(Request $request, string $slug): RedirectResponse
    {
        $produk = Product::tersedia()->where('slug', $slug)->firstOrFail();
        $this->tambahKeCart($request, $produk);

        return back()->with('sukses', "\"{$produk->judul}\" masuk keranjang.");
    }

    public function hapus(Request $request, string $slug): RedirectResponse
    {
        $produk = Product::where('slug', $slug)->firstOrFail();
        $cart = array_values(array_diff(self::isi($request), [$produk->id]));
        $request->session()->put('cart', $cart);

        return back()->with('sukses', "\"{$produk->judul}\" dihapus dari keranjang.");
    }

    private function tambahKeCart(Request $request, Product $produk): void
    {
        // Produk yang sudah dimiliki tidak bisa dibeli lagi (BR-RC-10)
        if ($request->user()?->hasProduct($produk->id)) {
            return;
        }
        $cart = self::isi($request);
        if (! in_array($produk->id, $cart)) {
            $cart[] = $produk->id;
            $request->session()->put('cart', $cart);
        }
    }

    /** Rekomendasi: terkait, terbaru, promo — di luar cart & milik. */
    private function rekomendasi(Request $request, $items)
    {
        $punya = $request->user() ? $request->user()->entitlements()->pluck('product_id')->all() : [];
        $kecuali = array_merge($items->pluck('id')->all(), $punya);

        return Product::tersedia()
            ->whereNotIn('id', $kecuali)
            ->orderByRaw('(harga_coret IS NOT NULL) DESC')
            ->latest()
            ->take(4)
            ->get();
    }

    /** Ringkasan: subtotal, diskon, pajak, total. */
    public static function ringkasan(Request $request, $items): array
    {
        $subtotal = $items->sum('harga');
        $diskon = 0;
        $kode = $request->session()->get('cart_diskon');

        if ($kode) {
            $dc = DiscountCode::where('kode', strtoupper($kode))->first();
            if ($dc && $dc->berlaku()) {
                $diskon = $subtotal - $dc->hitungTotal($subtotal);
            } else {
                $request->session()->forget('cart_diskon');
            }
        }

        $pajakPersen = (float) (Setting::get('pajak_persen', '0') ?? 0);
        $pajak = (int) round(($subtotal - $diskon) * $pajakPersen / 100);

        return [
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'kode_diskon' => $kode,
            'pajak_persen' => $pajakPersen,
            'pajak' => $pajak,
            'total' => $subtotal - $diskon + $pajak,
            'jumlah' => $items->count(),
        ];
    }

    /** Terapkan kode diskon ke cart. */
    public function diskon(Request $request): RedirectResponse
    {
        $kode = strtoupper(trim($request->input('kode_diskon', '')));
        $dc = $kode ? DiscountCode::where('kode', $kode)->first() : null;

        if (! $dc || ! $dc->berlaku()) {
            return back()->with('gagal', 'Kode diskon tidak valid atau sudah tidak berlaku.');
        }
        $request->session()->put('cart_diskon', $kode);

        return back()->with('sukses', "Kode {$kode} diterapkan.");
    }

    /** Form checkout: nama, email, WA, metode bayar. */
    public function checkout(Request $request): View|RedirectResponse
    {
        $items = Product::tersedia()->whereIn('id', self::isi($request))->get();
        if ($items->isEmpty()) {
            return redirect()->route('landing')->with('info', 'Keranjang masih kosong.');
        }

        $banks = $this->daftarBank();

        return view('cart.checkout', [
            'items' => $items,
            'ringkasan' => self::ringkasan($request, $items),
            'banks' => $banks,
            'user' => $request->user(),
        ]);
    }

    /** Proses checkout: buat order + items. */
    public function store(Request $request): RedirectResponse
    {
        $items = Product::tersedia()->whereIn('id', self::isi($request))->get();
        if ($items->isEmpty()) {
            return redirect()->route('landing')->with('info', 'Keranjang masih kosong.');
        }

        $banks = array_keys($this->daftarBank());

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'whatsapp' => ['required', 'string', 'max:20'],
            'metode_bayar' => ['required', 'in:qris,va'],
            'bank_va' => ['required_if:metode_bayar,va', 'in:'.implode(',', $banks)],
        ]);

        // Normalisasi No WA ke format 62...
        $wa = preg_replace('/\D/', '', $data['whatsapp']);
        if (str_starts_with($wa, '0')) $wa = '62'.substr($wa, 1);
        if (! str_starts_with($wa, '62')) $wa = '62'.$wa;

        // Cek stok & kepemilikan per item
        foreach ($items as $p) {
            if ($p->stok !== null && $p->stok <= 0) {
                return back()->with('gagal', "Stok \"{$p->judul}\" habis.")->withInput();
            }
            if ($request->user()?->hasProduct($p->id)) {
                return back()->with('gagal', "Kamu sudah memiliki \"{$p->judul}\".")->withInput();
            }
        }

        $ringkasan = self::ringkasan($request, $items);
        $discount = null;
        if ($ringkasan['kode_diskon']) {
            $discount = DiscountCode::where('kode', $ringkasan['kode_diskon'])->first();
        }

        $order = Order::create([
            'kode_order' => CheckoutController::generateKodeOrder(),
            'user_id' => $request->user()?->id,
            'guest_nama' => $request->user()?->name ?? $data['nama'],
            'guest_email' => $request->user()?->email ?? $data['email'],
            'guest_wa' => $request->user()?->whatsapp ?? $wa,
            'metode_bayar' => $data['metode_bayar'],
            'bank_va' => $data['metode_bayar'] === 'va' ? $data['bank_va'] : null,
            'product_id' => $items->count() === 1 ? $items->first()->id : null,
            'total_bayar' => $ringkasan['total'],
            'discount_code_id' => $discount?->id,
            'batas_bayar' => now()->addDay(),
        ]);

        foreach ($items as $p) {
            $order->items()->create([
                'product_id' => $p->id,
                'harga_saat_beli' => $p->harga,
                'qty' => 1,
            ]);
        }

        // Kosongkan cart; ingat order untuk akses halaman bayar (tamu)
        $request->session()->forget(['cart', 'cart_diskon']);
        $request->session()->put('order_terakhir', $order->id);

        return redirect()->route('checkout.bayar', $order->kode_order);
    }

    /** Halaman terima kasih setelah upload bukti. */
    public function terimaKasih(Request $request, string $kode_order): View
    {
        $order = Order::where('kode_order', $kode_order)->firstOrFail();
        $this->otorisasiOrder($request, $order);

        return view('cart.terima-kasih', compact('order'));
    }

    /** Order boleh dibuka pemilik login atau tamu pemegang session. */
    public static function otorisasiOrder(Request $request, Order $order): void
    {
        $boleh = ($request->user() && $order->user_id === $request->user()->id)
            || (int) $request->session()->get('order_terakhir') === $order->id;

        abort_unless($boleh, 404);
    }

    private function daftarBank(): array
    {
        $va = json_decode(Setting::get('va_numbers', '{}') ?? '{}', true) ?: [];
        $nama = ['BCA' => 'BCA', 'BRI' => 'BRI', 'MANDIRI' => 'Mandiri', 'BNI' => 'BNI'];
        // Hanya bank yang nomor VA-nya sudah diisi admin
        return array_filter($nama, fn ($b) => ! empty($va[$b]));
    }
}
