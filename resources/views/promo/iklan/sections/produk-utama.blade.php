{{-- Section: Produk Utama (bintang iklan, tampil besar) --}}
@php $utama = $kampanye->produkPrimary; @endphp
@if($utama)
    <div class="mb-10">
        <p class="text-center font-extrabold mb-3" style="color:#A63D2F;font-size:.8rem">⭐ PRODUK UTAMA</p>
        @if($utama->coverUrl())
            <img src="{{ $utama->coverUrl() }}" alt="{{ $utama->judul }}" class="rounded-2xl w-full object-cover mb-3" style="aspect-ratio:16/10">
        @endif
        <span class="kidza-kicker mb-2" style="font-size:.65rem">{{ $tipeLabel[$utama->tipe] ?? $utama->tipe }}</span>
        <p class="font-extrabold mb-2" style="font-size:1.3rem;color:#14532D;line-height:1.3">{{ $utama->judul }}</p>
        @if($utama->deskripsi)
            <p class="text-sm mb-3" style="color:#5B5344;line-height:1.6">{{ $utama->deskripsi }}</p>
        @endif
        <div class="flex items-baseline justify-center gap-2 mb-3">
            <span class="font-extrabold" style="font-size:1.4rem;color:#1E7B34">Rp {{ number_format($utama->harga, 0, ',', '.') }}</span>
            @if($utama->harga_coret)
                <span class="text-sm line-through" style="color:#a39e93">Rp {{ number_format($utama->harga_coret, 0, ',', '.') }}</span>
            @endif
        </div>
        <div class="text-center">
            <a href="{{ route('promo.pesan', $utama->slug) }}" class="kidza-btn kidza-btn-green" style="font-size:1rem;display:inline-block">
                <span class="anim-tunjuk">👉</span> Pesan Sekarang <span class="anim-tunjuk-kiri">👈</span>
            </a>
        </div>
    </div>
    <div class="my-12" aria-hidden="true" style="height:2px;background:#F0E7CC;border-radius:2px"></div>
@endif
