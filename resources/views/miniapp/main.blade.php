<x-app-layout>
@section('head')
    <link rel="manifest" href="{{ $manifestUrl }}">
    <meta name="theme-color" content="#167941">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $product->miniapp?->pwa_name ?: $product->judul }}">
@endsection

<div class="flex items-center justify-between mb-4">
    <a href="{{ route('home') }}" class="text-sm font-bold underline">← Kembali</a>
    <button id="btn-pasang" class="brutal-btn brutal-btn-yellow" style="padding:.5rem 1rem;font-size:.9rem;display:none">📲 Pasang di HP</button>
</div>

<h1 class="font-display text-xl font-bold mb-4 leading-tight">{{ $product->judul }}</h1>

<div class="brutal-card overflow-hidden" style="padding:0">
    <iframe src="{{ $fileUrl }}" sandbox="allow-scripts"
            class="w-full" style="height:75vh;border:0" title="{{ $product->judul }}"
            allow="fullscreen"></iframe>
</div>
<p class="text-xs opacity-60 mt-2 text-center">Tautan main berlaku 30 menit · refresh halaman untuk sesi baru</p>

<!-- Panduan iOS -->
<div id="panduan-ios" class="brutal-card p-5 mt-4" style="display:none;background:#FFF9EC">
    <h2 class="font-display font-bold mb-3">Pasang di iPhone/iPad 📲</h2>
    <ol class="text-sm space-y-2 list-decimal list-inside">
        <li>Ketuk tombol <strong>Share</strong> (kotak + panah) di Safari</li>
        <li>Pilih <strong>Add to Home Screen</strong></li>
        <li>Ketuk <strong>Add</strong> — ikon {{ $product->miniapp?->pwa_name ?: $product->judul }} muncul di Home Screen</li>
    </ol>
    <p class="text-xs opacity-60 mt-3">Setelah dipasang, buka lewat ikon — langsung masuk tanpa login ulang.</p>
</div>

<!-- Panel diagnostik PWA (?debug=1) -->
<div id="pwa-debug" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:9998;background:#1a1a1a;color:#0f0;font-family:monospace;font-size:11px;padding:8px;max-height:40vh;overflow:auto"></div>

<script>
/* PWA per MiniApp (PRD §12): daftarkan service worker agar "Pasang di HP" berfungsi */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register("{{ route('miniapp.sw') }}", { scope: '/' }).catch(function () {});
}
/* Diagnostik ?debug=1: tampilkan status SW, manifest, prompt */
(function () {
    if (new URLSearchParams(window.location.search).get('debug') !== '1') return;
    const panel = document.getElementById('pwa-debug');
    panel.style.display = 'block';
    const log = (t) => { panel.innerHTML += t + '<br>'; };
    log('UA: ' + navigator.userAgent);
    log('SW didukung: ' + ('serviceWorker' in navigator));
    navigator.serviceWorker.ready.then(() => {
        log('SW ready ✓ scope: ' + (navigator.serviceWorker.controller ? 'mengontrol halaman ✓' : 'TIDAK mengontrol ✗'));
    }).catch((e) => log('SW ready GAGAL: ' + e));
    fetch("{{ $manifestUrl }}").then((r) => {
        log('Manifest HTTP: ' + r.status);
        return r.json();
    }).then((m) => {
        log('Manifest name: ' + m.name + ' | display: ' + m.display);
        log('start_url: ' + m.start_url);
        m.icons.forEach((ic, i) => fetch(ic.src, { method: 'HEAD' }).then((r) => log('Icon[' + i + '] ' + ic.sizes + ': HTTP ' + r.status)).catch(() => log('Icon[' + i + '] GAGAL')));
    }).catch((e) => log('Manifest GAGAL: ' + e));
    window.addEventListener('beforeinstallprompt', () => log('EVENT beforeinstallprompt MASUK ✓'));
    setTimeout(() => log('--- 10 dtk, event belum masuk = Chrome menilai tidak installable ---'), 10000);
})();
</script>
<script>
(function () {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const btn = document.getElementById('btn-pasang');
    const panduan = document.getElementById('panduan-ios');
    const modePasang = new URLSearchParams(window.location.search).get('pasang') === '1';
    let deferredPrompt = null;

    // Dari tombol "Install" di MiniApps Milikku: sorot tombol pasang
    function sorotPasang() {
        btn.style.display = 'inline-flex';
        btn.style.animation = 'pulse-pasang 1.2s ease-in-out 3';
        btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    const style = document.createElement('style');
    style.textContent = '@keyframes pulse-pasang{0%,100%{transform:scale(1)}50%{transform:scale(1.12)}}';
    document.head.appendChild(style);

    if (isIOS) {
        // iOS: tidak ada prompt otomatis — tampilkan tombol + panduan manual
        btn.style.display = 'inline-flex';
        btn.addEventListener('click', () => {
            panduan.style.display = panduan.style.display === 'none' ? 'block' : 'none';
            panduan.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
        if (modePasang) {
            panduan.style.display = 'block';
            setTimeout(sorotPasang, 400);
        }
        return;
    }

    // Android/Chrome: pakai prompt install resmi bila tersedia
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        btn.style.display = 'inline-flex';
        if (modePasang) setTimeout(sorotPasang, 400);
    });

    // Fallback: bila prompt tak kunjung datang tapi mode pasang, tetap sorot tombolnya
    if (modePasang) setTimeout(() => { if (!deferredPrompt) sorotPasang(); }, 2500);

    // Sudah terpasang sebagai app: sembunyikan tombol
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
        btn.style.display = 'none';
        return;
    }

    btn.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            await deferredPrompt.userChoice;
            deferredPrompt = null;
            btn.style.display = 'none';
            return;
        }
        // Event belum datang (Chrome cek installability secara async):
        // tunggu sampai 8 detik sebelum menyerah
        const labelAsli = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Menyiapkan...';
        const eventTunggu = await new Promise((resolve) => {
            const timer = setTimeout(() => resolve(null), 8000);
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                clearTimeout(timer);
                resolve(e);
            }, { once: true });
        });
        btn.disabled = false;
        btn.innerHTML = labelAsli;
        if (eventTunggu) {
            eventTunggu.prompt();
            await eventTunggu.userChoice;
            btn.style.display = 'none';
        } else {
            infoDialog('Belum bisa memicu install otomatis setelah menunggu. Coba: 1) pastikan belum terpasang, 2) refresh halaman lalu coba lagi, 3) atau buka menu browser (⋮) → "Install app" / "Add to Home screen".', '📲');
        }
    });

    window.addEventListener('appinstalled', () => { btn.style.display = 'none'; });
})();
</script>
</x-app-layout>
