<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Invoice {{ $order->kode_order }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; color: #1a1a1a; font-size: 11pt; line-height: 1.5; }
    .header { border-bottom: 3px solid #14532D; padding-bottom: 12px; margin-bottom: 16px; }
    .header h1 { color: #14532D; font-size: 22pt; margin: 0; }
    .header p { margin: 2px 0; color: #555; font-size: 10pt; }
    .title { font-size: 18pt; color: #14532D; margin: 0 0 4px 0; }
    .meta td { padding: 2px 8px 2px 0; vertical-align: top; }
    .meta .label { color: #555; width: 140px; }
    table.items { width: 100%; border-collapse: collapse; margin: 16px 0; }
    table.items th { background: #14532D; color: #fff; text-align: left; padding: 8px; font-size: 10pt; }
    table.items td { border-bottom: 1px solid #ddd; padding: 8px; font-size: 10pt; }
    .totals { width: 60%; margin-left: 40%; }
    .totals td { padding: 4px 8px; font-size: 10pt; }
    .totals .grand { font-size: 13pt; font-weight: bold; color: #14532D; border-top: 2px solid #14532D; }
    .notes { background: #FFF9EC; border: 1px solid #F3A712; border-radius: 6px; padding: 10px 14px; margin-top: 18px; font-size: 10pt; }
    .notes ol { margin: 6px 0 0 18px; padding: 0; }
    .footer { margin-top: 24px; font-size: 9pt; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 8px; }
</style>
</head>
<body>

<div class="header">
    <h1>Rumah Cahaya-bks</h1>
    <p>Ebook interaktif & MiniApp game edukasi untuk anak PAUD dan SD Indonesia</p>
</div>

<p class="title">INVOICE</p>
<table class="meta">
    <tr><td class="label">Nomor Invoice</td><td><strong>{{ $order->kode_order }}</strong></td></tr>
    <tr><td class="label">Tanggal pesanan</td><td>{{ $order->created_at->format('d M Y, H:i') }} WIB</td></tr>
    <tr><td class="label">Metode bayar</td><td>{{ $order->labelMetode() }}</td></tr>
    <tr><td class="label">Status</td><td>{{ $order->labelStatus() }}</td></tr>
</table>

<table class="meta" style="margin-top:10px">
    <tr><td class="label">Nama</td><td>{{ $order->namaPembeli() }}</td></tr>
    <tr><td class="label">Email</td><td>{{ $order->emailPembeli() ?? '-' }}</td></tr>
    <tr><td class="label">WhatsApp</td><td>{{ $order->waPembeli() ?? '-' }}</td></tr>
</table>

<table class="items">
    <thead>
        <tr><th style="width:5%">No</th><th>Produk</th><th style="width:25%;text-align:right">Harga</th></tr>
    </thead>
    <tbody>
        @forelse($order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product->judul }}</td>
                <td style="text-align:right">Rp {{ number_format($item->harga_saat_beli, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td>1</td>
                <td>{{ $order->product->judul ?? '-' }}</td>
                <td style="text-align:right">{{ $order->totalRupiah() }}</td>
            </tr>
        @endforelse
    </tbody>
</table>

@php
    $subtotal = $order->items->sum('harga_saat_beli');
    if ($subtotal === 0) $subtotal = $order->total_bayar;
@endphp
<table class="totals">
    <tr><td>Subtotal</td><td style="text-align:right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td></tr>
    @if($order->discountCode)
        <tr><td>Diskon ({{ $order->discountCode->kode }})</td><td style="text-align:right">−Rp {{ number_format(max(0, $subtotal - $order->total_bayar), 0, ',', '.') }}</td></tr>
    @endif
    <tr class="grand"><td>Total Bayar</td><td style="text-align:right">Rp {{ number_format($order->total_bayar, 0, ',', '.') }}</td></tr>
</table>

<div class="notes">
    <strong>Yang perlu kamu tahu:</strong>
    <ol>
        <li>Admin memverifikasi pembayaran maks. 1×24 jam (jam kerja 08.00–20.00 WIB).</li>
        <li>Kode redeem dikirim ke WhatsApp {{ $order->waPembeli() ?? '-' }}.</li>
        <li>Login di website pakai Google (email: {{ $order->emailPembeli() ?? '-' }}), masukkan kode redeem di Home.</li>
    </ol>
</div>

<div class="footer">
    Rumah Cahaya-bks · Invoice ini dibuat otomatis · Simpan sebagai bukti pembelian
</div>

</body>
</html>
