<x-landing-layout>
@section('title', 'Rumah Cahaya-bks — Ebook & MiniApp Edukasi Anak')

<!-- ===== 1. HERO ===== -->
<section class="relative overflow-hidden" style="background:#FFF9EC">
    <div class="kidza-blob kidza-float" style="width:220px;height:220px;background:#F3A712;top:40px;left:-60px"></div>
    <div class="kidza-blob kidza-float-2" style="width:160px;height:160px;background:#0077B6;bottom:60px;right:-40px;opacity:.35"></div>
    <div class="kidza-blob kidza-float" style="width:110px;height:110px;background:#167941;top:120px;right:12%;opacity:.25"></div>

    <div class="kidza-wrap relative z-10 grid md:grid-cols-2 gap-10 items-center py-16 md:py-24 px-5">
        <div>
            <span class="kidza-kicker">📚 Untuk anak PAUD & SD</span>
            <h1 class="font-bold leading-tight mb-4" style="font-family:'Quicksand';font-size:clamp(2.2rem,5.5vw,3.6rem);color:#1F2937">
                {{ $heroHeadline }}
            </h1>
            <p class="text-lg mb-8" style="color:#4b5563">{{ $heroSubheadline }}</p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('produk.index', ['tipe' => 'ebook']) }}" class="kidza-btn kidza-btn-green">Lihat Ebook</a>
                <a href="{{ route('produk.index', ['tipe' => 'miniapp']) }}" class="kidza-btn kidza-btn-yellow">Coba MiniApp</a>
            </div>
            <div class="flex gap-6 mt-8 text-sm font-bold" style="color:#4b5563">
                <span>✓ Bayar via QRIS</span>
                <span>✓ Kode via WhatsApp</span>
                <span>✓ Tanpa ribet</span>
            </div>
        </div>
        <div class="relative">
            <div class="kidza-card p-2 kidza-float" style="transform:rotate(2deg)">
                <img src="{{ asset('images/covers/belajar-membaca.svg') }}" alt="Ebook Belajar Membaca" class="rounded-3xl w-full aspect-[4/5] object-cover">
            </div>
            <div class="kidza-card kidza-float-2 absolute -bottom-6 -left-4 px-5 py-3 flex items-center gap-3" style="transform:rotate(-3deg)">
                <span class="text-3xl">🎮</span>
                <div>
                    <p class="font-bold" style="font-family:'Quicksand'">MiniApp Game</p>
                    <p class="text-sm" style="color:#4b5563">Main offline setelah dipasang</p>
                </div>
            </div>
            <div class="kidza-card kidza-float absolute -top-4 -right-2 px-5 py-3 flex items-center gap-3" style="transform:rotate(3deg);animation-delay:.7s">
                <span class="text-3xl">⭐</span>
                <div>
                    <p class="font-bold" style="font-family:'Quicksand'">4.9/5</p>
                    <p class="text-sm" style="color:#4b5563">dari orang tua</p>
                </div>
            </div>
        </div>
    </div>
    <svg class="kidza-wave" viewBox="0 0 1440 70" preserveAspectRatio="none"><path d="M0,40 C240,80 480,0 720,30 C960,60 1200,10 1440,40 L1440,70 L0,70 Z" fill="#F9F9F6"/></svg>
</section>

