@php
$tipeLabel = ['ebook' => '📚 Ebook', 'miniapp' => '🎮 MiniApp', 'bundle' => '🎁 Paket Bundle'][$product->tipe] ?? $product->tipe;
$cover = $product->coverUrl();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->judul }} — Rumah Cahaya</title>
    <meta name="description" content="{{ Str::limit(strip_tags($product->deskripsi ?? ''), 150) }}">
    <meta property="og:title" content="{{ $product->judul }} — Rumah Cahaya">
    <meta property="og:description" content="{{ Str::limit(strip_tags($product->deskripsi ?? ''), 150) }}">
    @if($cover)<meta property="og:image" content="{{ $cover }}">@endif
    <meta property="og:type" content="website">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.meta-pixel-script')
    @include('partials.meta-pixel-events')
</head>
<body class="poster" style="background:#FFFEF7;min-height:100vh;min-height:100dvh">
    @include('partials.meta-pixel-noscript')
<div class="mx-auto px-4 py-8" style="max-width:28rem">

    {{-- Brand --}}
    <div class="text-center mb-5">
        <img src="{{ asset('images/logo-stacked.png') }}" alt="Rumah Cahaya" class="mx-auto mb-2" style="height:52px"
             onerror="this.style.display='none'">
        <p class="text-sm font-bold" style="color:#1E7B34">Rumah Cahaya</p>
    </div>

    {{-- Cover --}}
    <div class="kidza-card p-2 mb-5 kidza-float">
        @if($cover)
            <img src="{{ $cover }}" alt="{{ $product->judul }}" class="rounded-3xl w-full aspect-[4/3] object-cover">
        @else
            <div class="rounded-3xl w-full aspect-[4/3] flex items-center justify-center text-6xl" style="background:#FFF8E7">📚</div>
        @endif
    </div>

    {{-- Info --}}
    <div class="text-center mb-5">
        <div class="flex items-center justify-center gap-2 mb-3">
            <span class="kidza-kicker">{{ $tipeLabel }}</span>
            @if($product->jenjang)<span class="kidza-kicker poster-kicker-yellow">{{ $product->jenjang }}</span>@endif
        </div>
        <h1 class="font-extrabold mb-2" style="font-size:1.6rem;color:#14532D;line-height:1.25">{{ $product->judul }}</h1>
        <div class="flex items-baseline justify-center gap-2 mb-3">
            <span class="font-extrabold" style="font-size:1.5rem;color:#1E7B34">Rp {{ number_format($product->harga, 0, ',', '.') }}</span>
            @if($product->harga_coret)
                <span class="line-through" style="color:#a39e93">Rp {{ number_format($product->harga_coret, 0, ',', '.') }}</span>
            @endif
        </div>
        @if($product->deskripsi)
            <p class="text-sm mb-4" style="color:#5B5344">{{ Str::limit(strip_tags($product->deskripsi), 220) }}</p>
        @endif
        @if($product->tipe === 'bundle' && $product->bundle)
            <div class="poster-info-card text-left mb-4">
                <div class="poster-info-head" style="font-size:1rem">🎁 Isi paket</div>
                <ul class="p-6 pt-4 space-y-1 text-sm font-bold" style="color:#14532D">
                    @foreach($product->bundle->items as $item)
                        <li>✓ {{ $item->judul }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- CTA --}}
    <a href="{{ route('promo.pesan', $product->slug) }}"
       class="kidza-btn kidza-btn-green w-full text-center mb-2 js-meta-lead"
       data-meta-name="{{ $product->judul }}" data-meta-value="{{ $product->harga }}" style="font-size:1.1rem;padding:1rem">
        <span style="display:inline-flex;align-items:center;gap:.5rem;justify-content:center">
            <span style="background:#fff;border-radius:9999px;width:1.5rem;height:1.5rem;display:inline-flex;align-items:center;justify-content:center;font-weight:900;color:#4285F4">G</span>
            Pesan Sekarang
        </span>
    </a>
    <p class="text-center text-xs mb-6" style="color:#a39e93">Login dengan akun Google untuk melanjutkan</p>

    {{-- Cara beli --}}
    <div class="poster-info-card mb-6">
        <div class="poster-info-head" style="font-size:1rem">🛒 Cara beli</div>
        <ol class="p-6 pt-4 space-y-2 text-sm font-bold" style="color:#14532D">
            <li>1️⃣ Klik <b>Pesan Sekarang</b></li>
            <li>2️⃣ Login dengan akun Google</li>
            <li>3️⃣ Bayar via QRIS & upload bukti</li>
            <li>4️⃣ Kode akses dikirim via WhatsApp</li>
        </ol>
    </div>

    <div class="text-center mb-6">
        <a href="{{ route('produk.show', $product->slug) }}" class="text-sm font-bold underline" style="color:#1E7B34">Lihat detail lengkap →</a>
    </div>

    <p class="text-center text-xs" style="color:#a39e93">© {{ date('Y') }} Rumah Cahaya</p>
</div>
<script>
fbq('track', 'ViewContent', {
    content_ids: @json([(string) $product->id]),
    content_type: 'product',
    content_name: @json($product->judul),
    value: {{ (float) $product->harga }},
    currency: 'IDR',
});
</script>
</body>
</html>
