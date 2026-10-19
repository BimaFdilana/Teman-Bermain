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
     * Halaman iklan/promo multi-produk dengan hitungan mundur.
     * Draft & belum mulai → 404; sudah berakhir → tampil status berakhir.
     */
    public function iklan(string $slug): View
    {
        $kampanye = \App\Models\PromoCampaign::where('slug', $slug)->firstOrFail();

        if ($kampanye->status !== \App\Models\PromoCampaign::STATUS_AKTIF) {
            abort(404);
        }
        if ($kampanye->mulai_at && now()->lt($kampanye->mulai_at)) {
            abort(404);
        }
        $kampanye->load([
            'produkPrimary' => fn ($q) => $q->tersedia(),
            'produkSecondary' => fn ($q) => $q->tersedia(),
        ]);
        $testimonis = \App\Models\Testimonial::where('aktif', true)->orderBy('urutan')->take(3)->get();

        return view('promo.iklan', ['kampanye' => $kampanye, 'testimonis' => $testimonis]);
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
