<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman promosi per produk ala link-in-bio (PRD tambahan 2026-10-18).
 * Link: /p/{slug} — dibagikan ke sosmed, tiap produk punya 1 link promosi.
 * Alur: Pesan Sekarang → wajib login Google dulu → lanjut ke pembayaran.
 */
class PromoController extends Controller
{
    /**
     * Tampilkan halaman promo produk.
     */
    public function show(string $slug): View
    {
        $product = Product::tersedia()->where('slug', $slug)->firstOrFail();

        return view('promo.show', ['product' => $product]);
    }

    /**
     * Tombol "Pesan Sekarang": tamu diarahkan login Google dulu,
     * setelah login otomatis lanjut membuat order (intended URL).
     */
    public function pesan(Request $request, string $slug): RedirectResponse
    {
        $product = Product::tersedia()->where('slug', $slug)->firstOrFail();

        if (auth()->guest()) {
            session(['url.intended' => route('promo.pesan', $product->slug)]);
            return redirect()->route('google.redirect');
        }

        // Sudah login: teruskan ke logika checkout yang sudah ada & teruji.
        return app(CheckoutController::class)->store($request, $slug);
    }
}
