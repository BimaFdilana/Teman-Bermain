<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Rumah Cahaya-bks') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/favicon-64.png') }}">

        <!-- Fonts: Fredoka (heading) + Nunito (body) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @yield('head')
        @include('partials.meta-pixel-script')
    </head>
    <body class="antialiased">
        @include('partials.meta-pixel-noscript')
        <div class="app-shell min-h-screen">
            <!-- Header -->
            <header class="bg-white sticky top-0 z-30" style="border-bottom:2px solid #EDE4C8">
                <div class="max-w-2xl mx-auto px-4 py-2.5 flex items-center gap-3">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="Rumah Cahaya-bks" class="h-10 w-10">
                    <span class="font-display font-bold text-lg leading-none">
                        <span style="color:#167941">rumah</span><span style="color:#F3A712">cahaya</span><span class="text-sm" style="color:#0077B6">-bks</span>
                    </span>
                </div>
            </header>

            <!-- Page content -->
            <main class="max-w-2xl mx-auto px-4 py-5">
                {{ $slot }}
            </main>

            <!-- Bottom navigation: Home · MiniApp · Ebook · Profile (tampil juga di web) -->
            <nav class="bottom-nav">
                <div class="bottom-nav-inner">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                        <img src="{{ asset('images/nav/nav-home.png') }}" alt="">
                        <span>Home</span>
                    </a>
                    <a href="{{ route('miniapp.index') }}" class="{{ request()->routeIs('miniapp.index') || request()->routeIs('miniapp.main') || request()->routeIs('miniapp.install') ? 'active' : '' }}">
                        <img src="{{ asset('images/nav/nav-miniapp.png') }}" alt="">
                        <span>MiniApp</span>
                    </a>
                    <a href="{{ route('ebook') }}" class="{{ request()->routeIs('ebook') || request()->routeIs('ebook.baca') ? 'active' : '' }}">
                        <img src="{{ asset('images/nav/nav-ebook.png') }}" alt="">
                        <span>Ebook</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <img src="{{ asset('images/nav/nav-profile.png') }}" alt="">
                        <span>Profile</span>
                    </a>
                </div>
            </nav>
        </div>
    @include('components.dialog-custom')
<script>
/* Daftarkan SW PWA lebih awal (semua halaman user) agar saat buka player,
   SW sudah aktif dan Chrome bisa memicu beforeinstallprompt */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register("{{ route('miniapp.sw') }}", { scope: '/' }).catch(function () {});
}
</script>
</body>
</html>
