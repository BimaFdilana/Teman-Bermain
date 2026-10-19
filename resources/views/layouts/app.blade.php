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
    </head>
    <body class="antialiased">
        <div class="app-shell min-h-screen">
            <!-- Header -->
            <header class="bg-white border-b-[3px] border-[#1a1a1a] sticky top-0 z-30">
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

            <!-- Bottom navigation: Home · Ebook · Profile (PRD §5.8, tampil juga di web) -->
            <nav class="bottom-nav">
                <div class="bottom-nav-inner">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/></svg>
                        <span>Home</span>
                    </a>
                    <a href="{{ route('ebook') }}" class="{{ request()->routeIs('ebook') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/></svg>
                        <span>Ebook</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
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
