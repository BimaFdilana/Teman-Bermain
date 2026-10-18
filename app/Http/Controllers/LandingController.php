<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Beranda publik (PRD §4.2).
     */
    public function index(): View
    {
        $paketUnggulan = Product::tersedia()->where('tipe', 'bundle')->take(3)->get();
        $produkPopuler = Product::tersedia()->whereIn('tipe', ['ebook', 'miniapp'])->take(4)->get();
        $testimonials = Testimonial::aktif()->get();

        return view('landing.index', [
            'paketUnggulan' => $paketUnggulan,
            'produkPopuler' => $produkPopuler,
            'testimonials' => $testimonials,
            'heroHeadline' => Setting::get('hero_headline', 'Belajar Jadi Petualangan Seru!'),
            'heroSubheadline' => Setting::get('hero_subheadline', ''),
        ]);
    }

    /**
     * Halaman Harga: list bundle (PRD §4.4).
     */
    public function harga(): View
    {
        $bundles = Product::tersedia()->where('tipe', 'bundle')->with('bundle.items')->get();

        return view('landing.harga', ['bundles' => $bundles]);
    }
}
