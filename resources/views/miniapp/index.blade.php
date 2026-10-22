<x-app-layout>
    <h1 class="font-display text-3xl font-bold">MiniApp 🎮</h1>
    <p class="text-sm font-semibold mt-1 mb-5" style="color:#7A7261">Game edukasi milikmu — main langsung atau pasang di HP.</p>

    @if($miniapps->count())
        @php $jenjangList = $miniapps->pluck('jenjang')->filter()->unique()->values(); @endphp
        @if($jenjangList->count() > 1)
            <div class="flex gap-2 mb-5" id="filter-jenjang">
                <button type="button" class="rc-chip rc-chip-aktif" data-jenjang="">Semua</button>
                @foreach($jenjangList as $j)
                    <button type="button" class="rc-chip" data-jenjang="{{ $j }}">{{ $j }}</button>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4" id="grid-miniapp">
            @foreach($miniapps as $app)
                <div class="rc-card overflow-hidden flex flex-col" data-jenjang="{{ $app->jenjang }}">
                    @if($app->cover_path)
                        <img src="{{ $app->coverUrl() }}" alt="{{ $app->judul }}" class="w-full aspect-[4/3] object-cover">
                    @else
                        <div class="w-full aspect-[4/3] flex items-center justify-center text-5xl" style="background:linear-gradient(135deg,#B7E4C7,#3E9B4F)">🎮</div>
                    @endif
                    <div class="p-3.5 flex flex-col flex-1">
                        <p class="font-display font-bold text-sm leading-tight">{{ $app->judul }}</p>
                        @if($app->jenjang)
                            <p class="text-xs font-semibold mt-1" style="color:#7A7261">{{ $app->jenjang }}</p>
                        @endif
                        <div class="mt-auto pt-3 flex flex-col gap-2">
                            <a href="{{ route('miniapp.main', $app) }}" class="rc-btn rc-btn-ungu w-full" style="padding:.5rem;font-size:.85rem">▶ Main</a>
                            <a href="{{ route('miniapp.install', $app) }}" class="rc-btn rc-btn-kuning w-full" style="padding:.5rem;font-size:.85rem">📲 Install</a>
                        </div>
                    </div>
                </div>
            @endforeach

            <a href="{{ route('produk.index', ['tipe' => 'miniapp']) }}" class="flex flex-col items-center justify-center text-center p-4"
               style="border:3px dashed #D8CC9E;border-radius:1.5rem;min-height:10rem">
                <span class="text-4xl mb-2">🛒</span>
                <span class="font-display font-bold text-sm">Tambah Koleksi</span>
                <span class="text-xs font-semibold mt-1" style="color:#7A7261">Lihat MiniApp lain di katalog</span>
            </a>
        </div>

        <script>
        document.querySelectorAll('#filter-jenjang .rc-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                document.querySelectorAll('#filter-jenjang .rc-chip').forEach(c => c.classList.remove('rc-chip-aktif'));
                chip.classList.add('rc-chip-aktif');
                const j = chip.dataset.jenjang;
                document.querySelectorAll('#grid-miniapp [data-jenjang]').forEach(function (card) {
                    card.style.display = (!j || card.dataset.jenjang === j) ? '' : 'none';
                });
            });
        });
        </script>
    @else
        <div class="rc-card p-8 text-center">
            <div class="text-5xl mb-3">🎮</div>
            <p class="font-display font-bold">Belum ada MiniApp</p>
            <p class="text-sm font-semibold mt-1" style="color:#7A7261">Beli MiniApp seru lalu redeem kodenya di Home.</p>
            <a href="{{ route('produk.index', ['tipe' => 'miniapp']) }}" class="rc-btn rc-btn-kuning mt-4">Lihat MiniApp</a>
        </div>
    @endif
</x-app-layout>
