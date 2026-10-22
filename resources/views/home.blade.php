<x-app-layout>
    @if(session('sukses'))
        <div class="rc-card p-4 mb-5" style="background:#e8f6ee">
            <p class="font-bold">{{ session('sukses') }}</p>
        </div>
    @endif
    @if(session('gagal'))
        <div class="rc-card p-4 mb-5" style="background:#fdecea">
            <p class="font-bold">{{ session('gagal') }}</p>
        </div>
    @endif

    <!-- Sapaan -->
    <div class="flex items-center gap-3 mb-5 px-1">
        <div class="w-12 h-12 rounded-full flex items-center justify-center font-display text-xl font-bold flex-none"
             style="background:#FFC531;border:3px solid #fff;box-shadow:0 4px 10px rgba(120,90,0,.18)">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
        <div>
            <p class="text-xs font-extrabold" style="color:#A08C5B">Selamat datang,</p>
            <h1 class="font-display text-2xl font-bold leading-tight">{{ explode(' ', Auth::user()->name)[0] }} 👋</h1>
        </div>
    </div>

    <!-- Hero katalog -->
    <div class="rc-card p-5 mb-4">
        <div class="flex items-center gap-3">
            <div class="text-4xl">🌈</div>
            <div>
                <h2 class="font-display text-lg font-bold leading-snug">Siap bermain & belajar hari ini?</h2>
                <p class="text-sm font-semibold" style="color:#7A7261">Ide main dari alam & tradisi untuk anak usia 2–6 tahun.</p>
            </div>
        </div>
        <a href="{{ route('produk.index') }}" class="rc-btn rc-btn-kuning w-full mt-4">Lihat Katalog →</a>
    </div>

    <!-- Redeem Code -->
    <div class="rc-card p-5 mb-6">
        <h2 class="font-display text-lg font-bold mb-1">🎁 Punya Kode Redeem?</h2>
        <p class="text-sm font-semibold mb-3" style="color:#7A7261">Masukkan kode dari WhatsApp untuk membuka Ebook/MiniApp kamu.</p>
        <form method="POST" action="{{ route('redeem.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="code" class="rc-input uppercase" placeholder="RC-XXXX-XXXX" style="font-family:'Fredoka'" required>
            <button type="submit" class="rc-btn rc-btn-ungu whitespace-nowrap">Redeem</button>
        </form>
    </div>

    <!-- MiniApps milikku -->
    <div class="flex items-center justify-between mb-3 px-1">
        <h2 class="font-display text-lg font-bold">🎮 MiniApp Milikku</h2>
        <a href="{{ route('miniapp.index') }}" class="text-sm font-extrabold" style="color:#2E7CF6">Lihat semua →</a>
    </div>
    @if($miniapps->count())
        <div class="grid grid-cols-2 gap-4 mb-6">
            @foreach($miniapps as $app)
                <div class="rc-card overflow-hidden">
                    @if($app->cover_path)
                        <img src="{{ $app->coverUrl() }}" alt="{{ $app->judul }}" class="w-full aspect-[4/3] object-cover">
                    @endif
                    <div class="p-3">
                        <p class="font-display font-bold text-sm leading-tight mb-2">{{ $app->judul }}</p>
                        <a href="{{ route('miniapp.main', $app) }}" class="rc-btn rc-btn-kuning w-full" style="padding:.5rem;font-size:.85rem">▶ Main</a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rc-card p-8 text-center mb-6">
            <div class="text-5xl mb-3">🎮</div>
            <p class="font-display font-bold">Belum ada MiniApp</p>
            <p class="text-sm font-semibold mt-1" style="color:#7A7261">Beli MiniApp seru lalu redeem kodenya di atas.</p>
        </div>
    @endif

    <!-- Ebook milikku -->
    <div class="flex items-center justify-between mb-3 px-1">
        <h2 class="font-display text-lg font-bold">📚 Ebook Milikku</h2>
        <a href="{{ route('ebook') }}" class="text-sm font-extrabold" style="color:#2E7CF6">Lihat semua →</a>
    </div>
    @if($ebooks->count())
        <div class="flex flex-col gap-3 mb-6">
            @foreach($ebooks as $ebook)
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
    @else
        <div class="rc-card p-8 text-center mb-6">
            <div class="text-5xl mb-3">📚</div>
            <p class="font-display font-bold">Belum ada Ebook</p>
            <p class="text-sm font-semibold mt-1" style="color:#7A7261">Beli Ebook anak lalu redeem kodenya di atas.</p>
        </div>
    @endif

    <!-- Pesanan -->
    <a href="{{ route('checkout.riwayat') }}" class="rc-card p-4 flex items-center justify-between">
        <span class="font-display font-bold">🧾 Pesanan Saya</span>
        <span class="font-bold" style="color:#7A7261">→</span>
    </a>
</x-app-layout>