<!-- ===== 2. ABOUT / NILAI ===== -->
<section class="kidza-section" style="background:#F9F9F6">
    <div class="kidza-wrap text-center">
        <span class="kidza-kicker">Kenapa Rumah Cahaya?</span>
        <h2 class="kidza-h2 mb-3">Misi kami: literasi anak Indonesia</h2>
        <p class="max-w-2xl mx-auto mb-10 text-lg" style="color:#4b5563">
            Kami membuat Ebook dan MiniApp yang benar-benar dibuat untuk anak Indonesia —
            bahasanya sederhana, gambarnya ceria, dan harganya masuk akal untuk orang tua.
        </p>
        <div class="grid md:grid-cols-3 gap-6 text-left">
            <div class="kidza-card p-7">
                <div class="kidza-step-num mb-4" style="background:#167941">🛡</div>
                <h3 class="font-bold text-xl mb-2" style="font-family:'Quicksand'">Aman untuk Anak</h3>
                <p style="color:#4b5563">Tanpa iklan, tanpa konten asing yang tidak pantas. Semua materi dikurasi untuk usia PAUD dan SD.</p>
            </div>
            <div class="kidza-card p-7">
                <div class="kidza-step-num mb-4" style="background:#0077B6">🧠</div>
                <h3 class="font-bold text-xl mb-2" style="font-family:'Quicksand'">Edukatif & Interaktif</h3>
                <p style="color:#4b5563">Ebook bersuara dan game yang mengajak anak menyentuh, menjawab, dan berpikir — bukan sekadar menonton.</p>
            </div>
            <div class="kidza-card p-7">
                <div class="kidza-step-num mb-4" style="background:#F3A712">💛</div>
                <h3 class="font-bold text-xl mb-2" style="font-family:'Quicksand'">Terjangkau</h3>
                <p style="color:#4b5563">Mulai Rp 20 ribuan. Beli sekali, akses selamanya. Ada paket hemat untuk borong sekalian.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== 3. PAKET EBOOK ===== -->
<section class="kidza-section" style="background:#FFF9EC">
    <div class="kidza-wrap">
        <div class="text-center mb-10">
            <span class="kidza-kicker">Paling hemat</span>
            <h2 class="kidza-h2 mb-3">Paket Ebook Unggulan</h2>
            <p class="text-lg" style="color:#4b5563">Satu kode redeem untuk semua isi paket. Makin hemat, makin seru.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @forelse($paketUnggulan as $paket)
                <x-product-card :product="$paket" />
            @empty
                <p class="col-span-3 text-center" style="color:#4b5563">Paket segera hadir.</p>
            @endforelse
        </div>
        <div class="text-center mt-8">
            <a href="{{ route('harga') }}" class="kidza-btn kidza-btn-blue">Lihat Semua Paket</a>
        </div>
    </div>
</section>

<!-- ===== 4. PRODUK POPULER ===== -->
<section class="kidza-section" style="background:#F9F9F6">
    <div class="kidza-wrap">
        <div class="flex items-end justify-between mb-8 flex-wrap gap-4">
            <div>
                <span class="kidza-kicker">Paling disukai</span>
                <h2 class="kidza-h2">Ebook & MiniApp Populer</h2>
            </div>
            <a href="{{ route('produk.index') }}" class="kidza-btn kidza-btn-white">Lihat Semua →</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            @forelse($produkPopuler as $produk)
                <x-product-card :product="$produk" />
            @empty
                <p class="col-span-4 text-center" style="color:#4b5563">Produk segera hadir.</p>
            @endforelse
        </div>
    </div>
</section>

