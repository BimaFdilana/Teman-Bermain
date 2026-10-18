<x-app-layout>
    <h1 class="font-display text-2xl font-bold mb-1">Pesanan Saya</h1>
    <p class="text-sm opacity-70 mb-5">Lacak status pembayaran & redeem code-mu.</p>

    @if(session('sukses'))
        <div class="brutal-card p-4 mb-5" style="background:#e8f6ee">
            <p class="font-bold">{{ session('sukses') }}</p>
        </div>
    @endif

    @forelse($orders as $order)
        <a href="{{ route('checkout.show', $order->kode_order) }}" class="brutal-card p-4 mb-4 block">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-display font-bold leading-tight">{{ $order->product->judul }}</p>
                    <p class="text-xs opacity-60 mt-1">{{ $order->kode_order }} · {{ $order->created_at->format('d M Y H:i') }}</p>
                </div>
                <span class="brutal-badge whitespace-nowrap" style="background:{{ $order->warnaStatus() }};{{ in_array($order->status, ['pembayaran']) ? 'color:#1a1a1a' : 'color:#fff' }}">
                    {{ $order->labelStatus() }}
                </span>
            </div>
            <p class="font-display font-bold mt-2" style="color:#167941">{{ $order->totalRupiah() }}</p>
        </a>
    @empty
        <div class="brutal-card p-8 text-center">
            <div class="text-5xl mb-3">🧾</div>
            <p class="font-bold">Belum ada pesanan</p>
            <p class="text-sm opacity-70 mt-1">Yuk mulai belanja Ebook & MiniApp seru!</p>
            <a href="{{ route('produk.index') }}" class="brutal-btn brutal-btn-green mt-4">Lihat Produk</a>
        </div>
    @endforelse

    @if($orders->hasPages())
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</x-app-layout>
