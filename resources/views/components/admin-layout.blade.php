@props(['fullwidth' => false])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — {{ config('app.name', 'Rumah Cahaya-bks') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-64.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('head')
    <style>
        .admin-shell { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 240px; flex-shrink: 0; background: #1F2937; color: #F9F9F6;
            padding: 1.5rem 1rem; position: sticky; top: 0; height: 100vh; overflow-y: auto;
        }
        .side-group-title {
            font-family: 'Quicksand', sans-serif; font-size: .68rem; font-weight: 800;
            letter-spacing: .1em; text-transform: uppercase; color: #8a8f98;
            padding: 1rem .9rem .35rem; margin: 0;
        }
        .side-group-title:first-of-type { padding-top: .2rem; }
        .admin-sidebar a.side-link {
            display: flex; align-items: center; gap: .7rem;
            padding: .7rem .9rem; border-radius: .9rem; margin-bottom: .3rem;
            font-family: 'Quicksand', sans-serif; font-weight: 700; color: #d8d8d2;
        }
        .admin-sidebar a.side-link:hover { background: rgba(255,255,255,.08); color: #fff; }
        .admin-sidebar a.side-link.active { background: #F3A712; color: #1a1a1a; }
        .admin-main { flex: 1; padding: 2rem; max-width: 72rem; }
        .admin-main.full { max-width: none; }
        .admin-topbar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1.5rem;
        }
        @media (max-width: 768px) {
            .admin-shell { flex-direction: column; }
            .admin-sidebar { width: 100%; height: auto; position: static; padding: 1rem; }
            .admin-sidebar nav { display: flex; gap: .4rem; overflow-x: auto; }
            .admin-sidebar a.side-link { white-space: nowrap; margin-bottom: 0; }
            .side-group-title { display: none; }
            .admin-main { padding: 1.25rem; }
        }
    </style>
</head>
<body class="antialiased kidza" style="background:#F9F9F6">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="flex items-center gap-2 mb-6 px-2">
            <img src="{{ asset('images/logo-icon.png') }}" alt="" class="h-10 w-10">
            <div>
                <p class="font-bold leading-none" style="font-family:'Quicksand'">Admin</p>
                <p class="text-xs opacity-60">Rumah Cahaya-bks</p>
            </div>
        </div>
        <nav>
            <p class="side-group-title">Utama</p>
            <a href="{{ route('admin.dashboard') }}" class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">📊 Dashboard</a>
            <p class="side-group-title">Katalog</p>
            <a href="{{ route('admin.ebook.index') }}" class="side-link {{ request()->routeIs('admin.ebook.*') ? 'active' : '' }}">📚 Ebook</a>
            <a href="{{ route('admin.miniapp.index') }}" class="side-link {{ request()->routeIs('admin.miniapp.*') ? 'active' : '' }}">🎮 MiniApps</a>
            <a href="{{ route('admin.produk.index') }}" class="side-link {{ request()->routeIs('admin.produk.*') ? 'active' : '' }}">🏷️ Manage Product</a>
            <p class="side-group-title">Penjualan</p>
            <a href="{{ route('admin.pembayaran.index') }}" class="side-link {{ request()->routeIs('admin.pembayaran.*') ? 'active' : '' }}">💳 Pembayaran</a>
            <a href="{{ route('admin.diskon.index') }}" class="side-link {{ request()->routeIs('admin.diskon.*') ? 'active' : '' }}">🎟️ Diskon</a>
            <a href="{{ route('admin.laporan.index') }}" class="side-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">📈 Laporan</a>
            <p class="side-group-title">Promosi</p>
            <a href="{{ route('admin.iklan.index') }}" class="side-link {{ request()->routeIs('admin.iklan.*') ? 'active' : '' }}">📢 Iklan Promo</a>
            <a href="{{ route('admin.landing.index') }}" class="side-link {{ request()->routeIs('admin.landing.*') ? 'active' : '' }}">🌐 Landing Page</a>
            <p class="side-group-title">Lainnya</p>
            <a href="{{ route('admin.pengaturan.index') }}" class="side-link {{ request()->routeIs('admin.pengaturan.*') ? 'active' : '' }}">⚙️ Pengaturan</a>
            <a href="{{ route('landing') }}" class="side-link" target="_blank">👁️ Lihat Web</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="side-link w-full text-left" style="color:#f0a0a0">🚪 Keluar</button>
            </form>
        </nav>
    </aside>
    <div class="admin-main{{ $fullwidth ? ' full' : '' }}">
        @if(session('sukses'))
            <div class="kidza-card p-4 mb-5" style="background:#e8f6ee"><p class="font-bold">{{ session('sukses') }}</p></div>
        @endif
        @if(session('gagal'))
            <div class="kidza-card p-4 mb-5" style="background:#fdecea"><p class="font-bold">{{ session('gagal') }}</p></div>
        @endif
        {{ $slot }}
    </div>
</div>
</body>
</html>
