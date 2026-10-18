<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Rumah Cahaya-bks — Ebook interaktif & MiniApp game edukasi untuk anak PAUD dan SD.">

    <title>@yield('title', 'Rumah Cahaya-bks — Ebook & MiniApp Edukasi Anak')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-64.png') }}">

    <!-- Fonts: Baloo 2 (display ala poster) + Quicksand + Nunito (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Quicksand:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased kidza poster">
    <!-- ===== Navbar ===== -->
    <header class="kidza-nav">
        <div class="kidza-nav-inner">
            <a href="{{ route('landing') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/logo-stacked.png') }}" alt="Rumah Cahaya-bks" class="h-12 w-auto">
            </a>
            <nav class="kidza-menu">
                <a href="{{ route('landing') }}" class="{{ request()->routeIs('landing') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('produk.index') }}" class="{{ request()->routeIs('produk.*') ? 'active' : '' }}">Produk</a>
                <a href="{{ route('harga') }}" class="{{ request()->routeIs('harga') ? 'active' : '' }}">Harga</a>
            </nav>
            <div class="kidza-nav-cta">
                @auth
                    <a href="{{ route('home') }}" class="kidza-btn kidza-btn-green">Buka Akun Saya</a>
                @else
                    <a href="{{ route('login') }}" class="kidza-link">Masuk</a>
                    <a href="{{ route('register') }}" class="kidza-btn kidza-btn-yellow" style="color:#FFF8E7">Daftar Gratis</a>
                @endauth
                <button class="kidza-burger" onclick="document.getElementById('kidza-drawer').classList.toggle('open')" aria-label="Menu">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
            </div>
        </div>
        <!-- Mobile drawer -->
        <div id="kidza-drawer" class="kidza-drawer">
            <a href="{{ route('landing') }}">Beranda</a>
            <a href="{{ route('produk.index') }}">Produk</a>
            <a href="{{ route('harga') }}">Harga</a>
            @auth
                <a href="{{ route('home') }}">Buka Akun Saya</a>
            @else
                <a href="{{ route('login') }}">Masuk</a>
                <a href="{{ route('register') }}">Daftar Gratis</a>
            @endauth
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <!-- ===== Footer ===== -->
    <footer class="kidza-footer" style="background:linear-gradient(rgba(20,83,45,.78),rgba(20,83,45,.78)),url('{{ asset('storage/'.\App\Models\Setting::get('footer_bg', 'landing/galeri-2.jpg')) }}') center/cover;">
        <div class="kidza-footer-inner">
            <div class="kidza-footer-brand">
                <img src="{{ asset('images/logo-stacked.png') }}" alt="Rumah Cahaya-bks" class="h-16 w-auto mb-3">
                <p>{{ \App\Models\Setting::get('footer_tagline', 'Ebook interaktif & MiniApp game edukasi untuk anak PAUD dan SD Indonesia.') }}</p>
            </div>
            <div>
                <h4>Jelajah</h4>
                <a href="{{ route('landing') }}">Beranda</a>
                <a href="{{ route('produk.index') }}">Semua Produk</a>
                <a href="{{ route('harga') }}">Harga & Paket</a>
            </div>
            <div>
                <h4>Akun</h4>
                <a href="{{ route('login') }}">Masuk</a>
                <a href="{{ route('register') }}">Daftar</a>
            </div>
            <div>
                <h4>Hubungi Kami</h4>
                <p>WhatsApp: {{ \App\Models\Setting::get('wa_admin', '-') }}</p>
                <p>{{ \App\Models\Setting::get('jam_operasional', '') }}</p>
                <p>{{ \App\Models\Setting::get('alamat', '') }}</p>
            </div>
        </div>
        <div class="kidza-copy">
            © {{ date('Y') }} Rumah Cahaya-bks · <a href="#">Kebijakan Privasi</a> · <a href="#">Syarat & Ketentuan</a>
        </div>
    </footer>
</body>
</html>
