@php
$berakhir = $kampanye->isBerakhir();
$tipeLabel = ['ebook' => '📚 Ebook', 'miniapp' => '🎮 MiniApp', 'bundle' => '🎁 Bundle'];
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
        .cd-box { background:#FFFDF4; border:2px solid #F0E7CC; border-radius:1rem;
                  min-width:4.2rem; padding:.6rem .4rem; text-align:center;
                  box-shadow:0 4px 12px rgba(90,70,30,.08); }
        .cd-num { font-family:'Baloo 2',sans-serif; font-weight:800; font-size:1.6rem;
                  color:#14532D; line-height:1; font-variant-numeric:tabular-nums; }
        .cd-lbl { font-size:.68rem; font-weight:700; color:#a39e93; margin-top:.2rem; }
        /* Jari menunjuk ke tombol: goyangan halus kiri-kanan */
        @keyframes tunjuk { 0%,100% { transform:translateX(0); } 50% { transform:translateX(7px); } }
        .anim-tunjuk { display:inline-block; animation:tunjuk 1.4s ease-in-out infinite; }
        /* Panah ke bawah: memantul lembut */
        @keyframes lambung { 0%,100% { transform:translateY(0); } 50% { transform:translateY(10px); } }
        .anim-panah { display:inline-block; animation:lambung 1.6s ease-in-out infinite; }
        .anim-panah.d2 { animation-delay:.2s; } .anim-panah.d3 { animation-delay:.4s; }
        @media (prefers-reduced-motion: reduce) { .anim-tunjuk, .anim-panah { animation:none; } }
    </style>
</head>
<body class="poster" style="background:#FFFEF7;min-height:100vh">
<div class="mx-auto px-4 py-8" style="max-width:32rem">

    {{-- Brand --}}
    <div class="text-center mb-5">
        <img src="{{ asset('images/logo-stacked.png') }}" alt="Rumah Cahaya" class="mx-auto mb-2" style="height:52px"
             onerror="this.style.display='none'">
    </div>

    {{-- Judul promo ala sales page --}}
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

    {{-- Banner --}}
    @if($kampanye->gambarUrl())
        <div class="kidza-card p-2 mb-6">
            <img src="{{ $kampanye->gambarUrl() }}" alt="{{ $kampanye->judul }}" class="rounded-3xl w-full object-cover">
        </div>
    @endif

    {{-- Hitungan mundur --}}
    @if($berakhir)
        <div class="poster-info-card text-center p-6 mb-6">
            <p class="font-extrabold text-lg" style="color:#14532D">⏰ Promo telah berakhir</p>
            <p class="text-sm mt-1" style="color:#5B5344">Terima kasih atas antusiasmenya! Nantikan promo berikutnya.</p>
        </div>
    @else
        <div class="mb-2 text-center">
            <p class="text-xs font-bold mb-2" style="color:#a39e93">⏳ Promo berakhir dalam</p>
            <div class="flex justify-center gap-2" id="countdown">
                <div class="cd-box"><div class="cd-num" id="cd-d">0</div><div class="cd-lbl">Hari</div></div>
                <div class="cd-box"><div class="cd-num" id="cd-h">0</div><div class="cd-lbl">Jam</div></div>
                <div class="cd-box"><div class="cd-num" id="cd-m">0</div><div class="cd-lbl">Menit</div></div>
                <div class="cd-box"><div class="cd-num" id="cd-s">0</div><div class="cd-lbl">Detik</div></div>
            </div>
            <p class="text-xs mt-2" style="color:#a39e93">s/d {{ $kampanye->berakhir_at->format('d M Y, H:i') }} WIB</p>
        </div>
    @endif

    {{-- Panah animasi menunjuk ke bawah (ke produk) --}}
    @unless($berakhir)
        <div class="flex justify-center gap-6 my-2" style="font-size:2.6rem;color:#dc2626" aria-hidden="true">
            <span class="anim-panah">⬇️</span>
            <span class="anim-panah d2">⬇️</span>
            <span class="anim-panah d3">⬇️</span>
        </div>
    @endunless

    {{-- Produk: susun vertikal ke bawah --}}
    <div class="grid gap-4 mt-4 grid-cols-1">
        @foreach($kampanye->products as $product)
            <div class="kidza-card p-3">
                <div class="flex gap-3">
                    @if($product->coverUrl())
                        <img src="{{ $product->coverUrl() }}" alt="{{ $product->judul }}"
                             class="rounded-2xl object-cover shrink-0" style="width:88px;height:88px">
                    @endif
                    <div class="flex-1 min-w-0">
                        <span class="kidza-kicker mb-1" style="font-size:.62rem">{{ $tipeLabel[$product->tipe] ?? $product->tipe }}</span>
                        <p class="font-extrabold text-sm mb-1" style="color:#14532D;line-height:1.3">{{ $product->judul }}</p>
                        <div class="flex items-baseline gap-1">
                            <span class="font-extrabold" style="color:#1E7B34">Rp {{ number_format($product->harga, 0, ',', '.') }}</span>
                            @if($product->harga_coret)
                                <span class="text-xs line-through" style="color:#a39e93">{{ number_format($product->harga_coret, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($berakhir)
                    <span class="kidza-btn w-full text-center mt-3" style="background:#e5e7eb;color:#9ca3af">Berakhir</span>
                @else
                    <a href="{{ route('promo.pesan', $product->slug) }}"
                       class="kidza-btn kidza-btn-green w-full text-center mt-3" style="font-size:.9rem">
                        <span class="anim-tunjuk">👉</span> Pesan Sekarang
                    </a>
                @endif
            </div>
        @endforeach
    </div>

    <p class="text-center text-xs mt-6" style="color:#a39e93">Login dengan akun Google untuk melanjutkan pemesanan</p>
    <p class="text-center text-xs mt-2" style="color:#a39e93">© {{ date('Y') }} Rumah Cahaya</p>
</div>

@if(!$berakhir)
<script>
(function () {
    const akhir = new Date(@json($kampanye->berakhir_at->toIso8601String())).getTime();
    const d = document.getElementById('cd-d'), h = document.getElementById('cd-h'),
          m = document.getElementById('cd-m'), s = document.getElementById('cd-s');
    function tick() {
        let sisa = Math.max(0, Math.floor((akhir - Date.now()) / 1000));
        d.textContent = Math.floor(sisa / 86400);
        h.textContent = String(Math.floor(sisa % 86400 / 3600)).padStart(2, '0');
        m.textContent = String(Math.floor(sisa % 3600 / 60)).padStart(2, '0');
        s.textContent = String(sisa % 60).padStart(2, '0');
        if (sisa <= 0) location.reload();
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endif
</body>
</html>
