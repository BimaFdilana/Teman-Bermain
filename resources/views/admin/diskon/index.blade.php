<x-admin-layout>
    <div class="admin-topbar">
        <div>
            <h1 class="kidza-h2">Kode Diskon</h1>
            <p style="color:#4b5563">Kelola kode promo untuk pengguna.</p>
        </div>
        <a href="{{ route('admin.diskon.create') }}" class="kidza-btn kidza-btn-green">+ Buat Kode</a>
    </div>

    <div class="kidza-card tabel-scroll">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f3f4f6;text-align:left">
                    <th class="p-3">Kode</th>
                    <th class="p-3">Diskon</th>
                    <th class="p-3">Kuota</th>
                    <th class="p-3">Periode</th>
                    <th class="p-3">Status</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($diskons as $d)
                    <tr class="border-t">
                        <td class="p-3 font-bold" style="font-family:'Quicksand'">{{ $d->kode }}</td>
                        <td class="p-3">{{ $d->label() }}</td>
                        <td class="p-3">{{ $d->terpakai }}{{ $d->kuota ? '/' . $d->kuota : ' (∞)' }}</td>
                        <td class="p-3 text-xs" style="color:#6b7280">
                            {{ $d->mulai?->format('d M Y') ?? '—' }} s/d {{ $d->berakhir?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="p-3">
                            <span class="kidza-badge" style="background:{{ $d->berlaku() ? '#167941' : '#6b7280' }}">
                                {{ $d->berlaku() ? 'Berlaku' : 'Tidak berlaku' }}
                            </span>
                        </td>
                        <td class="p-3 whitespace-nowrap">
                            <a href="{{ route('admin.diskon.edit', $d) }}" class="kidza-link text-sm mr-3">Edit</a>
                            <form method="POST" action="{{ route('admin.diskon.destroy', $d) }}" class="inline"
                                  onsubmit="return tanyaForm(this, 'Hapus kode {{ $d->kode }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm font-bold" style="color:#e74c3c">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center" style="color:#6b7280">Belum ada kode diskon.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $diskons->links() }}</div>
</x-admin-layout>
