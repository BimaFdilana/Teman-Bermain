<x-admin-layout>
    <h1 class="kidza-h2 mb-1">📝 Aktivitas Admin</h1>
    <p class="text-sm mb-5" style="color:#6b7280">Jejak aksi penting: verifikasi order, ubah harga, publish/arsip produk.</p>

    <div class="kidza-card p-5 tabel-scroll">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left" style="border-bottom:2px solid #1a1a1a">
                    <th class="py-2 pr-3">Waktu</th>
                    <th class="py-2 pr-3">Admin</th>
                    <th class="py-2 pr-3">Aksi</th>
                    <th class="py-2">Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr style="border-bottom:1px dashed #e5e5e5">
                        <td class="py-2 pr-3 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td class="py-2 pr-3">{{ $log->admin?->name ?? '—' }}</td>
                        <td class="py-2 pr-3 font-bold">{{ \App\Models\ActivityLog::label($log->aksi) }}</td>
                        <td class="py-2">{{ $log->detail }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center" style="color:#6b7280">Belum ada aktivitas tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</x-admin-layout>
