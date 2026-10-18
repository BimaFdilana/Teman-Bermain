<x-landing-layout>
@section('title', 'Harga & Paket — Rumah Cahaya-bks')

<section class="kidza-section" style="background:#FFF9EC">
    <div class="kidza-wrap">
        <div class="text-center mb-10">
            <span class="kidza-kicker">Hemat dengan paket</span>
            <h1 class="kidza-h2 mb-3">Harga & Paket</h1>
            <p class="text-lg max-w-2xl mx-auto" style="color:#4b5563">
                Semua paket berisi Ebook dan/atau MiniApp pilihan. Satu kode redeem untuk seluruh isi paket —
                lebih murah daripada beli satuan.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 mb-12">
            @forelse($bundles as $bundle)
                <div class="kidza-card p-6 flex flex-col">
                    @if($bundle->cover_path)
                        <img src="{{ $bundle->coverUrl() }}" alt="{{ $bundle->judul }}" class="rounded-2xl w-full aspect-[16/10] object-cover mb-4">
                    @endif
                    @if($bundle->jenjang)
                        <p class="font-bold uppercase tracking-wide text-xs mb-1" style="color:#0077B6">{{ $bundle->jenjang }}</p>
                    @endif
                    <h3 class="font-bold text-xl mb-2" style="font-family:'Quicksand'">{{ $bundle->judul }}</h3>
                    <p class="text-sm mb-4 flex-1" style="color:#4b5563">{{ $bundle->deskripsi }}</p>

                    @if($bundle->bundle)
                        <ul class="text-sm mb-4 space-y-1">
                            @foreach($bundle->bundle->items as $item)
                                <li>✓ {{ $item->judul }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="flex items-baseline gap-2 mb-4">
                        <span class="kidza-price">{{ $bundle->hargaRupiah() }}</span>
                        @if($bundle->isPromo())
                            <span class="kidza-price-coret">{{ $bundle->hargaCoretRupiah() }}</span>
                        @endif
                    </div>
                    <a href="{{ route('produk.show', $bundle->slug) }}" class="kidza-btn kidza-btn-yellow w-full">Lihat Detail</a>
                </div>
            @empty
                <p class="col-span-3 text-center" style="color:#4b5563">Paket segera hadir.</p>
            @endforelse
        </div>

        <!-- Info pembayaran -->
        <div class="kidza-card p-8 text-center" style="background:#fff">
            <h2 class="font-bold text-2xl mb-3" style="font-family:'Quicksand'">Pembayaran mudah via QRIS</h2>
            <p class="max-w-2xl mx-auto" style="color:#4b5563">
                Scan kode QRIS dari e-wallet atau m-banking apa pun, upload bukti bayar,
                dan kode redeem dikirim ke WhatsApp kamu maks. 1×24 jam (jam kerja 08.00–20.00 WIB).
            </p>
        </div>
    </div>
</section>
</x-landing-layout>
