<x-landing-layout>
@section('title', 'Pembayaran ' . $order->kode_order . ' — Rumah Cahaya-bks')

<section class="kidza-section" style="background:#F9F9F6">
    <div class="kidza-wrap" style="max-width:44rem">
        <div class="text-center mb-8">
            <span class="kidza-kicker">Langkah 2 dari 4</span>
            <h1 class="kidza-h2 mb-2">Selesaikan Pembayaran</h1>
            <p style="color:#4b5563">Order <strong>{{ $order->kode_order }}</strong> · {{ $order->product->judul }}</p>
        </div>

        @if($order->status === 'pembayaran')
            <div class="kidza-card p-6 md:p-8 mb-6 text-center">
                <h2 class="font-bold text-xl mb-4" style="font-family:'Quicksand'">Scan QRIS untuk membayar</h2>
                @if($qrisImage)
                    <img src="{{ asset('storage/' . $qrisImage) }}" alt="QR QRIS" class="mx-auto mb-4 rounded-2xl border-2 border-gray-200" style="max-width:280px">
                @else
                    <div class="mx-auto mb-4 rounded-2xl flex items-center justify-center" style="max-width:280px;height:280px;background:#f3f4f6">
                        <p class="px-6" style="color:#6b7280">QR QRIS belum diatur admin.</p>
                    </div>
                @endif
                <p class="font-bold text-3xl mb-1" style="font-family:'Quicksand';color:#167941">{{ $order->totalRupiah() }}</p>
                <p class="text-sm mb-4" style="color:#6b7280">Nominal harus <strong>tepat</strong> agar verifikasi lancar.</p>
                <div class="kidza-card p-4 text-left text-sm" style="background:#FFF9EC">
                    <p class="mb-1">⏰ Batas upload bukti: <strong>{{ $order->batas_bayar->format('d M Y, H:i') }} WIB</strong></p>
                    <p>👩‍💼 Verifikasi admin maks. <strong>1×24 jam</strong> (jam kerja 08.00–20.00 WIB).</p>
                </div>
            </div>

            <div class="kidza-card p-6 md:p-8">
                <h2 class="font-bold text-xl mb-4" style="font-family:'Quicksand'">Upload Bukti Pembayaran</h2>
                <form method="POST" action="{{ route('checkout.bukti', $order->kode_order) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="bukti" accept=".jpg,.jpeg,.png" required
                           class="w-full border-2 border-dashed border-gray-300 rounded-2xl px-4 py-6 mb-2 text-center">
                    <p class="text-xs mb-4" style="color:#6b7280">JPG/PNG, maksimal 2 MB.</p>
                    @error('bukti')<p class="text-sm font-bold mb-3" style="color:#e74c3c">{{ $message }}</p>@enderror
                    <button type="submit" class="kidza-btn kidza-btn-green w-full text-lg">Kirim Bukti</button>
                </form>
            </div>
        @elseif($order->status === 'verifikasi')
            <div class="kidza-card p-8 text-center">
                <div class="text-5xl mb-4">🔍</div>
                <h2 class="font-bold text-xl mb-2" style="font-family:'Quicksand'">Menunggu verifikasi admin</h2>
                <p style="color:#4b5563">Bukti pembayaranmu sudah kami terima. Admin akan verifikasi maks. 1×24 jam (jam kerja 08.00–20.00 WIB).</p>
                <a href="{{ route('checkout.riwayat') }}" class="kidza-btn kidza-btn-white mt-6">Lihat Pesanan Saya</a>
            </div>
        @elseif($order->status === 'disetujui')
            <div class="kidza-card p-8 text-center" style="background:#e8f6ee">
                <div class="text-5xl mb-4">🎉</div>
                <h2 class="font-bold text-xl mb-2" style="font-family:'Quicksand'">Pembayaran disetujui!</h2>
                <p style="color:#4b5563">Kode redeem dikirim ke WhatsApp kamu. Masukkan di Home untuk membuka produkmu.</p>
                <a href="{{ route('home') }}" class="kidza-btn kidza-btn-green mt-6">Ke Home & Redeem</a>
            </div>
        @elseif($order->status === 'ditolak')
            <div class="kidza-card p-8">
                <div class="text-5xl mb-4 text-center">😔</div>
                <h2 class="font-bold text-xl mb-2 text-center" style="font-family:'Quicksand'">Bukti ditolak admin</h2>
                <p class="text-center mb-2" style="color:#4b5563">Alasan: <strong>{{ $order->alasan_tolak }}</strong></p>
                @if($order->bolehUploadBukti())
                    <p class="text-center text-sm mb-4" style="color:#4b5563">Kamu bisa upload ulang 1x.</p>
                    <form method="POST" action="{{ route('checkout.bukti', $order->kode_order) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="bukti" accept=".jpg,.jpeg,.png" required
                               class="w-full border-2 border-dashed border-gray-300 rounded-2xl px-4 py-6 mb-2">
                        @error('bukti')<p class="text-sm font-bold mb-3" style="color:#e74c3c">{{ $message }}</p>@enderror
                        <button type="submit" class="kidza-btn kidza-btn-yellow w-full text-lg">Upload Ulang Bukti</button>
                    </form>
                @else
                    <p class="text-center text-sm" style="color:#4b5563">Kesempatan upload ulang sudah habis. Hubungi admin via WhatsApp untuk bantuan.</p>
                @endif
            </div>
        @else
            <div class="kidza-card p-8 text-center">
                <div class="text-5xl mb-4">⌛</div>
                <h2 class="font-bold text-xl mb-2" style="font-family:'Quicksand'">Order kadaluarsa</h2>
                <p style="color:#4b5563">Batas waktu pembayaran 1×24 jam telah lewat. Silakan buat order baru.</p>
                <a href="{{ route('produk.show', $order->product->slug) }}" class="kidza-btn kidza-btn-green mt-6">Order Lagi</a>
            </div>
        @endif
    </div>
</section>
</x-landing-layout>
