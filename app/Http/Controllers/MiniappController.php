<?php

namespace App\Http\Controllers;

use App\Models\InstallToken;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MiniappController extends Controller
{
    /**
     * Player MiniApp: iframe sandbox full-screen + tombol Pasang di HP (PRD §5.6).
     */
    public function main(Request $request, Product $product): View
    {
        $this->otorisasi($request, $product);

        $token = InstallToken::untuk($request->user(), $product);

        return view('miniapp.main', [
            'product' => $product,
            'fileUrl' => URL::temporarySignedRoute(
                'file.miniapp', now()->addMinutes(30), ['product' => $product->id]
            ),
            'manifestUrl' => route('miniapp.manifest', $product),
            'installToken' => $token,
        ]);
    }

    /**
     * Sajikan file HTML MiniApp via signed URL (hanya pemilik).
     */
    public function file(Request $request, Product $product): Response
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Tautan tidak valid atau kadaluarsa.');
        }
        $this->otorisasi($request, $product);

        $path = $product->miniapp?->html_path;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'File MiniApp belum tersedia.');
        }

        return response()->file(
            Storage::disk('local')->path($path),
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * Manifest PWA dinamis per MiniApp (PRD BR-RC-14/19).
     * start_url membawa install token agar iOS bisa pasang tanpa login ulang.
     */
    public function manifest(Request $request, Product $product): JsonResponse
    {
        $this->otorisasi($request, $product);

        $token = InstallToken::untuk($request->user(), $product);
        $nama = $product->miniapp?->pwa_name ?: $product->judul;
        $icon = $product->miniapp?->icon_path
            ? asset('storage/'.$product->miniapp->icon_path)
            : asset('images/logo-icon.png');

        return response()->json([
            'name' => $nama,
            'short_name' => mb_substr($nama, 0, 12),
            'description' => $product->deskripsi,
            'start_url' => route('pwa.launch', ['token' => $token->token]),
            'scope' => route('pwa.launch', ['token' => $token->token]),
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => '#F9F9F6',
            'theme_color' => '#167941',
            'icons' => [
                ['src' => $icon, 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => $icon, 'sizes' => '512x512', 'type' => 'image/png'],
            ],
        ]);
    }

    /**
     * Launcher PWA: token ditukar menjadi sesi — tanpa login ulang (PRD §5.6).
     * Kritis untuk iOS yang storage-nya terpisah dari browser.
     */
    public function launch(string $token): RedirectResponse
    {
        $install = InstallToken::where('token', $token)->with('product')->first();

        if (! $install || ! $install->berlaku() || $install->product->tipe !== 'miniapp') {
            abort(403, 'Tautan install tidak valid atau sudah dicabut.');
        }

        // Pastikan yang membuka tetap pemilik entitlement
        if (! $install->user->hasProduct($install->product_id) && ! $install->user->isAdmin()) {
            abort(403, 'Akses MiniApp ini sudah tidak berlaku.');
        }

        Auth::login($install->user, true);
        $install->update(['dipakai_terakhir_at' => now()]);

        return redirect()->route('miniapp.main', $install->product);
    }

    private function otorisasi(Request $request, Product $product): void
    {
        abort_unless($product->tipe === 'miniapp', 404);
        $user = $request->user();
        abort_unless($user && ($user->isAdmin() || $user->hasProduct($product->id)), 403);
    }
}
