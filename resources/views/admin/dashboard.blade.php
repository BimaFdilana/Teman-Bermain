<x-admin-layout>
@section('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endsection

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
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

{{-- Chart aktivitas (satu chart, tab Hari / Jam) --}}
<div class="kidza-card p-5 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
        <h2 class="font-bold" style="font-family:'Quicksand'">📈 Aktivitas Login Pengguna</h2>
        <div class="flex gap-2">
            <button type="button" id="tab-hari" class="kidza-btn kidza-btn-green" onclick="gantiTab('hari')">Per Hari</button>
            <button type="button" id="tab-jam" class="kidza-btn" style="background:#fff" onclick="gantiTab('jam')">Per Jam</button>
        </div>
    </div>
    <div style="position:relative;height:160px"><canvas id="chartAktivitas"></canvas></div>
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
const dataHari = {
    labels: @json($aktivitas['label_hari']),
    data: @json(array_values($aktivitas['per_hari'])),
    warna: '#167941'
};
const dataJam = {
    labels: Array.from({length: 24}, (_, i) => String(i).padStart(2, '0')),
    data: @json($aktivitas['per_jam']),
    warna: '#0077B6'
};

const chart = new Chart(document.getElementById('chartAktivitas'), {
    type: 'bar',
    data: { labels: dataHari.labels, datasets: [{ data: dataHari.data, backgroundColor: dataHari.warna, borderRadius: 8 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});

function gantiTab(tab) {
    const aktif = tab === 'hari' ? dataHari : dataJam;
    chart.data.labels = aktif.labels;
    chart.data.datasets[0].data = aktif.data;
    chart.data.datasets[0].backgroundColor = aktif.warna;
    chart.update();

    const btnHari = document.getElementById('tab-hari');
    const btnJam = document.getElementById('tab-jam');
    btnHari.className = 'kidza-btn' + (tab === 'hari' ? ' kidza-btn-green' : '');
    btnJam.className = 'kidza-btn' + (tab === 'jam' ? ' kidza-btn-green' : '');
    if (tab === 'hari') { btnHari.style.background = ''; btnJam.style.background = '#fff'; }
    else { btnJam.style.background = ''; btnHari.style.background = '#fff'; }
}
</script>
</x-admin-layout>
