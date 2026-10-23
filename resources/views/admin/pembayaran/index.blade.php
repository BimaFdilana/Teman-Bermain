<x-admin-layout>
    <div class="admin-topbar">
        <div>
            <h1 class="kidza-h2">🧾 Orderan / Pesanan Produk</h1>
            <p style="color:#4b5563">Setujui atau tolak bukti pembayaran pengguna.</p>
        </div>
    </div>

    @if($counts['verifikasi'] > 0)
        <div class="kidza-card p-4 mb-5" style="background:#FFF9EC;border-color:#F3A712">
            <p class="font-bold">🔔 {{ $counts['verifikasi'] }} order menunggu verifikasi!</p>
        </div>
    @endif

    <div class="flex gap-2 mb-5 flex-wrap">
        @foreach(['verifikasi' => 'Menunggu Verifikasi', 'pembayaran' => 'Menunggu Bayar', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'kadaluarsa' => 'Kadaluarsa', 'semua' => 'Semua'] as $key => $label)
            <a href="{{ route('admin.pembayaran.index', ['status' => $key]) }}"
               class="kidza-btn {{ $status === $key ? 'kidza-btn-yellow' : 'kidza-btn-white' }}" style="padding:.5rem 1.1rem;font-size:.9rem">{{ $label }}</a>
        @endforeach
    </div>

    <div class="kidza-card tabel-scroll">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f3f4f6;text-align:left">
                    <th class="p-3">Kode Order</th>
                    <th class="p-3">Pengguna</th>
                    <th class="p-3">Produk</th>
                    <th class="p-3">Total</th>
                    <th class="p-3">Waktu</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-t">
                        <td class="p-3 font-bold">{{ $order->kode_order }}<br><span class="text-xs font-normal" style="color:#6b7280">{{ $order->labelMetode() }}</span></td>
                        <td class="p-3">{{ $order->namaPembeli() }}<br><span class="text-xs" style="color:#6b7280">{{ $order->emailPembeli() }}</span></td>
                        <td class="p-3">{{ $order->items->count() > 1 ? $order->items->count().' produk' : ($order->items->first()?->product->judul ?? $order->product?->judul ?? '-') }}</td>
                        <td class="p-3 font-bold">{{ $order->totalRupiah() }}</td>
                        <td class="p-3 text-xs" style="color:#6b7280">{{ $order->created_at->format('d M H:i') }}</td>
                        <td class="p-3"><a href="{{ route('admin.pembayaran.show', $order) }}" class="kidza-btn kidza-btn-blue" style="padding:.4rem 1rem;font-size:.85rem">Periksa</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center" style="color:#6b7280">Tidak ada order pada status ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</x-admin-layout>
