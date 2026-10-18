@props(['product'])

<a href="{{ route('produk.show', $product->slug) }}" class="kidza-card block">
    <div class="relative">
        @if($product->cover_path)
            <img src="{{ asset($product->cover_path) }}" alt="{{ $product->judul }}" class="w-full aspect-[4/5] object-cover">
        @else
            <div class="w-full aspect-[4/5] flex items-center justify-center text-6xl" style="background:#FFF6E0">
                {{ $product->tipe === 'miniapp' ? '🎮' : '📚' }}
            </div>
        @endif
        <span class="kidza-badge kidza-badge-{{ $product->tipe }} absolute top-3 left-3">{{ $product->labelTipe() }}</span>
        @if($product->isPromo())
            <span class="kidza-badge absolute top-3 right-3" style="background:#e74c3c">Promo</span>
        @endif
    </div>
    <div class="p-4">
        @if($product->jenjang)
            <p class="text-xs font-extrabold uppercase tracking-wide mb-1 poster-jenjang" style="color:#0077B6">{{ $product->jenjang }}</p>
        @endif
        <h3 class="font-extrabold text-lg leading-snug mb-2 poster-card-title">{{ $product->judul }}</h3>
        <div class="flex items-baseline gap-2">
            <span class="kidza-price">{{ $product->hargaRupiah() }}</span>
            @if($product->isPromo())
                <span class="kidza-price-coret">{{ $product->hargaCoretRupiah() }}</span>
            @endif
        </div>
    </div>
</a>
