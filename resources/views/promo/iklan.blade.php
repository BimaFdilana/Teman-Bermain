@php
$berakhir = $kampanye->isBerakhir();
$tipeLabel = ['ebook' => 'Ebook', 'miniapp' => 'MiniApp', 'bundle' => 'Bundle'];

// Parse URL video: YouTube -> embed, selain itu -> link
$videoEmbed = null; $videoLink = $kampanye->video_url;
if ($videoLink && preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{6,})~', $videoLink, $m)) {
    $videoEmbed = 'https://www.youtube-nocookie.com/embed/' . $m[1];
}
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $kampanye->judul }} — Rumah Cahaya</title>
    <meta name="description" content="{{ Str::limit(strip_tags($kampanye->deskripsi ?? ''), 150) }}">
    <meta property="og:title" content="{{ $kampanye->judul }} — Rumah Cahaya">
    <meta property="og:description" content="{{ Str::limit(strip_tags($kampanye->deskripsi ?? ''), 150) }}">
    <meta property="og:type" content="website">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-64.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon-180.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Jari menunjuk: goyangan halus */
        @keyframes tunjuk { 0%,100% { transform:translateX(0); } 50% { transform:translateX(7px); } }
        @keyframes tunjukKiri { 0%,100% { transform:translateX(0); } 50% { transform:translateX(-7px); } }
        .anim-tunjuk { display:inline-block; animation:tunjuk 1.4s ease-in-out infinite; }
        .anim-tunjuk-kiri { display:inline-block; animation:tunjukKiri 1.4s ease-in-out infinite; }
        /* Daun hias: goyang lembut */
        @keyframes daun-goyang { 0%,100% { transform:rotate(-5deg); } 50% { transform:rotate(6deg); } }
        .daun-anim { animation:daun-goyang 7s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) { .anim-tunjuk, .anim-tunjuk-kiri, .daun-anim { animation:none; } }
    </style>
    @include('partials.meta-pixel-script')
        @include('partials.meta-pixel-events')
</head>
<body style="background:#FAF6EC;min-height:100vh;min-height:100dvh;margin:0">
    @include('partials.meta-pixel-noscript')
{{-- Bingkai HP: putih, di atas background krem --}}
<div class="mx-auto bg-white" style="max-width:430px;min-height:100vh;min-height:100dvh;box-shadow:0 0 40px rgba(90,70,30,.12)">
    @if($pratinjau ?? false)
        <div class="text-center px-5 py-3 font-bold text-sm" style="background:#F5B301;color:#14532D">👁️ Pratinjau admin — iklan ini belum tayang untuk publik ({{ $kampanye->status === 'draft' ? 'Draft' : 'Terjadwal' }}). Ubah status ke Aktif agar bisa dibuka pengunjung.</div>
    @endif
<div class="px-5 py-8 text-center" style="position:relative">

    {{-- Daun hias ala landing page --}}
    <span class="poster-leaf daun-anim" aria-hidden="true" style="top:14px;left:6px;font-size:1.6rem">🌿</span>
    <span class="poster-leaf daun-anim" aria-hidden="true" style="top:44px;right:8px;font-size:1.6rem;animation-delay:1.2s">🍃</span>
    <span class="poster-leaf daun-anim" aria-hidden="true" style="top:32%;left:4px;font-size:1.4rem;animation-delay:2s">🍃</span>
    <span class="poster-leaf daun-anim" aria-hidden="true" style="top:52%;right:6px;font-size:1.5rem;animation-delay:.6s">🌿</span>
    <span class="poster-leaf daun-anim" aria-hidden="true" style="top:74%;left:6px;font-size:1.4rem;animation-delay:1.8s">🌱</span>

    {{-- Logo --}}
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo-stacked.png') }}" alt="Rumah Cahaya" class="mx-auto" style="height:56px"
             onerror="this.style.display='none'">
    </div>

    {{-- Judul promo (tetap di atas) --}}
    <div class="text-center mb-5">
        <span class="kidza-kicker poster-kicker-yellow mb-3">Promo Spesial</span>
        <h1 class="font-extrabold mt-3 mb-2" style="font-size:1.9rem;color:#14532D;line-height:1.25">{{ $kampanye->judul }}</h1>
        @if($kampanye->subjudul)
            <p class="font-extrabold mb-3" style="font-size:1.05rem;color:#1E7B34;line-height:1.4">{{ $kampanye->subjudul }}</p>
        @endif
    </div>

    @if($berakhir)
        <div class="text-center p-6 mb-6 rounded-3xl" style="background:#FFFDF4;border:2px solid #F0E7CC">
            <p class="font-extrabold text-lg" style="color:#14532D">Promo telah berakhir</p>
            <p class="text-sm mt-1" style="color:#5B5344">Terima kasih atas antusiasmenya! Nantikan promo berikutnya.</p>
        </div>
    @else
        {{-- Section dinamis sesuai urutan yang diatur admin --}}
        @foreach($kampanye->urutanKontenEfektif() as $section)
            @if($kampanye->sectionTampil($section))
                @include('promo.iklan.sections.' . str_replace('_', '-', $section))
            @endif
        @endforeach
    @endif

    <p class="text-center text-xs mt-8" style="color:#a39e93">Login dengan akun Google untuk melanjutkan pemesanan</p>
    <p class="text-center text-xs mt-2" style="color:#a39e93">© {{ date('Y') }} Rumah Cahaya</p>
    <span class="poster-leaf daun-anim" aria-hidden="true" style="bottom:64px;left:8px;font-size:1.6rem;animation-delay:3.1s">🌱</span>
    <span class="poster-leaf daun-anim" aria-hidden="true" style="bottom:96px;right:10px;font-size:1.6rem;animation-delay:2.6s">🌿</span>
</div>
</div>

@if(!$berakhir)
<script>
(function () {
    const akhir = new Date(@json($kampanye->berakhir_at->toIso8601String())).getTime();
    function fmt(sisa) {
        const d = Math.floor(sisa / 86400);
        const h = String(Math.floor(sisa % 86400 / 3600)).padStart(2, '0');
        const m = String(Math.floor(sisa % 3600 / 60)).padStart(2, '0');
        const s = String(sisa % 60).padStart(2, '0');
        return (d > 0 ? d + ' hari ' : '') + h + ':' + m + ':' + s;
    }
    function tick() {
        const sisa = Math.max(0, Math.floor((akhir - Date.now()) / 1000));
        document.querySelectorAll('.countdown .cd-text').forEach(function (el) { el.textContent = fmt(sisa); });
        if (sisa <= 0) location.reload();
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endif
</body>
</html>
