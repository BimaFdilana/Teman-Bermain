@php
$berakhir = $kampanye->isBerakhir();
$tipeLabel = ['ebook' => '📚 Ebook', 'miniapp' => '🎮 MiniApp', 'bundle' => '🎁 Bundle'];
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Hitungan mundur: merah mencolok */
        .cd-box { background:#DC2626; border-radius:1rem; min-width:4.2rem; padding:.6rem .4rem;
                  text-align:center; box-shadow:0 4px 12px rgba(220,38,38,.25); }
        .cd-num { font-weight:800; font-size:1.6rem; color:#fff; line-height:1; font-variant-numeric:tabular-nums; }
        .cd-lbl { font-size:.68rem; font-weight:700; color:#fecaca; margin-top:.2rem; }
        /* Jari menunjuk: goyangan halus */
        @keyframes tunjuk { 0%,100% { transform:translateX(0); } 50% { transform:translateX(7px); } }
        @keyframes tunjukKiri { 0%,100% { transform:translateX(0); } 50% { transform:translateX(-7px); } }
        .anim-tunjuk { display:inline-block; animation:tunjuk 1.4s ease-in-out infinite; }
        .anim-tunjuk-kiri { display:inline-block; animation:tunjukKiri 1.4s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) { .anim-tunjuk, .anim-tunjuk-kiri { animation:none; } }
    </style>
</head>
<body style="background:#FAF6EC;min-height:100vh;margin:0">
{{-- Bingkai HP: putih, di atas background krem --}}
<div class="mx-auto bg-white" style="max-width:430px;min-height:100vh;box-shadow:0 0 40px rgba(90,70,30,.12)">
<div class="px-5 py-8 text-center">

    {{-- Logo --}}
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo-stacked.png') }}" alt="Rumah Cahaya" class="mx-auto" style="height:56px"
             onerror="this.style.display='none'">
    </div>

    {{-- Judul promo --}}
    <div class="text-center mb-5">
        <span class="kidza-kicker poster-kicker-yellow mb-3">🎉 Promo Spesial</span>
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
            <p class="font-extrabold text-lg" style="color:#14532D">⏰ Promo telah berakhir</p>
            <p class="text-sm mt-1" style="color:#5B5344">Terima kasih atas antusiasmenya! Nantikan promo berikutnya.</p>
        </div>
    @else
        {{-- Hitungan mundur (merah) --}}
        <div class="mb-16 mt-16 text-center">
            <p class="text-sm font-extrabold mb-2" style="color:#DC2626">⏳ Promo berakhir dalam</p>
            <div class="flex justify-center gap-2 countdown">
                <div class="cd-box"><div class="cd-num cd-d">0</div><div class="cd-lbl">Hari</div></div>
                <div class="cd-box"><div class="cd-num cd-h">0</div><div class="cd-lbl">Jam</div></div>
                <div class="cd-box"><div class="cd-num cd-m">0</div><div class="cd-lbl">Menit</div></div>
                <div class="cd-box"><div class="cd-num cd-s">0</div><div class="cd-lbl">Detik</div></div>
            </div>
            <p class="text-xs mt-2" style="color:#a39e93">s/d {{ $kampanye->berakhir_at->format('d M Y, H:i') }} WIB</p>
        </div>

        {{-- Tiga panah sale memantul --}}
        <div class="flex justify-center gap-5 my-16" aria-hidden="true">
            <img src="{{ asset('images/iklan/sale.gif') }}" alt="" style="width:64px;height:64px">
            <img src="{{ asset('images/iklan/sale.gif') }}" alt="" style="width:64px;height:64px">
            <img src="{{ asset('images/iklan/sale.gif') }}" alt="" style="width:64px;height:64px">
        </div>

        {{-- PRODUK 1 (tanpa card) --}}
        @if($pertama)
            <div class="mb-16">
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
            <div class="flex justify-center gap-5 my-16" aria-hidden="true">
                <img src="{{ asset('images/iklan/sale.gif') }}" alt="" style="width:64px;height:64px">
                <img src="{{ asset('images/iklan/sale.gif') }}" alt="" style="width:64px;height:64px">
                <img src="{{ asset('images/iklan/sale.gif') }}" alt="" style="width:64px;height:64px">
            </div>
        @endif

        {{-- Video (opsional) --}}
        @if($videoLink)
            <div class="mb-16">
                <p class="font-extrabold text-center mb-3" style="color:#14532D">🎬 Tonton Dulu Videonya</p>
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
                    <p class="text-sm mb-3" style="color:#5B5344;line-height:1.6">{{ $kampanye->ajakan }}</p>
                @endif
                @if($pertama)
                    <a href="{{ route('promo.pesan', $pertama->slug) }}" class="kidza-btn kidza-btn-green w-full text-center" style="font-size:1rem">
                        <span class="anim-tunjuk">👉</span> Pesan Sekarang <span class="anim-tunjuk-kiri">👈</span>
                    </a>
                @endif
            </div>
        @elseif($kampanye->ajakan)
            <div class="mb-16">
                <p class="text-sm mb-3" style="color:#5B5344;line-height:1.6">{{ $kampanye->ajakan }}</p>
                @if($pertama)
                    <a href="{{ route('promo.pesan', $pertama->slug) }}" class="kidza-btn kidza-btn-green w-full text-center" style="font-size:1rem">
                        <span class="anim-tunjuk">👉</span> Pesan Sekarang <span class="anim-tunjuk-kiri">👈</span>
                    </a>
                @endif
            </div>
        @endif

        {{-- Materi Ebook (card) --}}
        @if(!empty($kampanye->materi))
            <div class="rounded-3xl p-5 mt-20 mb-8" style="background:#FFFDF4;border:2px solid #F0E7CC">
                <p class="font-extrabold mb-3" style="color:#14532D">📚 Materi Ebook</p>
                <ol class="text-sm space-y-2" style="color:#444;list-style-position:inside;margin:0;padding:0">
                    @foreach($kampanye->materi as $i => $m)
                        <li><span class="font-bold" style="color:#1E7B34">{{ $i + 1 }}.</span> {{ $m }}</li>
                    @endforeach
                </ol>
            </div>
            @if($pertama)
                <a href="{{ route('promo.pesan', $pertama->slug) }}" class="kidza-btn kidza-btn-green w-full text-center mt-3" style="font-size:1rem">
                    <span class="anim-tunjuk">👉</span> Pesan Sekarang <span class="anim-tunjuk-kiri">👈</span>
                </a>
            @endif
        @endif

        {{-- Bukti Kepuasan (hidden bila kosong) --}}
        @if($testimonis->isNotEmpty())
            <div class="mt-20 mb-8">
                <p class="font-extrabold text-center mb-3" style="color:#14532D">💬 Bukti Kepuasan</p>
                @foreach($testimonis as $t)
                    <div class="rounded-2xl p-4 mb-2 bg-white" style="border:2px solid #F0E7CC">
                        <p class="text-sm italic mb-1" style="color:#444">"{{ $t->isi }}"</p>
                        <p class="text-xs font-bold" style="color:#a39e93">— {{ $t->nama }}{{ $t->peran ? ', '.$t->peran : '' }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Harga Spesial --}}
        <div class="text-center mt-20 mb-8">
            <p class="font-extrabold mb-3" style="font-size:1.7rem;color:#DC2626">🔥 HARGA SPESIAL</p>
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

        {{-- Hitungan mundur (merah) lagi --}}
        <div class="mb-8 text-center mt-20">
            <p class="text-sm font-extrabold mb-2" style="color:#DC2626">⏳ Promo berakhir dalam</p>
            <div class="flex justify-center gap-2 countdown">
                <div class="cd-box"><div class="cd-num cd-d">0</div><div class="cd-lbl">Hari</div></div>
                <div class="cd-box"><div class="cd-num cd-h">0</div><div class="cd-lbl">Jam</div></div>
                <div class="cd-box"><div class="cd-num cd-m">0</div><div class="cd-lbl">Menit</div></div>
                <div class="cd-box"><div class="cd-num cd-s">0</div><div class="cd-lbl">Detik</div></div>
            </div>
        </div>

        {{-- Produk 2..n (berulang) --}}
        @foreach($sisa as $product)
            <div class="flex justify-center gap-5 my-16" aria-hidden="true">
                <img src="{{ asset('images/iklan/sale.gif') }}" alt="" style="width:64px;height:64px">
                <img src="{{ asset('images/iklan/sale.gif') }}" alt="" style="width:64px;height:64px">
                <img src="{{ asset('images/iklan/sale.gif') }}" alt="" style="width:64px;height:64px">
            </div>
            <div class="mb-16">
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
    <p class="text-center mt-1" style="font-size:.65rem;color:#c9c2b2">
        <a href="https://www.flaticon.com/free-animated-icons/scroll" target="_blank" rel="noopener" style="color:#c9c2b2">Scroll</a> &
        <a href="https://www.flaticon.com/free-animated-icons/sale" target="_blank" rel="noopener" style="color:#c9c2b2">Sale</a>
        animated icons by Flaticon
    </p>
</div>
</div>

@if(!$berakhir)
<script>
(function () {
    const akhir = new Date(@json($kampanye->berakhir_at->toIso8601String())).getTime();
    function tick() {
        let sisa = Math.max(0, Math.floor((akhir - Date.now()) / 1000));
        document.querySelectorAll('.countdown').forEach(function (box) {
            box.querySelector('.cd-d').textContent = Math.floor(sisa / 86400);
            box.querySelector('.cd-h').textContent = String(Math.floor(sisa % 86400 / 3600)).padStart(2, '0');
            box.querySelector('.cd-m').textContent = String(Math.floor(sisa % 3600 / 60)).padStart(2, '0');
            box.querySelector('.cd-s').textContent = String(sisa % 60).padStart(2, '0');
        });
        if (sisa <= 0) location.reload();
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endif
</body>
</html>
