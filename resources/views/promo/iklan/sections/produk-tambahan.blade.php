{{-- Section: Produk Tambahan (card atau list sesuai pengaturan) --}}
@php $tambahan = $kampanye->produkSecondary; @endphp
@if($tambahan->isNotEmpty())
    <div class="mb-10">
        <p class="text-center font-extrabold mb-4" style="color:#14532D">🎁 Produk Tambahan</p>
        @if(($kampanye->tampilan_secondary ?? 'card') === 'list')
            {{-- Tampilan list: thumbnail kecil + judul + harga + tombol tambah --}}
            @foreach($tambahan as $p)
                <div class="flex items-center gap-3 rounded-2xl p-3 mb-2 bg-white" style="border:2px solid #F0E7CC">
                    @if($p->coverUrl())
                        <img src="{{ $p->coverUrl() }}" alt="{{ $p->judul }}" class="rounded-xl object-cover shrink-0" style="width:64px;height:64px">
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-extrabold text-sm truncate" style="color:#14532D">{{ $p->judul }}</p>
                        <p class="text-sm"><b style="color:#1E7B34">Rp {{ number_format($p->harga, 0, ',', '.') }}</b>
                            @if($p->harga_coret)
                                <span class="text-xs line-through" style="color:#a39e93">Rp {{ number_format($p->harga_coret, 0, ',', '.') }}</span>
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('promo.pesan', $p->slug) }}" class="text-xs font-extrabold px-3 py-2 rounded-full shrink-0" style="background:#F5B301;color:#1a1a1a">+ Lihat</a>
                </div>
            @endforeach
        @else
            {{-- Tampilan card: grid 2 kolom --}}
            <div class="grid grid-cols-2 gap-3">
                @foreach($tambahan as $p)
                    <div class="rounded-2xl p-3 bg-white" style="border:2px solid #F0E7CC">
                        @if($p->coverUrl())
                            <img src="{{ $p->coverUrl() }}" alt="{{ $p->judul }}" class="rounded-xl w-full object-cover mb-2" style="aspect-ratio:1/1">
                        @endif
                        <p class="font-extrabold text-xs mb-1" style="color:#14532D;line-height:1.3">{{ $p->judul }}</p>
                        <p class="text-xs mb-2"><b style="color:#1E7B34">Rp {{ number_format($p->harga, 0, ',', '.') }}</b></p>
                        <a href="{{ route('promo.pesan', $p->slug) }}" class="kidza-btn kidza-btn-green w-full text-center" style="font-size:.75rem;padding:.4rem">Pesan</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    <div class="my-12" aria-hidden="true" style="height:2px;background:#F0E7CC;border-radius:2px"></div>
@endif
