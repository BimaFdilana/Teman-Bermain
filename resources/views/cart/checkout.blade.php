<x-landing-layout>
@section('title', 'Checkout — Rumah Cahaya-bks')

<section class="kidza-section" style="background:#F9F9F6">
    <div class="kidza-wrap" style="max-width:44rem">
        <div class="text-center mb-8">
            <span class="kidza-kicker">Langkah terakhir</span>
            <h1 class="kidza-h2 mb-2">📝 Data Pesanan</h1>
            <p style="color:#4b5563">Kode redeem & info pesanan dikirim ke email/WhatsApp ini.</p>
        </div>

        @if($errors->any())
            <div class="kidza-card p-4 mb-4" style="background:#fdecea"><p class="font-bold">{{ $errors->first() }}</p></div>
        @endif
        @if(session('gagal'))
            <div class="kidza-card p-4 mb-4" style="background:#fdecea"><p class="font-bold">{{ session('gagal') }}</p></div>
        @endif

        <div class="kidza-card p-6 md:p-8">
            <form method="POST" action="{{ route('cart.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="text-sm font-bold block mb-1">Nama lengkap</label>
                    <input type="text" name="nama" value="{{ old('nama', $user?->name) }}" required maxlength="100"
                           class="w-full border-2 border-gray-300 rounded-xl px-4 py-2" placeholder="Nama kamu">
                </div>
                <div class="mb-4">
                    <label class="text-sm font-bold block mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user?->email) }}" required maxlength="150"
                           class="w-full border-2 border-gray-300 rounded-xl px-4 py-2" placeholder="email@contoh.com">
                    <p class="text-xs mt-1" style="color:#6b7280">Nanti login pakai Google dengan email ini untuk klaim produk.</p>
                </div>
                <div class="mb-4">
                    <label class="text-sm font-bold block mb-1">No. WhatsApp</label>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp', $user?->whatsapp) }}" required maxlength="20"
                           class="w-full border-2 border-gray-300 rounded-xl px-4 py-2" placeholder="08xxxxxxxxxx">
                    <p class="text-xs mt-1" style="color:#6b7280">Kode redeem dikirim ke nomor ini.</p>
                </div>
                <div class="mb-4">
                    <label class="text-sm font-bold block mb-1">Metode pembayaran</label>
                    <select name="metode_bayar" id="metode_bayar" required
                            class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
                        <option value="qris" {{ old('metode_bayar') === 'qris' ? 'selected' : '' }}>QRIS</option>
                        @if(count($banks) > 0)
                            <option value="va" {{ old('metode_bayar') === 'va' ? 'selected' : '' }}>Rekening</option>
                        @endif
                    </select>
                </div>
                <div class="mb-6" id="bank_wrap" style="display:none">
                    <label class="text-sm font-bold block mb-1">Pilih bank</label>
                    <select name="bank_va" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
                        @foreach($banks as $kode => $nama)
                            <option value="{{ $kode }}" {{ old('bank_va') === $kode ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rounded-2xl p-4 mb-6 text-sm" style="background:#FFF9EC">
                    <div class="flex justify-between mb-1"><span>{{ $ringkasan['jumlah'] }} produk</span><span>Rp {{ number_format($ringkasan['subtotal'], 0, ',', '.') }}</span></div>
                    @if($ringkasan['diskon'] > 0)
                        <div class="flex justify-between mb-1" style="color:#167941"><span>Diskon</span><span>−Rp {{ number_format($ringkasan['diskon'], 0, ',', '.') }}</span></div>
                    @endif
                    @if($ringkasan['pajak'] > 0)
                        <div class="flex justify-between mb-1"><span>Pajak ({{ $ringkasan['pajak_persen'] }}%)</span><span>Rp {{ number_format($ringkasan['pajak'], 0, ',', '.') }}</span></div>
                    @endif
                    <div class="flex justify-between font-bold text-lg pt-2" style="border-top:2px solid #1a1a1a">
                        <span>Total bayar</span><span style="color:#167941">Rp {{ number_format($ringkasan['total'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <button type="submit" class="kidza-btn kidza-btn-green w-full text-lg">Bayar Sekarang</button>
                <p class="text-xs text-center mt-3" style="color:#6b7280">Dengan menekan Bayar, kamu menyetujui pesanan ini.</p>
            </form>
        </div>
    </div>
</section>

<script>
(function () {
    const metode = document.getElementById('metode_bayar');
    const wrap = document.getElementById('bank_wrap');
    function toggle() { wrap.style.display = metode.value === 'va' ? 'block' : 'none'; }
    metode.addEventListener('change', toggle);
    toggle();
})();
</script>
<script>
fbq('track', 'InitiateCheckout', {
    content_ids: @json($items->pluck('id')->map(fn ($id) => (string) $id)->all()),
    content_type: 'product',
    value: {{ (float) $ringkasan['total'] }},
    currency: 'IDR',
});
</script>
</x-landing-layout>
