<?php

namespace App\Http\Controllers;

use App\Models\InstallToken;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'scope' => '/',
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
     *
     * Yang terpasang = file HTML gamenya LANGSUNG (bukan halaman web):
     * start_url menyajikan HTML MiniApp, bukan redirect ke player.
     */
    public function launch(string $token): Response
    {
        $install = InstallToken::where('token', $token)->with('product.miniapp')->first();

        if (! $install || ! $install->berlaku() || $install->product->tipe !== 'miniapp') {
            abort(403, 'Tautan install tidak valid atau sudah dicabut.');
        }

        // Pastikan yang membuka tetap pemilik entitlement
        if (! $install->user->hasProduct($install->product_id) && ! $install->user->isAdmin()) {
            abort(403, 'Akses MiniApp ini sudah tidak berlaku.');
        }

        $install->update(['dipakai_terakhir_at' => now()]);

        $path = $install->product->miniapp?->html_path;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404, 'File MiniApp belum tersedia.');
        }

        return response()->file(
            Storage::disk('local')->path($path),
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * Service worker PWA MiniApp (PRD §12).
     * Didaftarkan dari halaman player dengan scope '/' agar memenuhi syarat
     * install Chrome (SW + fetch handler); hanya menangani URL MiniApp —
     * situs utama & admin dilewatkan apa adanya (bukan PWA).
     */
    public function serviceWorker(): Response
    {
        // Versi cache mengikuti update MiniApp terbaru: upload baru = cache lama dibuang.
        $versi = \App\Models\Miniapp::max('updated_at')?->timestamp ?? 1;
        $offline = route('pwa.offline');

        $js = <<<JS
        /* Service worker Rumah Cahaya-bks — PWA per MiniApp */
        const CACHE = 'rc-miniapp-v{$versi}';
        const OFFLINE_URL = '{$offline}';

        self.addEventListener('install', (e) => {
            e.waitUntil(
                caches.open(CACHE).then((c) => c.add(OFFLINE_URL)).then(() => self.skipWaiting())
            );
        });

        self.addEventListener('activate', (e) => {
            e.waitUntil(
                caches.keys()
                    .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
                    .then(() => self.clients.claim())
            );
        });

        self.addEventListener('fetch', (e) => {
            const url = new URL(e.request.url);
            if (e.request.method !== 'GET' || url.origin !== self.location.origin) return;
            const path = url.pathname;

            // 1) Navigasi area PWA (file HTML game langsung): cache-first agar bisa main
            //    offline setelah terpasang; fallback ke halaman offline bila belum pernah dibuka
            if (path.startsWith('/pwa/') && e.request.mode === 'navigate') {
                e.respondWith(
                    caches.match(e.request).then((cached) => {
                        const network = fetch(e.request).then((res) => {
                            if (res && res.ok) {
                                const copy = res.clone();
                                caches.open(CACHE).then((c) => c.put(e.request, copy));
                            }
                            return res;
                        }).catch(() => cached || caches.match(OFFLINE_URL));
                        return cached || network;
                    })
                );
                return;
            }

            // 2) File game MiniApp: cache-first (kunci stabil tanpa signature) agar bisa main offline
            if (path.startsWith('/file/miniapp/')) {
                const key = url.origin + path;
                e.respondWith(
                    caches.match(key).then((cached) => {
                        const network = fetch(e.request).then((res) => {
                            if (res && res.ok) {
                                const copy = res.clone();
                                caches.open(CACHE).then((c) => c.put(key, copy));
                            }
                            return res;
                        }).catch(() => cached);
                        return cached || network;
                    })
                );
                return;
            }

            // 3) Selain URL MiniApp: jangan sentuh (situs utama & admin tetap non-PWA)
        });
        JS;

        return response($js, 200, ['Content-Type' => 'application/javascript']);
    }

    private function otorisasi(Request $request, Product $product): void
    {
        abort_unless($product->tipe === 'miniapp', 404);
        $user = $request->user();
        abort_unless($user && ($user->isAdmin() || $user->hasProduct($product->id)), 403);
    }
}
