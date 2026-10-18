<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Laporan Penjualan</title>
<style>
body { font-family: sans-serif; font-size: 12px; color: #222; }
h1 { font-size: 20px; margin-bottom: 4px; }
p.meta { color: #666; margin-top: 0; }
table { width: 100%; border-collapse: collapse; margin-top: 16px; }
th, td { border: 1px solid #ccc; padding: 6px 8px; }
th { background: #167941; color: #fff; }
td.num { text-align: right; }
</style></head>
<body>
<h1>Laporan Penjualan — Rumah Cahaya-bks</h1>
<p class="meta">Per {{ ucfirst($tipe) }} · dicetak {{ date('d M Y H:i') }}</p>
<table>
    <thead><tr>
        <th>Periode</th><th>Pendapatan (Rp)</th><th>Target (Rp)</th><th>Selisih (Rp)</th><th>Order</th>
    </tr></thead>
    <tbody>
    @foreach($periodes as $p)
        <tr>
            <td>{{ $p['label'] }}</td>
            <td class="num">{{ number_format($p['pendapatan'], 0, ',', '.') }}</td>
            <td class="num">{{ $p['target'] ? number_format($p['target'], 0, ',', '.') : '—' }}</td>
            <td class="num">{{ $p['target'] ? number_format($p['pendapatan'] - $p['target'], 0, ',', '.') : '—' }}</td>
            <td class="num">{{ $p['jumlah_order'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
