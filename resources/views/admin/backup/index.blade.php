<x-admin-layout>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-1">
        <h1 class="kidza-h2">💾 Backup</h1>
        <form method="POST" action="{{ route('admin.backup.store') }}" onsubmit="return tanyaForm(this, 'Buat backup database + file sekarang?')">
            @csrf
            <button class="kidza-btn kidza-btn-green">⬇️ Backup Sekarang</button>
        </form>
    </div>
    <p class="text-sm mb-5" style="color:#6b7280">Backup otomatis berjalan tiap hari jam 02:00 (database + file). Tersimpan 7 terbaru per jenis.</p>

    <div class="kidza-card p-5 tabel-scroll">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left" style="border-bottom:2px solid #1a1a1a">
                    <th class="py-2 pr-3">Nama file</th>
                    <th class="py-2 pr-3">Jenis</th>
                    <th class="py-2 pr-3">Ukuran</th>
                    <th class="py-2 pr-3">Waktu</th>
                    <th class="py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $f)
                    <tr style="border-bottom:1px dashed #e5e5e5">
                        <td class="py-2 pr-3 font-mono text-xs">{{ $f['nama'] }}</td>
                        <td class="py-2 pr-3">{{ $f['jenis'] }}</td>
                        <td class="py-2 pr-3">{{ number_format($f['ukuran'] / 1048576, 1, ',', '.') }} MB</td>
                        <td class="py-2 pr-3">{{ \Carbon\Carbon::createFromTimestamp($f['waktu'])->format('d M Y H:i') }}</td>
                        <td class="py-2">
                            <a href="{{ route('admin.backup.download', $f['nama']) }}" class="kidza-btn" style="background:#fff;padding:.3rem .8rem;font-size:.8rem">⬇️ Unduh</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center" style="color:#6b7280">Belum ada backup. Klik "Backup Sekarang".</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