<!-- ===== 5. CARA BELI ===== -->
<section class="kidza-section" style="background:#EAF4FF">
    <div class="kidza-wrap">
        <div class="text-center mb-10">
            <span class="kidza-kicker">Gampang banget</span>
            <h2 class="kidza-h2 mb-3">Cara Beli dalam 4 Langkah</h2>
        </div>
        <div class="grid md:grid-cols-4 gap-6">
            @php
            $steps = [
                ['1', '#167941', 'Pilih Produk', 'Klik Beli pada Ebook, MiniApp, atau Paket favoritmu.'],
                ['2', '#0077B6', 'Bayar via QRIS', 'Scan kode QRIS dari e-wallet atau m-banking apa pun.'],
                ['3', '#F3A712', 'Upload Bukti', 'Unggah bukti bayar, admin verifikasi maks. 1×24 jam.'],
                ['4', '#e74c3c', 'Terima Kode via WA', 'Kode redeem dikirim ke WhatsApp. Redeem, lalu mainkan!'],
            ];
            @endphp
            @foreach($steps as $s)
                <div class="kidza-card p-6 text-center">
                    <div class="kidza-step-num mx-auto mb-4" style="background:{{ $s[1] }}">{{ $s[0] }}</div>
                    <h3 class="font-bold text-lg mb-2" style="font-family:'Quicksand'">{{ $s[2] }}</h3>
                    <p class="text-sm" style="color:#4b5563">{{ $s[3] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ===== 6. TESTIMONI ===== -->
<section class="kidza-section" style="background:#F9F9F6">
    <div class="kidza-wrap">
        <div class="text-center mb-8">
            <span class="kidza-kicker">Kata mereka</span>
            <h2 class="kidza-h2 mb-3">Cerita dari Orang Tua</h2>
            <p style="color:#4b5563">Geser untuk membaca testimoni lainnya →</p>
        </div>
        <div class="kidza-testi-track">
            @forelse($testimonials as $t)
                <div class="kidza-card p-6">
                    <div class="text-2xl mb-3">⭐⭐⭐⭐⭐</div>
                    <p class="mb-4" style="color:#374151">“{{ $t->isi }}”</p>
                    <div class="flex items-center gap-3">
                        <div class="kidza-step-num" style="background:#167941;font-size:1.1rem">{{ strtoupper(substr($t->nama, 0, 1)) }}</div>
                        <div>
                            <p class="font-bold" style="font-family:'Quicksand'">{{ $t->nama }}</p>
                            @if($t->peran)<p class="text-sm" style="color:#6b7280">{{ $t->peran }}</p>@endif
                        </div>
                    </div>
                </div>
            @empty
                <p style="color:#4b5563">Testimoni segera hadir.</p>
            @endforelse
        </div>
    </div>
</section>

<!-- ===== 7. MARQUEE BRAND ===== -->
<section style="background:#fff;border-top:3px solid #1a1a1a;border-bottom:3px solid #1a1a1a">
    <div class="kidza-marquee">
        <div class="kidza-marquee-track">
            @foreach(['Ebook Anak 📚', 'MiniApp Game 🎮', 'PAUD Ceria 🌈', 'SD Pintar ⭐', 'Bayar QRIS 💳', 'Kode via WA 💬'] as $b)
                <span>{{ $b }}</span><span>·</span>
            @endforeach
            @foreach(['Ebook Anak 📚', 'MiniApp Game 🎮', 'PAUD Ceria 🌈', 'SD Pintar ⭐', 'Bayar QRIS 💳', 'Kode via WA 💬'] as $b)
                <span>{{ $b }}</span><span>·</span>
            @endforeach
        </div>
    </div>
</section>

<!-- ===== 8. QUICK KONTAK ===== -->
<section class="kidza-section" style="background:#F9F9F6">
    <div class="kidza-wrap">
        <div class="kidza-card p-8 md:p-10 grid md:grid-cols-2 gap-8 items-center" style="background:#FFF9EC">
            <div>
                <h2 class="kidza-h2 mb-3">Butuh bantuan? Chat kami! 💬</h2>
                <p class="text-lg mb-2" style="color:#4b5563">Tim kami siap membantu Senin–Sabtu, 08.00–20.00 WIB.</p>
                <p style="color:#4b5563">Tanya produk, bantuan redeem, atau sekadar say hi — silakan!</p>
            </div>
            <div class="flex flex-col gap-4">
                <a href="https://wa.me/{{ \App\Models\Setting::get('wa_admin') }}?text=Halo%20Rumah%20Cahaya-bks!" target="_blank" class="kidza-btn kidza-btn-green w-full text-lg">Chat WhatsApp Admin</a>
                <p class="text-center text-sm" style="color:#6b7280">{{ \App\Models\Setting::get('alamat') }}</p>
            </div>
        </div>
    </div>
</section>
</x-landing-layout>
