@props(['status'])
@php
$warna = [
    'pembayaran' => '#f39c12', 'verifikasi' => '#3498db', 'ditolak' => '#e74c3c',
    'disetujui' => '#27ae60', 'kadaluarsa' => '#95a5a6',
    'tersedia' => '#27ae60', 'draft' => '#f39c12', 'arsip' => '#95a5a6',
    'aktif' => '#27ae60', 'terpakai' => '#95a5a6',
][$status] ?? '#7f8c8d';
$label = [
    'pembayaran' => 'Menunggu Bayar', 'verifikasi' => 'Verifikasi',
    'ditolak' => 'Ditolak', 'disetujui' => 'Disetujui', 'kadaluarsa' => 'Kadaluarsa',
    'tersedia' => 'Tersedia', 'draft' => 'Draft', 'arsip' => 'Arsip',
    'aktif' => 'Aktif', 'terpakai' => 'Terpakai',
][$status] ?? ucfirst($status);
@endphp
<span class="inline-block text-xs font-bold px-3 py-1 rounded-full text-white" style="background:{{ $warna }}">{{ $label }}</span>
