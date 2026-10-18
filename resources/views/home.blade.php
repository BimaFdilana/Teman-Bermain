<x-app-layout>
    @if(session('sukses'))
        <div class="brutal-card p-4 mb-5" style="background:#e8f6ee">
            <p class="font-bold">{{ session('sukses') }}</p>
        </div>
    @endif
    @if(session('gagal'))
        <div class="brutal-card p-4 mb-5" style="background:#fdecea">
            <p class="font-bold">{{ session('gagal') }}</p>
        </div>
    @endif

    <!-- Sapaan -->
    <div class="brutal-card p-5 mb-5" style="background: linear-gradient(135deg, #fff 60%, #F7EDCC);">
        <h1 class="font-display text-2xl font-bold">Hai, {{ explode(' ', Auth::user()->name)[0] }}! 👋</h1>
        <p class="text-sm opacity-70 mt-1">Siap belajar dan bermain hari ini?</p>
    </div>

    <!-- Redeem Code -->
    <div class="brutal-card p-5 mb-5">
        <h2 class="font-display text-lg font-bold mb-1">Punya Kode Redeem?</h2>
        <p class="text-sm opacity-70 mb-3">Masukkan kode dari WhatsApp untuk membuka Ebook/MiniApp kamu.</p>
        <form method="POST" action="{{ route('redeem.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="code" class="brutal-input uppercase" placeholder="RC-XXXX-XXXX" style="font-family:'Fredoka'" required>
            <button type="submit" class="brutal-btn brutal-btn-yellow whitespace-nowrap">Redeem</button>
        </form>
    </div>

    <!-- MiniApps milikku -->
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-display text-lg font-bold">MiniApps Milikku</h2>
        <a href="{{ route('produk.index', ['tipe' => 'miniapp']) }}" class="text-sm font-bold underline">+ Beli</a>
    </div>
    @if($miniapps->count())
        <div class="grid grid-cols-2 gap-4 mb-5">
            @foreach($miniapps as $app)
                <div class="brutal-card overflow-hidden">
                    @if($app->cover_path)
                        <img src="{{ asset($app->cover_path) }}" alt="{{ $app->judul }}" class="w-full aspect-[4/5] object-cover">
                    @endif
                    <div class="p-3">
                        <p class="font-display font-bold text-sm leading-tight mb-2">{{ $app->judul }}</p>
                        <span class="brutal-badge" style="background:#0077B6;color:#fff">Mainkan</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="brutal-card p-8 text-center mb-5">
            <div class="text-5xl mb-3">🎮</div>
            <p class="font-bold">Belum ada MiniApp</p>
            <p class="text-sm opacity-70 mt-1">Beli MiniApp seru lalu redeem kodenya di atas.</p>
        </div>
    @endif

    <!-- Pesanan -->
    <a href="{{ route('checkout.riwayat') }}" class="brutal-card p-4 flex items-center justify-between mb-5">
        <span class="font-display font-bold">🧾 Pesanan Saya</span>
        <span class="font-bold">→</span>
    </a>
</x-app-layout>
