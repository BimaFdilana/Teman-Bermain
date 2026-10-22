<x-landing-layout>
@section('title', 'Rumah Cahaya-bks — Ebook & MiniApp Edukasi Anak')

<!-- ===== 1. HERO ===== -->
<section class="relative overflow-hidden" style="background:linear-gradient(135deg,#22803F 0%,#14532D 100%)">
    <span class="poster-leaf" style="top:24px;left:18px">🌿</span>
    <span class="poster-leaf" style="top:36px;right:26px">🍃</span>
    <span class="poster-leaf" style="bottom:90px;left:6%">🌱</span>
    <span class="poster-leaf" style="bottom:120px;right:8%">🌿</span>

    <div class="kidza-wrap relative z-10 grid md:grid-cols-2 gap-10 items-center py-14 md:py-20 px-5">
        <div class="text-center md:text-left">
            <div class="mb-5">
                <span class="kidza-kicker poster-kicker-yellow">🌱 Untuk anak PAUD & SD</span>
            </div>
            <h1 class="font-extrabold leading-tight mb-4" style="font-size:clamp(2.2rem,5.5vw,3.4rem);color:#FFF8E7">
                {{ $heroHeadline }}
            </h1>
            <p class="text-lg mb-8" style="color:#FFF8E7">{{ $heroSubheadline }}</p>
            <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                <a href="{{ route('produk.index', ['tipe' => 'ebook']) }}" class="kidza-btn kidza-btn-yellow">📚 Lihat Ebook</a>
                <a href="{{ route('produk.index', ['tipe' => 'miniapp']) }}" class="kidza-btn kidza-btn-white">🎮 Coba MiniApp</a>
            </div>
            <div class="flex flex-wrap gap-x-6 gap-y-2 mt-8 text-sm font-bold justify-center md:justify-start" style="color:#FFF8E7">
                <span>✓ Bayar via QRIS</span>
                <span>✓ Kode via WhatsApp</span>
                <span>✓ Tanpa ribet</span>
            </div>
        </div>
        <div class="relative">
            <div class="kidza-card p-2 kidza-float-soft" style="transform:rotate(2deg)">
                <img src="{{ $heroImage }}" alt="Ebook Rumah Cahaya-bks" class="rounded-3xl w-full aspect-[4/5] object-cover">
            </div>
            <div class="kidza-card kidza-float-2 absolute -bottom-6 -left-2 md:-left-4 px-5 py-3 flex items-center gap-3" style="transform:rotate(-3deg)">
                <span class="text-3xl">🎮</span>
                <div>
                    <p class="font-extrabold" style="color:#14532D">MiniApp Game</p>
                    <p class="text-sm" style="color:#5B5344">Main offline setelah dipasang</p>
                </div>
            </div>
            <div class="kidza-card kidza-float absolute -top-4 -right-1 md:-right-2 px-5 py-3 flex items-center gap-3" style="transform:rotate(3deg);animation-delay:.7s">
                <span class="text-3xl">⭐</span>
                <div>
                    <p class="font-extrabold" style="color:#14532D">4.9/5</p>
                    <p class="text-sm" style="color:#5B5344">dari orang tua</p>
                </div>
            </div>
        </div>
    </div>
    <svg class="kidza-wave" viewBox="0 0 1440 70" preserveAspectRatio="none"><path d="M0,40 C240,80 480,0 720,30 C960,60 1200,10 1440,40 L1440,70 L0,70 Z" fill="#FFF8E7"/></svg>
</section>

<!-- ===== 2. TENTANG / NILAI ===== -->
<section class="kidza-section relative overflow-hidden" style="background:linear-gradient(rgba(255,248,231,.78),rgba(255,248,231,.78)),url('{{ asset('storage/'.\App\Models\Setting::get('misi_bg', 'landing/galeri-1.jpg')) }}') center/cover;">
    <span class="poster-leaf" style="top:30px;right:30px">🌿</span>
    <span class="poster-leaf" style="bottom:40px;left:24px">🍃</span>
    <div class="kidza-wrap text-center relative z-10">
        <span class="kidza-kicker">🌱 Kenapa Rumah Cahaya?</span>
        <h2 class="kidza-h2 mb-3"><span class="poster-brush">Misi kami: literasi anak Indonesia</span></h2>
        <p class="max-w-2xl mx-auto mb-10 text-lg" style="color:#5B5344">
            Kami membuat Ebook dan MiniApp yang benar-benar dibuat untuk anak Indonesia —
            bahasanya sederhana, gambarnya ceria, dan harganya masuk akal untuk orang tua.
        </p>
        <div class="grid md:grid-cols-3 gap-6 text-left">
            <div class="poster-info-card">
                <div class="poster-info-head">🛡️ Aman untuk Anak</div>
                <p class="p-6 pt-4" style="color:#5B5344">Tanpa iklan, tanpa konten asing yang tidak pantas. Semua materi dikurasi untuk usia PAUD dan SD.</p>
            </div>
            <div class="poster-info-card">
                <div class="poster-info-head">🧠 Edukatif & Interaktif</div>
                <p class="p-6 pt-4" style="color:#5B5344">Ebook bersuara dan game yang mengajak anak menyentuh, menjawab, dan berpikir — bukan sekadar menonton.</p>
            </div>
            <div class="poster-info-card">
                <div class="poster-info-head">💛 Terjangkau</div>
                <p class="p-6 pt-4" style="color:#5B5344">Mulai Rp 20 ribuan. Beli sekali, akses selamanya. Ada paket hemat untuk borong sekalian.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== 3. PAKET UNGGULAN ===== -->
