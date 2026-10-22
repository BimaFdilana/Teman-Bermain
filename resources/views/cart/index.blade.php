<x-landing-layout>
@section('title', 'Keranjang — Rumah Cahaya-bks')

<section class="kidza-section" style="background:#F9F9F6">
    <div class="kidza-wrap" style="max-width:60rem">
        <div class="text-center mb-8">
            <span class="kidza-kicker">Tanpa login dulu</span>
            <h1 class="kidza-h2 mb-2">🛒 Keranjang Pesanan</h1>
        </div>

        @if(session('sukses'))<div class="kidza-card p-4 mb-4" style="background:#e8f6ee"><p class="font-bold">{{ session('sukses') }}</p></div>@endif
        @if(session('gagal'))<div class="kidza-card p-4 mb-4" style="background:#fdecea"><p class="font-bold">{{ session('gagal') }}</p></div>@endif

        <div class="grid md:grid-cols-3 gap-6">
            {{-- Kiri: isi cart + rekomendasi --}}
            <div class="md:col-span-2">
                <div class="kidza-card p-5 mb-6">
                    <h2 class="font-bold text-lg mb-4" style="font-family:'Quicksand'">Produk yang dipilih ({{ $items->count() }})</h2>
                    @forelse($items as $p)
                        <div class="flex gap-4 items-center py-3" style="border-bottom:1px dashed #e5e5e5">
                            @if($p->coverUrl())
                                <img src="{{ $p->coverUrl() }}" alt="{{ $p->judul }}" class="rounded-xl" style="width:64px;height:80px;object-fit:cover">
                            @endif
                            <div class="flex-1">
                                <p class="font-bold">{{ $p->judul }}</p>
                                <p class="text-sm" style="color:#6b7280">{{ ucfirst($p->tipe) }}</p>
                                <p class="font-bold" style="color:#167941">Rp {{ number_format($p->harga, 0, ',', '.') }}</p>
                            </div>
                            <form method="POST" action="{{ route('cart.hapus', $p->slug) }}">
                                @csrf
                                <button class="kidza-btn" style="background:#fdecea" title="Hapus">🗑️</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-center py-6" style="color:#6b7280">Keranjang masih kosong.</p>
                    @endforelse
                </div>

                @if($rekomendasi->isNotEmpty())
                    <div class="kidza-card p-5">
                        <h2 class="font-bold text-lg mb-1" style="font-family:'Quicksand'">🎁 Lengkapi belanjamu</h2>
                        <p class="text-sm mb-3" style="color:#6b7280">Produk lainnya:</p>
                        <div>
                            @foreach($rekomendasi as $r)
                                <div class="flex items-center gap-3 py-2" style="border-bottom:1px dashed #e5e5e5">
                                    @if($r->coverUrl())
                                        <img src="{{ $r->coverUrl() }}" alt="{{ $r->judul }}" class="rounded-lg" style="width:44px;height:56px;object-fit:cover">
                                    @endif
                                    <div class="flex-1">
                                        <p class="font-bold text-sm leading-tight">{{ $r->judul }}</p>
                                        <p class="text-sm">
                                            @if($r->harga_coret)<s class="text-xs" style="color:#9ca3af">Rp {{ number_format($r->harga_coret, 0, ',', '.') }}</s> @endif
                                            <strong style="color:#167941">Rp {{ number_format($r->harga, 0, ',', '.') }}</strong>
                                        </p>
                                    </div>
                                    <form method="POST" action="{{ route('cart.tambah', $r->slug) }}">
                                        @csrf
                                        <button class="kidza-btn kidza-btn-yellow" style="padding:.35rem .9rem;font-size:.85rem">＋ Tambah</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Kanan: ringkasan --}}
            <div>
                <div class="kidza-card p-5" style="position:sticky;top:1rem">
                    <h2 class="font-bold text-lg mb-4" style="font-family:'Quicksand'">Ringkasan</h2>
                    <div class="text-sm space-y-2 mb-4">
                        <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ number_format($ringkasan['subtotal'], 0, ',', '.') }}</span></div>
                        @if($ringkasan['diskon'] > 0)
                            <div class="flex justify-between" style="color:#167941"><span>Diskon ({{ $ringkasan['kode_diskon'] }})</span><span>−Rp {{ number_format($ringkasan['diskon'], 0, ',', '.') }}</span></div>
                        @endif
                        @if($ringkasan['pajak'] > 0)
                            <div class="flex justify-between"><span>Pajak ({{ $ringkasan['pajak_persen'] }}%)</span><span>Rp {{ number_format($ringkasan['pajak'], 0, ',', '.') }}</span></div>
                        @endif
                        <div class="flex justify-between font-bold text-lg pt-2" style="border-top:2px solid #1a1a1a">
                            <span>Total</span><span style="color:#167941">Rp {{ number_format($ringkasan['total'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('cart.diskon') }}" class="flex gap-2 mb-4">
                        @csrf
                        <input type="text" name="kode_diskon" placeholder="Kode diskon" value="{{ $ringkasan['kode_diskon'] }}"
                               class="flex-1 min-w-0 border-2 border-gray-300 rounded-xl px-3 py-2 text-base uppercase">
                        <button class="kidza-btn" style="background:#fff">Pakai</button>
                    </form>

                    @if($items->isNotEmpty())
                        <a href="{{ route('cart.checkout') }}" class="kidza-btn kidza-btn-green w-full text-lg text-center">Lakukan Pesanan →</a>
                    @endif
                    <p class="text-xs text-center mt-3" style="color:#6b7280">Tanpa perlu login / daftar dulu.</p>
                </div>
            </div>
        </div>
    </div>
</section>
</x-landing-layout>
