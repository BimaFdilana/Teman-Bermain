@php
$berakhir = $kampanye->isBerakhir();
$tipeLabel = ['ebook' => 'Ebook', 'miniapp' => 'MiniApp', 'bundle' => 'Bundle'];
$produks = $kampanye->products;
$pertama = $produks->first();
$sisa = $produks->skip(1);

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
</head>
<body style="background:#FAF6EC;min-height:100vh;margin:0">
{{-- Bingkai HP: putih, di atas background krem --}}
<div class="mx-auto bg-white" style="max-width:430px;min-height:100vh;box-shadow:0 0 40px rgba(90,70,30,.12)">
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

    {{-- Judul promo --}}
    <div class="text-center mb-5">
        <span class="kidza-kicker poster-kicker-yellow mb-3">Promo Spesial</span>
        <h1 class="font-extrabold mt-3 mb-2" style="font-size:1.9rem;color:#14532D;line-height:1.25">{{ $kampanye->judul }}</h1>
        @if($kampanye->subjudul)
            <p class="font-extrabold mb-3" style="font-size:1.05rem;color:#1E7B34;line-height:1.4">{{ $kampanye->subjudul }}</p>
        @endif
        @if($kampanye->deskripsi)
            <p class="text-sm" style="color:#5B5344">{{ $kampanye->deskripsi }}</p>
        @endif
    </div>

    {{-- Banner (opsional) --}}
    @if($kampanye->gambarUrl())
        <div class="mb-6 -mx-5">
            <img src="{{ $kampanye->gambarUrl() }}" alt="{{ $kampanye->judul }}" class="w-full object-cover">
        </div>
    @endif

    @if($berakhir)
        <div class="text-center p-6 mb-6 rounded-3xl" style="background:#FFFDF4;border:2px solid #F0E7CC">
            <p class="font-extrabold text-lg" style="color:#14532D">Promo telah berakhir</p>
            <p class="text-sm mt-1" style="color:#5B5344">Terima kasih atas antusiasmenya! Nantikan promo berikutnya.</p>
        </div>
    @else
        {{-- Hitungan mundur (ramping) --}}
        <div class="mt-12 mb-10 text-center">
            <p class="inline-block px-5 py-2 rounded-full text-sm font-bold countdown" style="background:#FDF0EC;color:#A63D2F;border:1.5px solid #E8C4BC">⏳ Berakhir dalam <span class="cd-text" style="font-variant-numeric:tabular-nums">…</span></p>
            <p class="text-xs mt-2" style="color:#a39e93">s/d {{ $kampanye->berakhir_at->format('d M Y, H:i') }} WIB</p>
        </div>

        {{-- Tiga panah sale memantul --}}
        <div class="my-12" aria-hidden="true" style="height:2px;background:#F0E7CC;border-radius:2px"></div>

        {{-- PRODUK 1 (tanpa card) --}}
        @if($pertama)
            <div class="mb-10">
                @if($pertama->coverUrl())
                    <img src="{{ $pertama->coverUrl() }}" alt="{{ $pertama->judul }}" class="rounded-2xl w-full object-cover mb-3" style="aspect-ratio:16/10">
                @endif
                <span class="kidza-kicker mb-2" style="font-size:.65rem">{{ $tipeLabel[$pertama->tipe] ?? $pertama->tipe }}</span>
                <p class="font-extrabold mb-2" style="font-size:1.15rem;color:#14532D;line-height:1.3">{{ $pertama->judul }}</p>
                @if($pertama->deskripsi)
                    <p class="text-sm mb-3" style="color:#5B5344;line-height:1.6">{{ $pertama->deskripsi }}</p>
                @endif
                <div class="flex items-baseline justify-center gap-2 mb-3">
                    <span class="font-extrabold" style="font-size:1.2rem;color:#1E7B34">Rp {{ number_format($pertama->harga, 0, ',', '.') }}</span>
                    @if($pertama->harga_coret)
                        <span class="text-sm line-through" style="color:#a39e93">Rp {{ number_format($pertama->harga_coret, 0, ',', '.') }}</span>
                    @endif
                </div>
                <a href="{{ route('promo.pesan', $pertama->slug) }}" class="kidza-btn kidza-btn-green w-full text-center" style="font-size:1rem">
                    <span class="anim-tunjuk">👉</span> Pesan Sekarang <span class="anim-tunjuk-kiri">👈</span>
                </a>
            </div>

            {{-- Tiga panah sale --}}
            <div class="my-12" aria-hidden="true" style="height:2px;background:#F0E7CC;border-radius:2px"></div>
        @endif

        {{-- Video (opsional) --}}
        @if($videoLink)
            <div class="mb-10">
                <p class="font-extrabold text-center mb-3" style="color:#14532D">Tonton Dulu Videonya</p>
                @if($videoEmbed)
                    <div class="rounded-2xl overflow-hidden mb-3" style="aspect-ratio:16/9">
                        <iframe src="{{ $videoEmbed }}" class="w-full h-full" style="border:0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen title="Video promo"></iframe>
                    </div>
                @else
                    <a href="{{ $videoLink }}" target="_blank" rel="noopener"
                       class="kidza-btn w-full text-center mb-3" style="background:#fff;border:2px solid #F0E7CC;color:#14532D">▶ Tonton Videonya</a>
                @endif
                @if($kampanye->ajakan)
                    <p class="text-sm" style="color:#5B5344;line-height:1.6;overflow-wrap:anywhere">{{ $kampanye->ajakan }}</p>
                @endif
            </div>
        @elseif($kampanye->ajakan)
            <div class="mb-10">
                <p class="text-sm" style="color:#5B5344;line-height:1.6;overflow-wrap:anywhere">{{ $kampanye->ajakan }}</p>
            </div>
        @endif

        {{-- Materi Ebook (card) --}}
        @if(!empty($kampanye->materi))
            <div class="rounded-3xl p-5 mt-12 mb-8" style="background:#FFFDF4;border:2px solid #F0E7CC">
                <p class="font-extrabold mb-3" style="color:#14532D">Materi Ebook</p>
                <ol class="text-sm space-y-2" style="color:#444;list-style-position:inside;margin:0;padding:0">
                    @foreach($kampanye->materi as $i => $m)
                        <li><span class="font-bold" style="color:#1E7B34">{{ $i + 1 }}.</span> {{ $m }}</li>
                    @endforeach
                </ol>
            </div>
        @endif

        {{-- Bukti Kepuasan (hidden bila kosong) --}}
        @if($testimonis->isNotEmpty())
            <div class="mt-12 mb-8">
                <p class="font-extrabold text-center mb-3" style="color:#14532D">Bukti Kepuasan</p>
                @foreach($testimonis as $t)
                    <div class="rounded-2xl p-4 mb-2 bg-white" style="border:2px solid #F0E7CC">
                        <p class="text-sm italic mb-1" style="color:#444">"{{ $t->isi }}"</p>
                        <p class="text-xs font-bold" style="color:#a39e93">— {{ $t->nama }}{{ $t->peran ? ', '.$t->peran : '' }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Harga Spesial --}}
        <div class="text-center mt-12 mb-8">
            <p class="font-extrabold mb-3" style="font-size:1.7rem;color:#14532D">HARGA SPESIAL</p>
            @foreach($produks as $p)
                <div class="mb-2">
                    <p class="text-xs font-bold" style="color:#a39e93">{{ $p->judul }}</p>
                    @if($p->harga_coret)
                        <span class="text-sm line-through" style="color:#a39e93">Rp {{ number_format($p->harga_coret, 0, ',', '.') }}</span>
                    @endif
                    <p class="font-extrabold" style="font-size:1.6rem;color:#14532D">Rp {{ number_format($p->harga, 0, ',', '.') }}</p>
                </div>
            @endforeach
            @if($pertama)
                <a href="{{ route('promo.pesan', $pertama->slug) }}" class="kidza-btn kidza-btn-green w-full text-center mt-2" style="font-size:1rem">
                    <span class="anim-tunjuk">👉</span> Pesan Sekarang <span class="anim-tunjuk-kiri">👈</span>
                </a>
            @endif
        </div>

        {{-- Hitungan mundur (ramping) lagi --}}
        <div class="mb-8 text-center mt-12">
            <p class="inline-block px-5 py-2 rounded-full text-sm font-bold countdown" style="background:#FDF0EC;color:#A63D2F;border:1.5px solid #E8C4BC">⏳ Berakhir dalam <span class="cd-text" style="font-variant-numeric:tabular-nums">…</span></p>
        </div>

        {{-- Produk 2..n (berulang) --}}
        @foreach($sisa as $product)
            <div class="my-12" aria-hidden="true" style="height:2px;background:#F0E7CC;border-radius:2px"></div>
            <div class="mb-10">
                @if($product->coverUrl())
                    <img src="{{ $product->coverUrl() }}" alt="{{ $product->judul }}" class="rounded-2xl w-full object-cover mb-3" style="aspect-ratio:16/10">
                @endif
                <span class="kidza-kicker mb-2" style="font-size:.65rem">{{ $tipeLabel[$product->tipe] ?? $product->tipe }}</span>
                <p class="font-extrabold mb-2" style="font-size:1.15rem;color:#14532D;line-height:1.3">{{ $product->judul }}</p>
                @if($product->deskripsi)
                    <p class="text-sm mb-3" style="color:#5B5344;line-height:1.6">{{ $product->deskripsi }}</p>
                @endif
                <div class="flex items-baseline justify-center gap-2 mb-3">
                    <span class="font-extrabold" style="font-size:1.2rem;color:#1E7B34">Rp {{ number_format($product->harga, 0, ',', '.') }}</span>
                    @if($product->harga_coret)
                        <span class="text-sm line-through" style="color:#a39e93">Rp {{ number_format($product->harga_coret, 0, ',', '.') }}</span>
                    @endif
                </div>
                <a href="{{ route('promo.pesan', $product->slug) }}" class="kidza-btn kidza-btn-green w-full text-center" style="font-size:1rem">
                    <span class="anim-tunjuk">👉</span> Pesan Sekarang <span class="anim-tunjuk-kiri">👈</span>
                </a>
            </div>
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
