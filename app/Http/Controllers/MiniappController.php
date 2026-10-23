<?php

namespace App\Http\Controllers;

use App\Models\InstallToken;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

        $user = $request->user() ?? $this->userDariToken($request, $product);
        abort_unless($user, 403);
        $token = InstallToken::untuk($user, $product);
        $nama = $product->miniapp?->pwa_name ?: $product->judul;
        $startUrl = route('pwa.launch', ['token' => $token->token]);

        return response()->json([
            'id' => $startUrl,
            'name' => $nama,
            'short_name' => mb_substr($nama, 0, 12),
            'description' => $product->deskripsi,
            'start_url' => $startUrl,
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => '#F9F9F6',
            'theme_color' => '#167941',
            'icons' => $this->ikonManifest($product),
        ]);
    }

    /**
     * Ikon manifest dengan ukuran yang JUJUR: Chrome menolak installability
     * bila ukuran deklarasi tidak cocok dengan file aslinya. Ikon unggahan
     * admin dinyatakan sesuai ukuran file sebenarnya; fallback memakai
     * ikon persegi bawaan 192/512 yang ukurannya pasti.
     */
    private function ikonManifest(Product $product): array
    {
        $path = $product->miniapp?->icon_path;
        if ($path) {
            $sizes = '512x512';
            try {
                $full = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
                if (is_file($full) && ($info = getimagesize($full)) !== false) {
                    $sizes = $info[0].'x'.$info[1];
                }
            } catch (\Throwable) {
                // Abaikan — pakai deklarasi default.
            }

            return [['src' => asset('storage/'.$path), 'sizes' => $sizes, 'type' => 'image/png', 'purpose' => 'any']];
        }

        return [
            ['src' => asset('images/pwa-icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => asset('images/pwa-icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ];
    }

    /**
     * Launcher PWA: token ditukar menjadi sesi — tanpa login ulang (PRD §5.6).
     * Kritis untuk iOS yang storage-nya terpisah dari browser.
     *
     * Yang terpasang = file HTML gamenya LANGSUNG (bukan halaman web):
     * start_url menyajikan HTML MiniApp dengan manifest + tombol install
     * mengambang yang diinjeksi — tanpa view website.
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

        $html = Storage::disk('local')->get($path);
        $html = $this->injeksiPwa($html, $install->product, $token);

        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /**
     * Injeksi manifest + tombol install mengambang ke HTML game.
     * Hasil: yang dibuka/dipasang murni game, bukan view website.
     */
    private function injeksiPwa(string $html, Product $product, string $token): string
    {
        $manifestUrl = route('miniapp.manifest', [$product, 'token' => $token]);
        $swUrl = route('miniapp.sw');

        // Cabut link manifest bawaan game (mis. dari paket ZIP): browser hanya membaca
        // link manifest PERTAMA — manifest sistem (dengan token) harus satu-satunya.
        $html = preg_replace('/<link[^>]+rel=["\']manifest["\'][^>]*>/i', '', $html);

        $headInject = '<link rel="manifest" href="'.e($manifestUrl).'">'."\n"
            .'<meta name="theme-color" content="#167941">'."\n";
        // Sisipkan viewport hanya bila HTML game belum memilikinya (agar responsif di HP).
        if (! preg_match('/<meta[^>]+name=["\']viewport["\']/i', $html)) {
            $headInject .= '<meta name="viewport" content="width=device-width, initial-scale=1">'."\n";
        }
        // Meta iOS: ikon layar utama + mode standalone Safari (hanya bila belum ada).
        $icon = $product->miniapp?->icon_path
            ? asset('storage/'.$product->miniapp->icon_path)
            : asset('images/apple-touch-icon-180.png');
        if (! preg_match('/<link[^>]+rel=["\']apple-touch-icon["\']/i', $html)) {
            $headInject .= '<link rel="apple-touch-icon" href="'.e($icon).'">'."\n";
        }
        if (! preg_match('/<meta[^>]+name=["\']apple-mobile-web-app-capable["\']/i', $html)) {
            $headInject .= '<meta name="apple-mobile-web-app-capable" content="yes">'."\n";
        }
        if (! preg_match('/<meta[^>]+name=["\']mobile-web-app-capable["\']/i', $html)) {
            $headInject .= '<meta name="mobile-web-app-capable" content="yes">'."\n";
        }
        if (! preg_match('/<meta[^>]+name=["\']apple-mobile-web-app-title["\']/i', $html)) {
            $headInject .= '<meta name="apple-mobile-web-app-title" content="'.e($product->miniapp?->pwa_name ?: $product->judul).'">'."\n";
        }

        $btnInject = <<<'HTML'
<style>
#rc-install-fab{position:fixed;bottom:18px;right:18px;z-index:2147483647;background:#F5B301;color:#1a1a1a;
font-family:system-ui,sans-serif;font-weight:800;font-size:15px;border:3px solid #1a1a1a;border-radius:999px;
padding:12px 20px;cursor:pointer;box-shadow:4px 4px 0 #1a1a1a;display:none;align-items:center;gap:8px}
#rc-install-fab:active{transform:translate(2px,2px);box-shadow:2px 2px 0 #1a1a1a}
#rc-install-panel{position:fixed;bottom:84px;right:18px;z-index:2147483647;width:min(320px,calc(100vw - 36px));
background:#fff;color:#1a1a1a;font-family:system-ui,sans-serif;font-size:14px;line-height:1.55;
border:3px solid #1a1a1a;border-radius:16px;box-shadow:4px 4px 0 #1a1a1a;padding:14px 16px;display:none}
#rc-install-panel p{margin:0 0 6px;font-weight:800}
#rc-install-panel ol{margin:0;padding-left:20px}
#rc-panel-tutup{position:absolute;top:6px;right:10px;background:none;border:none;font-size:16px;cursor:pointer;color:#1a1a1a}
@media (display-mode: standalone){#rc-install-fab,#rc-install-panel{display:none!important}}
</style>
<button id="rc-install-fab" type="button">📲 Pasang di HP</button>
<div id="rc-install-panel">
<button id="rc-panel-tutup" type="button" aria-label="Tutup">✕</button>
<div id="rc-panduan-android">
<p>📲 Pasang aplikasi ini</p>
<ol>
<li>Ketuk menu <strong>⋮</strong> di kanan atas Chrome</li>
<li>Pilih <strong>“Install app”</strong> / <strong>“Tambahkan ke Layar Utama”</strong></li>
<li>Ketuk <strong>Install</strong> — ikon muncul di layar utama</li>
</ol>
</div>
<div id="rc-panduan-ios" style="display:none">
<p>📲 Pasang di iPhone/iPad</p>
<ol>
<li>Ketuk tombol <strong>Bagikan</strong> (kotak dengan panah ke atas) di Safari</li>
<li>Pilih <strong>“Tambah ke Layar Utama”</strong></li>
<li>Ketuk <strong>“Tambah”</strong> — ikon muncul di layar utama</li>
</ol>
</div>
</div>
<script>
(function(){
var btn=document.getElementById('rc-install-fab'),panel=document.getElementById('rc-install-panel'),dp=null;
var standalone=window.matchMedia('(display-mode: standalone)').matches||window.navigator.standalone===true;
if(standalone){return;}
if('serviceWorker' in navigator){navigator.serviceWorker.register(SW_URL,{scope:'/'}).catch(function(){});}
var ua=window.navigator.userAgent||'';
var isIos=/iphone|ipad|ipod/i.test(ua)||(window.navigator.platform==='MacIntel'&&window.navigator.maxTouchPoints>1);
function tampil(){btn.style.display='inline-flex';}
window.addEventListener('beforeinstallprompt',function(e){e.preventDefault();dp=e;tampil();});
setTimeout(tampil,3000);
function promptInstall(){dp.prompt();dp.userChoice.then(function(){dp=null;btn.style.display='none';panel.style.display='none';});}
var menunggu=false;
btn.addEventListener('click',function(){
  if(dp){promptInstall();return;}
  if(panel.style.display==='block'){panel.style.display='none';return;}
  if(menunggu){return;}
  // Chrome kadang terlambat memberi sinyal install — tunggu dulu sampai 10 detik.
  menunggu=true;
  var labelAsli=btn.innerHTML;
  btn.innerHTML='⏳ Menyiapkan…';
  var selesai=false;
  var timer=setTimeout(function(){
    if(selesai){return;} selesai=true; menunggu=false; btn.innerHTML=labelAsli;
    document.getElementById('rc-panduan-ios').style.display=isIos?'block':'none';
    document.getElementById('rc-panduan-android').style.display=isIos?'none':'block';
    panel.style.display='block';
  },10000);
  window.addEventListener('beforeinstallprompt',function(){
    if(selesai){return;} selesai=true; clearTimeout(timer); menunggu=false; btn.innerHTML=labelAsli;
    if(dp){promptInstall();}
  },{once:true});
});
document.getElementById('rc-panel-tutup').addEventListener('click',function(){panel.style.display='none';});
window.addEventListener('appinstalled',function(){btn.style.display='none';panel.style.display='none';});
})();
</script>
HTML;
        $btnInject = str_replace('SW_URL', json_encode($swUrl), $btnInject);

        // Injeksi ke <head>, fallback ke awal dokumen
        if (stripos($html, '</head>') !== false) {
            $html = preg_replace('/<\/head>/i', $headInject.'</head>', $html, 1);
        } else {
            $html = $headInject.$html;
        }

        // Injeksi tombol sebelum </body>, fallback ke akhir dokumen
        if (stripos($html, '</body>') !== false) {
            $html = preg_replace('/<\/body>/i', $btnInject.'</body>', $html, 1);
        } else {
            $html .= $btnInject;
        }

        return $html;
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
        // Otorisasi via sesi login ATAU token install (?token=) — launcher tidak login
        $user = $request->user() ?? $this->userDariToken($request, $product);
        abort_unless($user && ($user->isAdmin() || $user->hasProduct($product->id)), 403);
    }

    /**
     * Ambil user dari token install pada query (?token=...) bila tanpa sesi login.
     */
    private function userDariToken(Request $request, Product $product): ?User
    {
        $token = $request->query('token');
        if (! $token) {
            return null;
        }
        $install = InstallToken::where('token', $token)->first();
        if (! $install || ! $install->berlaku() || $install->product_id !== $product->id) {
            return null;
        }

        return $install->user;
    }

    /**
     * Tombol "Install" di MiniApps Milikku: buat token lalu arahkan
     * langsung ke HTML game (/pwa/{token}) — tanpa view website.
     */
    public function install(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->tipe === 'miniapp', 404);
        $user = $request->user();
        abort_unless($user && ($user->isAdmin() || $user->hasProduct($product->id)), 403);

        $token = InstallToken::untuk($user, $product);

        return redirect()->route('pwa.launch', ['token' => $token->token]);
    }
}
