<x-landing-layout>
@section('title', 'Terima Kasih — Rumah Cahaya-bks')

<section class="kidza-section" style="background:#F9F9F6">
    <div class="kidza-wrap" style="max-width:36rem">
        <div class="kidza-card p-8 md:p-10 text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h1 class="kidza-h2 mb-3">Terima kasih sudah membeli produk kami!</h1>
            <p class="mb-2" style="color:#4b5563">Pesanan akan kami kirim melalui <strong>Email</strong> dan <strong>WhatsApp</strong>.</p>
            <p class="mb-6" style="color:#4b5563">Nomor order/invoice kamu:</p>
            <p class="font-bold text-2xl mb-6" style="font-family:'Quicksand';color:#167941">{{ $order->kode_order }}</p>

            <a href="{{ route('checkout.invoice-pdf', $order->kode_order) }}" class="kidza-btn kidza-btn-yellow mb-6" style="display:inline-block">📄 Download Invoice (PDF)</a>

            <div class="rounded-2xl p-4 text-left text-sm mb-6" style="background:#FFF9EC">
                <p class="font-bold mb-2">Yang perlu kamu tahu:</p>
                <ol class="list-decimal list-inside space-y-1" style="color:#4b5563">
                    <li>Admin memverifikasi pembayaran maks. 1×24 jam.</li>
                    <li>Kode redeem dikirim ke WA <strong>{{ $order->waPembeli() }}</strong>.</li>
                    <li>Login di website pakai Google (email: {{ $order->emailPembeli() }}), masukkan kode di Home.</li>
                </ol>
            </div>

            <a href="{{ route('landing') }}" class="kidza-btn kidza-btn-green">Kembali ke Beranda</a>
        </div>
    </div>
</section>
</x-landing-layout>
