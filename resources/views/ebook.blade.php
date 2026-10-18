<x-app-layout>
    <h1 class="font-display text-2xl font-bold mb-1">Ebook Milikku</h1>
    <p class="text-sm opacity-70 mb-5">Koleksi Ebook yang sudah kamu redeem.</p>

    @if($ebooks->count())
        <div class="grid grid-cols-2 gap-4">
            @foreach($ebooks as $ebook)
                <div class="brutal-card overflow-hidden">
                    @if($ebook->cover_path)
                        <img src="{{ $ebook->coverUrl() }}" alt="{{ $ebook->judul }}" class="w-full aspect-[4/5] object-cover">
                    @endif
                    <div class="p-3">
                        <p class="font-display font-bold text-sm leading-tight mb-2">{{ $ebook->judul }}</p>
                        <a href="{{ route('ebook.baca', $ebook) }}" class="brutal-badge" style="background:#167941;color:#fff">Baca →</a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="brutal-card p-8 text-center">
            <div class="text-5xl mb-3">📚</div>
            <p class="font-bold">Belum ada Ebook</p>
            <p class="text-sm opacity-70 mt-1">Beli Ebook anak lalu redeem kodenya di Home.</p>
            <a href="{{ route('produk.index', ['tipe' => 'ebook']) }}" class="brutal-btn brutal-btn-green mt-4">Lihat Ebook</a>
        </div>
    @endif
</x-app-layout>
