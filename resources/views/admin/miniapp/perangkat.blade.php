<x-admin-layout>
    <a href="{{ route('admin.miniapp.index') }}" class="text-sm font-bold underline">← Kembali ke MiniApps</a>
    <h1 class="kidza-h2 mt-2 mb-1">📲 Perangkat Terpasang</h1>
    <p class="text-sm mb-5" style="color:#6b7280">"{{ $product->judul }}" — cabut token bila perangkat hilang atau akses perlu dihentikan.</p>

    <div class="kidza-card p-5 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left" style="border-bottom:2px solid #1a1a1a">
                    <th class="py-2 pr-3">Pengguna</th>
                    <th class="py-2 pr-3">Dibuat</th>
                    <th class="py-2 pr-3">Terakhir dipakai</th>
                    <th class="py-2 pr-3">Status</th>
                    <th class="py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tokens as $t)
                    <tr style="border-bottom:1px dashed #e5e5e5">
                        <td class="py-2 pr-3 font-bold">{{ $t->user?->name ?? '—' }}<br><span class="text-xs font-normal" style="color:#6b7280">{{ $t->user?->email }}</span></td>
                        <td class="py-2 pr-3">{{ $t->created_at->format('d M Y H:i') }}</td>
                        <td class="py-2 pr-3">{{ $t->dipakai_terakhir_at?->format('d M Y H:i') ?? '—' }}</td>
                        <td class="py-2 pr-3">{!! $t->dicabut ? '🔌 Dicabut' : '✅ Aktif' !!}</td>
                        <td class="py-2">
                            @if(! $t->dicabut)
                                <form method="POST" action="{{ route('admin.miniapp.token.cabut', $t) }}" onsubmit="return confirm('Cabut akses perangkat ini? Aplikasi terpasang tidak bisa dibuka lagi.')">
                                    @csrf
                                    <button class="kidza-btn" style="background:#fdecea;padding:.3rem .8rem;font-size:.8rem">🔌 Cabut</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center" style="color:#6b7280">Belum ada perangkat terpasang.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $tokens->links() }}</div>
    </div>
</x-admin-layout>
