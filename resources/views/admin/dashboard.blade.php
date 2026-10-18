<x-admin-layout>
@section('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endsection

<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-bold" style="font-family:'Quicksand'">📊 Dashboard</h1>
    <div class="flex gap-2">
        @foreach(['minggu' => 'Minggu', 'bulan' => 'Bulan', 'tahun' => 'Tahun'] as $key => $label)
            <a href="{{ route('admin.dashboard', ['periode' => $key]) }}"
               class="kidza-btn {{ $filter === $key ? 'kidza-btn-green' : '' }}"
               style="{{ $filter === $key ? '' : 'background:#fff' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

{{-- Stat cards (PRD §6.3) --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card judul="Jumlah Produk" :nilai="$stats['produk_tersedia']" :sub="'Tersedia · Draft: '.$stats['produk_draft']" warna="#167941" />
    <x-stat-card judul="Total Penjualan" :nilai="$stats['produk_terjual'].' terjual'" :sub="'Pendapatan: Rp '.number_format($stats['pendapatan'], 0, ',', '.')" warna="#0077B6" />
    <x-stat-card judul="Jumlah Pengguna" :nilai="$stats['pengguna']" :sub="'Menunggu verifikasi: '.$stats['menunggu_verifikasi'].' order'" warna="#F3A712" />
</div>

{{-- Insight otomatis --}}
<div class="kidza-card p-5 mb-6" style="background:#FFF9EC">
    <h2 class="font-bold mb-2" style="font-family:'Quicksand'">💡 Keterangan Otomatis</h2>
    <ul class="list-disc list-inside text-sm space-y-1">
        @foreach($aktivitas['insights'] as $insight)
            <li>{{ $insight }}</li>
        @endforeach
    </ul>
</div>

{{-- Chart aktivitas --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="kidza-card p-5">
        <h2 class="font-bold mb-3" style="font-family:'Quicksand'">Login per Hari</h2>
        <canvas id="chartHari" height="220"></canvas>
    </div>
    <div class="kidza-card p-5">
        <h2 class="font-bold mb-3" style="font-family:'Quicksand'">Login per Jam</h2>
        <canvas id="chartJam" height="220"></canvas>
    </div>
</div>

{{-- Log login terbaru --}}
<div class="kidza-card p-5">
    <h2 class="font-bold mb-3" style="font-family:'Quicksand'">🕒 5 Login Terakhir</h2>
    @if($logTerbaru->isEmpty())
        <x-empty-state pesan="Belum ada log login." />
    @else
        <x-data-table>
            <x-slot:head>
                <th class="text-left px-4 py-2">Nama</th>
                <th class="text-left px-4 py-2">Jam Login</th>
            </x-slot:head>
            @foreach($logTerbaru as $log)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $log->user?->name ?? '—' }}</td>
                    <td class="px-4 py-2">{{ $log->login_at->format('d M Y H:i') }}</td>
                </tr>
            @endforeach
        </x-data-table>
    @endif
</div>

<script>
const hari = @json($aktivitas['label_hari']);
const perHari = @json(array_values($aktivitas['per_hari']));
const perJam = @json($aktivitas['per_jam']);

new Chart(document.getElementById('chartHari'), {
    type: 'bar',
    data: { labels: hari, datasets: [{ data: perHari, backgroundColor: '#167941', borderRadius: 8 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});

new Chart(document.getElementById('chartJam'), {
    type: 'bar',
    data: {
        labels: Array.from({length: 24}, (_, i) => String(i).padStart(2, '0')),
        datasets: [{ data: perJam, backgroundColor: '#0077B6', borderRadius: 4 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
</x-admin-layout>
