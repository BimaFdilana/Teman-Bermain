<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Katalog produk (PRD §4.3): filter tipe, jenjang, pencarian.
     */
    public function index(Request $request): View
    {
        $query = Product::tersedia()->with('bundle.items');

        if ($request->filled('tipe') && in_array($request->tipe, ['ebook', 'miniapp', 'bundle'])) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('jenjang')) {
            $query->where('jenjang', $request->jenjang);
        }
        if ($request->filled('q')) {
            $query->where('judul', 'like', '%'.$request->q.'%');
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $jenjangs = Product::tersedia()->select('jenjang')->distinct()->pluck('jenjang')->filter();

        return view('produk.index', [
            'products' => $products,
            'jenjangs' => $jenjangs,
            'filters' => $request->only(['tipe', 'jenjang', 'q']),
        ]);
    }

    /**
     * Detail produk (PRD §4.3).
     */
    public function show(string $slug): View
    {
        $product = Product::tersedia()->where('slug', $slug)->with('bundle.items')->firstOrFail();
        $terkait = Product::tersedia()
            ->where('id', '!=', $product->id)
            ->where('tipe', $product->tipe)
            ->take(3)->get();

        return view('produk.show', [
            'product' => $product,
            'terkait' => $terkait,
        ]);
    }
}
