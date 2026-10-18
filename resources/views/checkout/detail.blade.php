<x-app-layout>
    <a href="{{ route('checkout.riwayat') }}" class="text-sm font-bold underline mb-4 inline-block">← Kembali</a>

    <div class="brutal-card p-5 mb-4">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <h1 class="font-display text-xl font-bold leading-tight">{{ $order->product->judul }}</h1>
                <p class="text-xs opacity-60 mt-1">{{ $order->kode_order }} · {{ $order->created_at->format('d M Y, H:i') }} WIB</p>
            </div>
            <span class="brutal-badge whitespace-nowrap" style="background:{{ $order->warnaStatus() }};color:#fff">{{ $order->labelStatus() }}</span>
        </div>

        <div class="text-sm space-y-1 mb-4">
            <p>Harga produk: <strong>{{ $order->product->hargaRupiah() }}</strong></p>
            @if($order->discountCode)
                <p>Diskon ({{ $order->discountCode->kode }}): <strong style="color:#167941">−{{ $order->discountCode->label() }}</strong></p>
            @endif
            <p class="font-display font-bold text-lg" style="color:#167941">Total bayar: {{ $order->totalRupiah() }}</p>
        </div>

        @if($order->status === 'pembayaran')
            <a href="{{ route('checkout.bayar', $order->kode_order) }}" class="brutal-btn brutal-btn-green w-full">Bayar Sekarang</a>
            <p class="text-xs opacity-60 mt-2 text-center">Batas bayar: {{ $order->batas_bayar->format('d M Y, H:i') }} WIB</p>
        @elseif($order->status === 'verifikasi')
            <p class="text-sm">🔍 Bukti pembayaran sedang diverifikasi admin (maks. 1×24 jam).</p>
        @elseif($order->status === 'ditolak')
            <p class="text-sm mb-1">😔 <strong>Alasan penolakan:</strong> {{ $order->alasan_tolak }}</p>
            @if($order->bolehUploadBukti())
                <a href="{{ route('checkout.bayar', $order->kode_order) }}" class="brutal-btn brutal-btn-yellow w-full mt-3">Upload Ulang Bukti</a>
            @endif
        @elseif($order->status === 'disetujui')
            <p class="text-sm">🎉 Disetujui! Kode redeem dikirim ke WhatsApp kamu — redeem di Home ya!</p>
            <a href="{{ route('home') }}" class="brutal-btn brutal-btn-green w-full mt-3">Ke Home</a>
        @else
            <p class="text-sm">⌛ Order ini kadaluarsa.</p>
        @endif
    </div>

    @if($order->bukti_path)
        <div class="brutal-card p-4">
            <p class="font-display font-bold mb-2 text-sm">Bukti pembayaran:</p>
            <img src="{{ asset('storage/' . $order->bukti_path) }}" alt="Bukti pembayaran" class="rounded-xl w-full">
        </div>
    @endif
</x-app-layout>