<section class="kidza-section relative overflow-hidden" style="background:#FFFEF7">
    <span class="poster-leaf" style="top:26px;left:34px">🍃</span>
    <div class="kidza-wrap relative z-10">
        <div class="text-center mb-10">
            <span class="kidza-kicker">🎁 Paling hemat</span>
            <h2 class="kidza-h2 mb-3"><span class="poster-brush">Paket Ebook Unggulan</span></h2>
            <p class="text-lg" style="color:#5B5344">Satu kode redeem untuk semua isi paket. Makin hemat, makin seru.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @forelse($paketUnggulan as $paket)
                <x-product-card :product="$paket" />
            @empty
                <p class="col-span-3 text-center" style="color:#5B5344">Paket segera hadir.</p>
            @endforelse
        </div>
        <div class="text-center mt-8">
            <a href="{{ route('harga') }}" class="kidza-btn kidza-btn-blue">Lihat Semua Paket</a>
        </div>
    </div>
</section>

<!-- ===== 4. PRODUK POPULER ===== -->
<section class="kidza-section relative overflow-hidden" style="background:#FFF8E7">
    <span class="poster-leaf" style="top:30px;right:40px">🌿</span>
    <div class="kidza-wrap relative z-10">
        <div class="flex items-end justify-between mb-8 flex-wrap gap-4">
            <div>
                <span class="kidza-kicker">⭐ Paling disukai</span>
                <h2 class="kidza-h2"><span class="poster-brush">Ebook & MiniApp Populer</span></h2>
            </div>
            <a href="{{ route('produk.index') }}" class="kidza-btn kidza-btn-white">Lihat Semua →</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            @forelse($produkPopuler as $produk)
                <x-product-card :product="$produk" />
            @empty
                <p class="col-span-4 text-center" style="color:#5B5344">Produk segera hadir.</p>
            @endforelse
        </div>
    </div>
</section>

