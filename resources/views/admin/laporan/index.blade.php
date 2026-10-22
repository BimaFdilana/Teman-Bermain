<x-admin-layout>
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h1 class="text-2xl font-bold" style="font-family:'Quicksand'">📈 Laporan</h1>
    <div class="flex flex-wrap gap-2">
        @foreach(['minggu' => 'Minggu', 'bulan' => 'Bulan', 'tahun' => 'Tahun'] as $key => $label)
            <a href="{{ route('admin.laporan.index', ['tipe' => $key]) }}"
               class="kidza-btn {{ $tipe === $key ? 'kidza-btn-green' : '' }}"
               style="{{ $tipe === $key ? '' : 'background:#fff' }}">{{ $label }}</a>
        @endforeach
        <a href="{{ route('admin.laporan.excel', ['tipe' => $tipe]) }}" class="kidza-btn kidza-btn-blue">⬇ Excel</a>
        <a href="{{ route('admin.laporan.pdf', ['tipe' => $tipe]) }}" class="kidza-btn kidza-btn-yellow">⬇ PDF</a>
    </div>
</div>

@if(session('sukses'))
    <div class="kidza-card p-3 mb-4" style="background:#e8f8f0"><p class="text-sm font-bold" style="color:#167941">{{ session('sukses') }}</p></div>
@endif

{{-- Ringkasan periode berjalan --}}
@php $p0 = $periodes[0]; @endphp
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card judul="Pendapatan {{ $p0['label'] }}" :nilai="'Rp '.number_format($p0['pendapatan'], 0, ',', '.')" :sub="$p0['jumlah_order'].' order disetujui'" warna="#167941" />
    <x-stat-card judul="Target" :nilai="$p0['target'] ? 'Rp '.number_format($p0['target'], 0, ',', '.') : 'Belum diisi'" warna="#0077B6" />
    <x-stat-card judul="Capaian" :nilai="$p0['target'] ? round($p0['pendapatan'] / max($p0['target'], 1) * 100).'%' : '—'" warna="#F3A712" />
</div>

<x-data-table>
    <x-slot:head>
        <th class="text-left px-4 py-2">Periode</th>
        <th class="text-right px-4 py-2">Pendapatan</th>
        <th class="text-right px-4 py-2">Target</th>
        <th class="text-right px-4 py-2">Selisih</th>
        <th class="text-right px-4 py-2">Order</th>
        <th class="text-left px-4 py-2">Aksi</th>
    </x-slot:head>
    @foreach($periodes as $p)
        @php $selisih = $p['pendapatan'] - ($p['target'] ?? 0); @endphp
        <tr class="border-t">
            <td class="px-4 py-2 font-bold">{{ $p['label'] }}</td>
            <td class="px-4 py-2 text-right">Rp {{ number_format($p['pendapatan'], 0, ',', '.') }}</td>
            <td class="px-4 py-2 text-right">{{ $p['target'] ? 'Rp '.number_format($p['target'], 0, ',', '.') : '—' }}</td>
            <td class="px-4 py-2 text-right font-bold" style="color:{{ $p['target'] ? ($selisih >= 0 ? '#167941' : '#e74c3c') : '#6b7280' }}">
                {{ $p['target'] ? ($selisih >= 0 ? '+' : '').'Rp '.number_format($selisih, 0, ',', '.') : '—' }}
            </td>
            <td class="px-4 py-2 text-right">{{ $p['jumlah_order'] }}</td>
            <td class="px-4 py-2">
                <button class="kidza-btn" style="background:#fff" onclick="document.getElementById('target-{{ $loop->index }}').style.display='flex'">🎯 Target</button>
            </td>
        </tr>
    @endforeach
</x-data-table>

{{-- Modal target per periode --}}
@foreach($periodes as $p)
<x-modal id="target-{{ $loop->index }}" judul="🎯 Target: {{ $p['label'] }}">
    <form method="POST" action="{{ route('admin.laporan.target') }}">
        @csrf
        <input type="hidden" name="tipe" value="{{ $tipe }}">
        <input type="hidden" name="periode" value="{{ $p['key'] }}">
        <x-form-input label="Target Pendapatan (Rp)" name="target" type="number" :value="$p['target']" required attrs="min=0" />
        <button type="submit" class="kidza-btn kidza-btn-green">💾 Simpan Target</button>
    </form>
</x-modal>
@endforeach
</x-admin-layout>
