<x-admin-layout>
<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-bold" style="font-family:'Quicksand'">📚 Ebook</h1>
    <a href="{{ route('admin.ebook.create') }}" class="kidza-btn kidza-btn-green">+ Tambah Ebook</a>
</div>

@if(session('sukses'))
    <div class="kidza-card p-3 mb-4" style="background:#e8f8f0"><p class="text-sm font-bold" style="color:#167941">{{ session('sukses') }}</p></div>
@endif

@if($ebooks->isEmpty())
    <x-empty-state pesan="Belum ada Ebook. Tambahkan yang pertama!" />
@else
    <x-data-table>
        <x-slot:head>
            <th class="text-left px-4 py-2">Judul</th>
            <th class="text-left px-4 py-2">PDF</th>
            <th class="text-left px-4 py-2">Status</th>
            <th class="text-left px-4 py-2">Aksi</th>
        </x-slot:head>
        @foreach($ebooks as $p)
            <tr class="border-t">
                <td class="px-4 py-2 font-bold">{{ $p->judul }}</td>
                <td class="px-4 py-2">{{ $p->ebook?->pdf_path ? '✅' : '❌' }}</td>
                <td class="px-4 py-2"><x-badge-status :status="$p->status" /></td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <a href="{{ route('admin.ebook.edit', $p) }}" class="kidza-btn" style="background:#fff">✏️</a>
                    <form method="POST" action="{{ route('admin.ebook.destroy', $p) }}" class="inline" onsubmit="return tanyaForm(this, 'Hapus Ebook ini?')">
                        @csrf @method('DELETE')
                        <button class="kidza-btn" style="background:#fdecea">🗑️</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </x-data-table>
    <div class="mt-4">{{ $ebooks->links() }}</div>
@endif
</x-admin-layout>
