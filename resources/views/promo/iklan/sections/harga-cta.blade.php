{{-- Section: Harga Spesial + Tombol Pesan (hanya produk utama) --}}
@php $utama = $kampanye->produkPrimary; @endphp
@if($utama)
    <div class="text-center mt-12 mb-8">
        <p class="font-extrabold mb-3" style="font-size:1.7rem;color:#14532D">HARGA SPESIAL</p>
        <div class="mb-2">
            <p class="text-xs font-bold" style="color:#a39e93">{{ $utama->judul }}</p>
            @if($utama->harga_coret)
                <span class="text-sm line-through" style="color:#a39e93">Rp {{ number_format($utama->harga_coret, 0, ',', '.') }}</span>
            @endif
            <p class="font-extrabold" style="font-size:1.6rem;color:#14532D">Rp {{ number_format($utama->harga, 0, ',', '.') }}</p>
        </div>
        <div class="text-center">
            <a href="{{ route('promo.pesan', $utama->slug) }}" class="kidza-btn kidza-btn-green mt-2" style="font-size:1rem;display:inline-block">
                <span class="anim-tunjuk">👉</span> Pesan Sekarang <span class="anim-tunjuk-kiri">👈</span>
            </a>
        </div>
    </div>
@endif
