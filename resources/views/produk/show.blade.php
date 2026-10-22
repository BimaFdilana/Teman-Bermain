<x-landing-layout>
@section('title', $product->judul . ' — Rumah Cahaya-bks')

<section class="kidza-section" style="background:#F9F9F6">
    <div class="kidza-wrap">
        @if(session('info'))
            <div class="kidza-card p-4 mb-6" style="background:#FFF9EC;border-color:#F3A712">
                <p class="font-bold">{{ session('info') }}</p>
            </div>
        @endif
        <a href="{{ route('produk.index') }}" class="kidza-link text-sm mb-6 inline-block">← Kembali ke katalog</a>

        <div class="grid md:grid-cols-2 gap-10 items-start">
            <!-- Cover -->
            <div class="kidza-card p-3 relative">
                @if($product->cover_path)
                    <img src="{{ $product->coverUrl() }}" alt="{{ $product->judul }}" class="rounded-3xl w-full aspect-[4/5] object-cover">
                @endif
                <span class="kidza-badge kidza-badge-{{ $product->tipe }} absolute top-6 left-6">{{ $product->labelTipe() }}</span>
            </div>

            <!-- Info -->
            <div>
                @if($product->jenjang)
                    <p class="font-bold uppercase tracking-wide text-sm mb-2" style="color:#0077B6">{{ $product->jenjang }}</p>
                @endif
                <h1 class="font-bold text-3xl md:text-4xl mb-4 leading-tight" style="font-family:'Quicksand'">{{ $product->judul }}</h1>
                <p class="text-lg mb-6" style="color:#4b5563">{{ $product->deskripsi }}</p>

                @if($product->fitur)
                    <ul class="mb-6 space-y-2">
                        @foreach($product->fitur as $f)
                            <li class="flex items-start gap-2">
                                <span class="font-bold" style="color:#167941">✓</span>
                                <span>{{ $f }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if($product->tipe === 'bundle' && $product->bundle)
                    <div class="kidza-card p-5 mb-6" style="background:#FFF9EC">
                        <h3 class="font-bold mb-3" style="font-family:'Quicksand'">Isi paket ini:</h3>
                        <ul class="space-y-2">
                            @foreach($product->bundle->items as $item)
                                <li class="flex items-center gap-2 text-sm">
                                    <span class="kidza-badge kidza-badge-{{ $item->tipe }}" style="font-size:.65rem">{{ $item->labelTipe() }}</span>
                                    <a href="{{ route('produk.show', $item->slug) }}" class="underline hover:opacity-70">{{ $item->judul }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Harga + Beli -->
                <div class="kidza-card p-6" style="background:#fff">
                    <div class="flex items-baseline gap-3 mb-1">
                        <span class="font-bold text-3xl" style="font-family:'Quicksand';color:#167941">{{ $product->hargaRupiah() }}</span>
                        @if($product->isPromo())
                            <span class="kidza-price-coret text-lg">{{ $product->hargaCoretRupiah() }}</span>
                            <span class="kidza-badge" style="background:#e74c3c">Hemat!</span>
                        @endif
                    </div>
                    <p class="text-sm mb-5" style="color:#6b7280">Sekali bayar, akses selamanya.</p>
                    @if(auth()->check() && auth()->user()->hasProduct($product->id))
                        <a href="{{ route('home') }}" class="kidza-btn kidza-btn-blue w-full text-lg">Sudah Milikmu — Buka di Home</a>
                    @elseif($product->stok !== null && $product->stok <= 0)
                        <button disabled class="kidza-btn w-full text-lg opacity-50 cursor-not-allowed" style="background:#ccc">Stok Habis</button>
                    @else
                        <a href="{{ route('promo.pesan', $product->slug) }}" class="kidza-btn kidza-btn-green w-full text-lg">Beli Sekarang</a>
                        <p class="text-xs text-center mt-2" style="color:#6b7280">Tanpa perlu login — checkout sebagai tamu, bayar via QRIS/VA, kode redeem dikirim setelah pembayaran diverifikasi</p>
                    @endif
                </div>
            </div>
        </div>

        @if($product->tipe === 'miniapp')
            <!-- Cara Akses MiniApps (PRD §4.3) -->
            <div class="mt-14">
                <h2 class="kidza-h2 text-center mb-8">Cara Akses MiniApp</h2>
                <div class="grid md:grid-cols-5 gap-4">
                    @php
                    $cara = [
                        ['#167941', 'Pilih & beli MiniApp ini'],
                        ['#0077B6', 'Bayar via QRIS'],
                        ['#F3A712', 'Terima Redeem Code via WA'],
                        ['#e74c3c', 'Redeem & mainkan'],
                        ['#8e44ad', '(Opsional) Pasang sebagai aplikasi di HP'],
                    ];
                    @endphp
                    @foreach($cara as $i => $c)
                        <div class="kidza-card p-5 text-center">
                            <div class="kidza-step-num mx-auto mb-3" style="background:{{ $c[0] }}">{{ $i + 1 }}</div>
                            <p class="text-sm font-bold">{{ $c[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($terkait->count())
            <div class="mt-14">
                <h2 class="kidza-h2 mb-6">Mungkin kamu suka</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
                    @foreach($terkait as $t)
                        <x-product-card :product="$t" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
</x-landing-layout>
