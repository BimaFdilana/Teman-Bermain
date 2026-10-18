<?php

namespace App\Http\Controllers;

use App\Models\Entitlement;
use App\Models\RedeemCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedeemController extends Controller
{
    /**
     * Redeem kode dari Home: beri entitlement produk (+ isi bundle).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:20']]);

        $kode = RedeemCode::normalize($request->input('code'));
        $redeem = RedeemCode::where('kode', $kode)->with('product.bundle.items')->first();

        if (! $redeem) {
            return back()->with('gagal', 'Kode tidak ditemukan. Periksa lagi kode dari WhatsApp ya.');
        }
        if ($redeem->status !== 'aktif') {
            return back()->with('gagal', 'Kode ini sudah dipakai.');
        }

        $user = $request->user();
        $product = $redeem->product;

        if ($user->hasProduct($product->id)) {
            return back()->with('gagal', 'Produk ini sudah menjadi milikmu.');
        }

        // Entitlement: produk utama + semua isi bundle (bila bundle)
        $productIds = [$product->id];
        if ($product->tipe === 'bundle' && $product->bundle) {
            foreach ($product->bundle->items as $item) {
                $productIds[] = $item->id;
            }
        }

        foreach (array_unique($productIds) as $pid) {
            Entitlement::firstOrCreate(
                ['user_id' => $user->id, 'product_id' => $pid],
                ['redeem_code_id' => $redeem->id]
            );
        }

        $redeem->update(['status' => 'terpakai', 'dipakai_pada' => now()]);

        return redirect()->route('home')
            ->with('sukses', "Yeay! \"{$product->judul}\" sekarang milikmu. Selamat belajar & bermain! 🎉");
    }
}
