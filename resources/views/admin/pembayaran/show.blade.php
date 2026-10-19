<x-admin-layout>
    <a href="{{ route('admin.pembayaran.index') }}" class="kidza-link text-sm mb-4 inline-block">← Kembali ke daftar</a>

    <h1 class="kidza-h2 mb-6">Order {{ $order->kode_order }}</h1>

    <div class="grid md:grid-cols-2 gap-6 items-start">
        <div class="kidza-card p-6">
            <h2 class="font-bold text-lg mb-4" style="font-family:'Quicksand'">Bukti Pembayaran</h2>
            @if($order->bukti_path)
                <a href="{{ asset('storage/' . $order->bukti_path) }}" target="_blank">
                    <img src="{{ asset('storage/' . $order->bukti_path) }}" alt="Bukti pembayaran" class="rounded-2xl w-full border-2 border-gray-200">
                </a>
                <p class="text-xs mt-2 text-center" style="color:#6b7280">Klik untuk perbesar</p>
            @else
                <p style="color:#6b7280">Belum ada bukti diupload.</p>
            @endif
        </div>

        <div>
            <div class="kidza-card p-6 mb-5">
                <h2 class="font-bold text-lg mb-3" style="font-family:'Quicksand'">Detail</h2>
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt style="color:#6b7280">Pembeli</dt><dd class="font-bold">{{ $order->namaPembeli() }}</dd></div>
                    <div class="flex justify-between"><dt style="color:#6b7280">Email</dt><dd class="font-bold">{{ $order->emailPembeli() ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt style="color:#6b7280">WhatsApp</dt><dd class="font-bold">{{ $order->waPembeli() ?? '-' }}</dd></div>
                    <div class="flex justify-between"><dt style="color:#6b7280">Metode</dt><dd class="font-bold">{{ $order->labelMetode() }}</dd></div>
                    <div class="flex justify-between"><dt style="color:#6b7280">Total</dt><dd class="font-bold" style="color:#167941">{{ $order->totalRupiah() }}</dd></div>
                    @if($order->discountCode)
                        <div class="flex justify-between"><dt style="color:#6b7280">Diskon</dt><dd class="font-bold">{{ $order->discountCode->kode }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt style="color:#6b7280">Status</dt><dd><span class="kidza-badge" style="background:{{ $order->warnaStatus() }}">{{ $order->labelStatus() }}</span></dd></div>
                    <div class="flex justify-between"><dt style="color:#6b7280">Dibuat</dt><dd>{{ $order->created_at->format('d M Y, H:i') }}</dd></div>
                </dl>
                <h3 class="font-bold mt-4 mb-2">Item ({{ $order->items->count() ?: 1 }})</h3>
                <div class="text-sm space-y-1">
                    @forelse($order->items as $item)
                        <div class="flex justify-between py-1" style="border-bottom:1px dashed #e5e5e5">
                            <span>{{ $item->product->judul }}</span>
                            <strong>Rp {{ number_format($item->harga_saat_beli, 0, ',', '.') }}</strong>
                        </div>
                    @empty
                        <div class="flex justify-between py-1">
                            <span>{{ $order->product->judul ?? '-' }}</span>
                            <strong>{{ $order->totalRupiah() }}</strong>
                        </div>
                    @endforelse
                </div>
            </div>

            @if($order->status === 'verifikasi')
                <div class="kidza-card p-6 mb-5">
                    <form method="POST" action="{{ route('admin.pembayaran.approve', $order) }}" class="mb-4">
                        @csrf
                        <button type="submit" class="kidza-btn kidza-btn-green w-full text-lg"
                                onclick="return tanyaForm(this.form, 'Setujui order ini dan buat kode redeem?', '✅')">✓ Setujui & Buat Kode Redeem</button>
                    </form>
                    <form method="POST" action="{{ route('admin.pembayaran.reject', $order) }}">
                        @csrf
                        <label class="text-sm font-bold block mb-1">Alasan penolakan</label>
                        <textarea name="alasan_tolak" rows="2" required placeholder="mis. Bukti tidak jelas / nominal kurang"
                                  class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 mb-3"></textarea>
                        <button type="submit" class="kidza-btn w-full" style="background:#e74c3c;color:#fff"
                                onclick="return tanyaForm(this.form, 'Tolak order ini?', '✕')">✕ Tolak</button>
                    </form>
                </div>
            @endif

            @if($order->status === 'disetujui' && $order->redeemCodes->isNotEmpty())
                <div class="kidza-card p-6" style="background:#e8f6ee">
                    <h2 class="font-bold text-lg mb-2" style="font-family:'Quicksand'">Kode Redeem ({{ $order->redeemCodes->count() }})</h2>
                    @foreach($order->redeemCodes as $rc)
                        <div class="flex justify-between items-center py-1" style="border-bottom:1px dashed #b9d4c0">
                            <span class="text-sm">{{ $rc->product->judul }}</span>
                            <span class="font-bold" style="font-family:'Quicksand';letter-spacing:.08em">{{ $rc->kode }}</span>
                        </div>
                    @endforeach
                    @if($waLink)
                        <a href="{{ $waLink }}" target="_blank" class="kidza-btn kidza-btn-green w-full text-lg mt-4">📤 Kirim Produk via WA</a>
                        <p class="text-xs mt-2 text-center" style="color:#6b7280">Membuka WhatsApp dengan pesan lengkap — tinggal tap kirim.</p>
                    @else
                        <p class="text-sm mt-3" style="color:#6b7280">Nomor WhatsApp pembeli belum diisi.</p>
                    @endif
                </div>
            @endif

            @if($order->status === 'ditolak' && $order->alasan_tolak)
                <div class="kidza-card p-6" style="background:#fdecea">
                    <p class="text-sm mb-3"><strong>Alasan ditolak:</strong> {{ $order->alasan_tolak }}</p>
                    @if($waGagalLink)
                        <a href="{{ $waGagalLink }}" target="_blank" class="kidza-btn w-full" style="background:#e74c3c;color:#fff">✉️ Kirim Info Gagal via WA</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