<!-- ===== 5. CARA BELI ===== -->
<section class="kidza-section relative overflow-hidden" style="background:#FFFEF7">
    <span class="poster-leaf" style="bottom:36px;right:30px">🌱</span>
    <div class="kidza-wrap relative z-10">
        <div class="text-center mb-10">
            <span class="kidza-kicker">👣 Gampang banget</span>
            <h2 class="kidza-h2 mb-3"><span class="poster-brush">Cara Beli dalam 4 Langkah</span></h2>
        </div>
        <div class="grid md:grid-cols-4 gap-6">
            @php
            $steps = [
                ['1', 'Pilih Produk', 'Klik Beli pada Ebook, MiniApp, atau Paket favoritmu.'],
                ['2', 'Bayar via QRIS', 'Scan kode QRIS dari e-wallet atau m-banking apa pun.'],
                ['3', 'Upload Bukti', 'Unggah bukti bayar, admin verifikasi maks. 1×24 jam.'],
                ['4', 'Terima Kode via WA', 'Kode redeem dikirim ke WhatsApp. Redeem, lalu mainkan!'],
            ];
            @endphp
            @foreach($steps as $s)
                <div class="poster-info-card text-center">
                    <div class="kidza-step-num mx-auto mt-6 mb-4">{{ $s[0] }}</div>
                    <h3 class="font-extrabold text-lg mb-2 px-4" style="color:#14532D">{{ $s[1] }}</h3>
                    <p class="text-sm px-6 pb-6" style="color:#5B5344">{{ $s[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ===== 6. TESTIMONI ===== -->
<section class="kidza-section relative overflow-hidden" style="background:linear-gradient(rgba(255,248,231,.72),rgba(255,248,231,.72)),url('{{ asset('storage/'.\App\Models\Setting::get('testimoni_bg', 'landing/testimoni-bg.jpg')) }}') center/cover;">
    <span class="poster-leaf" style="top:28px;left:28px">🌿</span>
    <div class="kidza-wrap relative z-10">
        <div class="text-center mb-8">
            <span class="kidza-kicker">💬 Kata mereka</span>
            <h2 class="kidza-h2 mb-3"><span class="poster-brush">Cerita dari Orang Tua</span></h2>
            <p style="color:#5B5344">Geser untuk membaca testimoni lainnya →</p>
        </div>
        <div class="kidza-testi-track">
            @forelse($testimonials as $t)
                <div class="kidza-card p-6">
                    <div class="text-2xl mb-3">⭐⭐⭐⭐⭐</div>
                    <p class="mb-4" style="color:#3E3A2C">“{{ $t->isi }}”</p>
                    <div class="flex items-center gap-3">
                        <div class="kidza-step-num" style="font-size:1.1rem">{{ strtoupper(substr($t->nama, 0, 1)) }}</div>
                        <div>
                            <p class="font-extrabold" style="color:#14532D">{{ $t->nama }}</p>
                            @if($t->peran)<p class="text-sm" style="color:#6b7280">{{ $t->peran }}</p>@endif
                        </div>
                    </div>
                </div>
            @empty
                <p style="color:#5B5344">Testimoni segera hadir.</p>
            @endforelse
        </div>
    </div>
</section>

<!-- ===== 7. MARQUEE / LOGO SLIDER ===== -->
@php $logoSlider = json_decode(\App\Models\Setting::get('logo_slider', '[]') ?? '[]', true) ?: []; @endphp
<section style="background:#FFFDF0;border-top:3px solid #EDE4C8;border-bottom:3px solid #EDE4C8">
    <div class="kidza-marquee">
        <div class="kidza-marquee-track {{ !empty($logoSlider) ? 'kidza-marquee-track-logo' : '' }}">
            @if(!empty($logoSlider))
                {{-- Diulang 6x: separuh track harus selalu lebih lebar dari layar agar mulus ujung ke ujung --}}
                @foreach([1, 2, 3, 4, 5, 6] as $putaran)
                    @foreach($logoSlider as $path)
                        <img src="{{ asset('storage/'.$path) }}" alt="Logo" class="kidza-marquee-logo">
                    @endforeach
                @endforeach
            @else
                @foreach(['Ebook Anak 📚', 'MiniApp Game 🎮', 'PAUD Ceria 🌈', 'SD Pintar ⭐', 'Bayar QRIS 💳', 'Kode via WA 💬'] as $b)
                    <span>{{ $b }}</span><span>🌿</span>
                @endforeach
                @foreach(['Ebook Anak 📚', 'MiniApp Game 🎮', 'PAUD Ceria 🌈', 'SD Pintar ⭐', 'Bayar QRIS 💳', 'Kode via WA 💬'] as $b)
                    <span>{{ $b }}</span><span>🌿</span>
                @endforeach
            @endif
        </div>
    </div>
</section>

<!-- ===== 8. QUICK KONTAK ===== -->
<section class="kidza-section relative overflow-hidden" style="background:#FFFEF7">
    <span class="poster-leaf" style="top:30px;right:36px">🍃</span>
    <div class="kidza-wrap relative z-10">
        <div class="poster-info-card p-8 md:p-10 grid md:grid-cols-2 gap-8 items-center">
            <div>
                <span class="kidza-kicker">💬 Butuh bantuan?</span>
                <h2 class="kidza-h2 mb-3"><span class="poster-brush">Chat kami!</span></h2>
                <p class="text-lg mb-2" style="color:#5B5344">Tim kami siap membantu Senin–Sabtu, 08.00–20.00 WIB.</p>
                <p style="color:#5B5344">Tanya produk, bantuan redeem, atau sekadar say hi — silakan!</p>
            </div>
            <div class="flex flex-col gap-4">
                <a href="https://wa.me/{{ \App\Models\Setting::get('wa_admin') }}?text=Halo%20Rumah%20Cahaya-bks!" target="_blank" class="kidza-btn kidza-btn-green w-full text-lg">Chat WhatsApp Admin</a>
                <p class="text-center text-sm" style="color:#6b7280">{{ \App\Models\Setting::get('alamat') }}</p>
            </div>
        </div>
    </div>
</section>
</x-landing-layout>
