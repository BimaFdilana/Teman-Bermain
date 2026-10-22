<x-landing-layout>
@section('title', 'Semua Produk — Rumah Cahaya-bks')

<section class="kidza-section" style="background:#F9F9F6;min-height:70vh">
    <div class="kidza-wrap">
        <span class="kidza-kicker">Katalog</span>
        <h1 class="kidza-h2 mb-2">Semua Produk</h1>
        <p class="text-lg mb-8" style="color:#4b5563">Ebook interaktif, MiniApp game, dan paket hemat untuk anak.</p>

        <!-- Filter -->
        <form method="GET" action="{{ route('produk.index') }}" class="kidza-card p-4 mb-8 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="text-sm font-bold block mb-1">Cari</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Judul produk..."
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-[#167941]">
            </div>
            <div>
                <label class="text-sm font-bold block mb-1">Tipe</label>
                <select name="tipe" class="border-2 border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-[#167941]">
                    <option value="">Semua</option>
                    <option value="ebook" @selected(($filters['tipe'] ?? '') === 'ebook')>Ebook</option>
                    <option value="miniapp" @selected(($filters['tipe'] ?? '') === 'miniapp')>MiniApp</option>
                    <option value="bundle" @selected(($filters['tipe'] ?? '') === 'bundle')>Paket</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-bold block mb-1">Jenjang</label>
                <select name="jenjang" class="border-2 border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-[#167941]">
                    <option value="">Semua</option>
                    @foreach($jenjangs as $j)
                        <option value="{{ $j }}" @selected(($filters['jenjang'] ?? '') === $j)>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="kidza-btn kidza-btn-green">Filter</button>
            @if(array_filter($filters))
                <a href="{{ route('produk.index') }}" class="kidza-link text-sm">Reset</a>
            @endif
        </form>

        <!-- Grid -->
        @if($products->count())
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="flex justify-center">{{ $products->links() }}</div>
        @else
            <div class="kidza-card p-12 text-center">
                <div class="text-5xl mb-4">🔍</div>
                <p class="font-bold text-lg" style="font-family:'Quicksand'">Tidak ada produk yang cocok</p>
                <p style="color:#4b5563">Coba kata kunci atau filter lain.</p>
            </div>
        @endif
    </div>
</section>
@include('partials.tombol-home')
</x-landing-layout>
