<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EbookController extends Controller
{
    /**
     * Reader PDF di dalam aplikasi (PRD §5.5).
     */
    public function baca(Request $request, Product $product): View
    {
        $this->otorisasi($request, $product, 'ebook');

        return view('ebook.baca', [
            'product' => $product,
            'fileUrl' => URL::temporarySignedRoute(
                'file.ebook', now()->addMinutes(30), ['product' => $product->id]
            ),
            'downloadUrl' => URL::temporarySignedRoute(
                'file.ebook', now()->addMinutes(30), ['product' => $product->id, 'download' => 1]
            ),
        ]);
    }

    /**
     * Sajikan file PDF via signed URL berumur pendek (PRD §5.5).
     */
    public function file(Request $request, Product $product): Response
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Tautan tidak valid atau kadaluarsa.');
        }
        $this->otorisasi($request, $product, 'ebook');

        $path = $product->ebook?->pdf_path;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'File Ebook belum tersedia.');
        }

        $full = Storage::disk('local')->path($path);
        $nama = $product->slug.'.pdf';

        if ($request->boolean('download')) {
            return response()->download($full, $nama);
        }
        return response()->file($full, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Hanya pemilik entitlement (atau admin) yang boleh akses.
     */
    private function otorisasi(Request $request, Product $product, string $tipe): void
    {
        abort_unless($product->tipe === $tipe, 404);
        $user = $request->user();
        abort_unless($user && ($user->isAdmin() || $user->hasProduct($product->id)), 403);
    }
}
