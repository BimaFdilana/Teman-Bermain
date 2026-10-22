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
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
            <a href="{{ route('landing') }}" class="mb-6">
                <img src="{{ asset('images/logo-stacked.png') }}" alt="Rumah Cahaya-bks" class="h-28 w-auto">
            </a>

            <div class="brutal-card w-full max-w-md p-6 sm:p-8">
                {{ $slot }}
            </div>

            <p class="mt-6 text-sm text-center opacity-70">
                Ebook &amp; MiniApps anak PAUD/SD yang seru dan edukatif
            </p>
        </div>
    </body>
</html>
