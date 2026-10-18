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

<script>
(function () {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const btn = document.getElementById('btn-pasang');
    const panduan = document.getElementById('panduan-ios');
    let deferredPrompt = null;

    if (isIOS) {
        // iOS: tidak ada prompt otomatis — tampilkan tombol + panduan manual
        btn.style.display = 'inline-flex';
        btn.addEventListener('click', () => {
            panduan.style.display = panduan.style.display === 'none' ? 'block' : 'none';
            panduan.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
        return;
    }

    // Android/Chrome: pakai prompt install resmi bila tersedia
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        btn.style.display = 'inline-flex';
    });

    btn.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            await deferredPrompt.userChoice;
            deferredPrompt = null;
            btn.style.display = 'none';
        } else {
            alert('Untuk memasang: buka menu browser (⋮) lalu pilih "Install app" / "Add to Home screen".');
        }
    });

    window.addEventListener('appinstalled', () => { btn.style.display = 'none'; });
})();
</script>
</x-app-layout>
