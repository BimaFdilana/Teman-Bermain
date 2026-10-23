<x-admin-layout>
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold" style="font-family:'Quicksand'">🎮 MiniApps</h1>
    <a href="{{ route('admin.miniapp.create') }}" class="kidza-btn kidza-btn-green">+ Tambah MiniApp</a>
</div>

@if(session('sukses'))
    <div class="kidza-card p-3 mb-4" style="background:#e8f8f0"><p class="text-sm font-bold" style="color:#167941">{{ session('sukses') }}</p></div>
@endif

@if($miniapps->isEmpty())
    <x-empty-state pesan="Belum ada MiniApp. Tambahkan yang pertama!" />
@else
    <x-data-table>
        <x-slot:head>
            <th class="text-left px-4 py-2">Nama</th>
            <th class="text-left px-4 py-2">Nama PWA</th>
            <th class="text-left px-4 py-2">Versi</th>
            <th class="text-left px-4 py-2">Status</th>
            <th class="text-left px-4 py-2">Aksi</th>
        </x-slot:head>
        @foreach($miniapps as $p)
            <tr class="border-t">
                <td class="px-4 py-2 font-bold">{{ $p->judul }}</td>
                <td class="px-4 py-2">{{ $p->miniapp?->pwa_name ?? '—' }}</td>
                <td class="px-4 py-2">{{ $p->miniapp?->versi ?? '—' }}</td>
                <td class="px-4 py-2"><x-badge-status :status="$p->status" /></td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <a href="{{ route('admin.miniapp.preview', $p) }}" class="kidza-btn" style="background:#fff" title="Preview">👁️</a>
                    <a href="{{ route('miniapp.install', $p) }}" target="_blank" rel="noopener" class="kidza-btn kidza-btn-yellow" title="Uji install — langsung ke HTML game seperti customer">🚀</a>
                    <a href="{{ route('admin.miniapp.edit', $p) }}" class="kidza-btn" style="background:#fff" title="Edit">✏️</a>
                    <form method="POST" action="{{ route('admin.miniapp.destroy', $p) }}" class="inline" onsubmit="return tanyaForm(this, 'Hapus MiniApp ini?')">
                        @csrf @method('DELETE')
                        <button class="kidza-btn" style="background:#fdecea" title="Hapus">🗑️</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </x-data-table>
    <div class="mt-4">{{ $miniapps->links() }}</div>
@endif
</x-admin-layout>
