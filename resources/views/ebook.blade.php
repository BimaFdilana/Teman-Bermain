<x-app-layout>
    <h1 class="font-display text-3xl font-bold">Ebook 📚</h1>
    <p class="text-sm font-semibold mt-1 mb-5" style="color:#7A7261">Koleksi Ebook yang sudah kamu miliki.</p>

    @if($ebooks->count())
        @php $unggulan = $ebooks->first(); $sisanya = $ebooks->skip(1); @endphp

        <!-- Ebook unggulan (terbaru dimiliki) -->
        <div class="rc-card p-4 mb-6">
            <div class="flex gap-4">
                @if($unggulan->cover_path)
                    <img src="{{ $unggulan->coverUrl() }}" alt="{{ $unggulan->judul }}" class="w-24 rounded-2xl object-cover flex-none" style="aspect-ratio:3/4">
                @else
                    <div class="w-24 rounded-2xl flex-none flex items-center justify-center text-4xl" style="aspect-ratio:3/4;background:linear-gradient(150deg,#B7E4C7,#167941)">📚</div>
                @endif
                <div class="min-w-0">
                    <span class="rc-chip" style="background:#FFF3D6;color:#9A6B00;padding:.2rem .7rem">⭐ Unggulan</span>
                    <h2 class="font-display font-bold leading-snug mt-1.5">{{ $unggulan->judul }}</h2>
                    <p class="text-xs font-semibold mt-1" style="color:#7A7261">PDF Interaktif{{ $unggulan->jenjang ? ' • '.$unggulan->jenjang : '' }} • Bisa diunduh</p>
                    <a href="{{ route('ebook.baca', $unggulan) }}" class="rc-btn rc-btn-kuning mt-3" style="padding:.55rem 1.1rem;font-size:.9rem">Baca Sekarang →</a>
                </div>
            </div>
        </div>

        @if($sisanya->count())
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="font-display text-lg font-bold">Semua Ebook</h2>
                <span class="text-sm font-extrabold" style="color:#7A7261">{{ $ebooks->count() }} ebook</span>
            </div>
            <div class="flex flex-col gap-3">
                @foreach($sisanya as $ebook)
                    <div class="rc-card p-3.5 flex items-center gap-3">
                        @if($ebook->cover_path)
                            <img src="{{ $ebook->coverUrl() }}" alt="{{ $ebook->judul }}" class="w-14 h-14 rounded-2xl object-cover flex-none">
                        @else
                            <div class="w-14 h-14 rounded-2xl flex-none flex items-center justify-center text-2xl" style="background:#E8F6EE">📚</div>
                        @endif
                        <div class="min-w-0">
                            <p class="font-display font-bold text-sm leading-tight">{{ $ebook->judul }}</p>
                            <p class="text-xs font-semibold mt-0.5" style="color:#7A7261">PDF Interaktif{{ $ebook->jenjang ? ' • '.$ebook->jenjang : '' }}</p>
                        </div>
                        <a href="{{ route('ebook.baca', $ebook) }}" class="rc-btn rc-btn-ungu ml-auto flex-none" style="padding:.5rem 1rem;font-size:.85rem">Baca</a>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <div class="rc-card p-8 text-center">
            <div class="text-5xl mb-3">📚</div>
            <p class="font-display font-bold">Belum ada Ebook</p>
            <p class="text-sm font-semibold mt-1" style="color:#7A7261">Beli Ebook anak lalu redeem kodenya di Home.</p>
            <a href="{{ route('produk.index', ['tipe' => 'ebook']) }}" class="rc-btn rc-btn-kuning mt-4">Lihat Ebook</a>
        </div>
    @endif
</x-app-layout>
