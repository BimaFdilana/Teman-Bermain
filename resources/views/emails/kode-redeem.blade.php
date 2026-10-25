<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Kode redeem Rumah Cahaya-bks</title></head>
<body style="font-family:Arial,sans-serif;color:#222;line-height:1.6">
<p>Halo {{ $order->namaPembeli() }}! Terima kasih sudah membeli di Rumah Cahaya-bks 🎉</p>
<p>📦 No. Order/Invoice: <b>{{ $order->kode_order }}</b></p>
<p>🔑 Kode redeem kamu:</p>
<ul>
@foreach($kodes as $kode)
    <li><b>{{ $kode }}</b></li>
@endforeach
</ul>
<p>Cara klaim:</p>
<ol>
    <li>Login di {{ config('app.url') }} pakai Google (email: {{ $order->emailPembeli() }})</li>
    <li>Buka Home, masukkan kode redeem di kolom Redeem</li>
    <li>Klik Redeem — produk langsung jadi milikmu!</li>
</ol>
<p>Jika ada kendala, balas email ini atau hubungi admin via WhatsApp. Terima kasih!</p>
</body>
</html>
