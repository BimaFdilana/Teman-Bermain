<x-guest-layout>
    <h1 class="font-display text-2xl font-bold text-center mb-1">Selamat datang kembali!</h1>
    <p class="text-sm text-center opacity-70 mb-6">Masuk untuk lanjut belajar dan bermain</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Login dengan Google -->
    <a href="{{ route('google.redirect') }}" class="brutal-btn brutal-btn-white w-full mb-5">
        <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/></svg>
        Masuk dengan Google
    </a>

    <div class="flex items-center gap-3 mb-5">
        <div class="flex-1 border-t-[3px] border-[#1a1a1a]"></div>
        <span class="text-sm font-bold">atau</span>
        <div class="flex-1 border-t-[3px] border-[#1a1a1a]"></div>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" class="brutal-input mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="'Password'" />
            <x-text-input id="password" class="brutal-input mt-1" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 shadow-sm" name="remember" checked>
                <span class="ms-2 text-sm">Ingat saya (30 hari)</span>
            </label>
        </div>

        <div class="mt-5">
            <button type="submit" class="brutal-btn brutal-btn-primary w-full">Masuk</button>
        </div>

        <div class="flex items-center justify-between mt-4 text-sm">
            @if (Route::has('password.request'))
                <a class="underline hover:opacity-70" href="{{ route('password.request') }}">Lupa password?</a>
            @endif
            <a class="underline hover:opacity-70 font-bold" href="{{ route('register') }}">Daftar akun baru</a>
        </div>
    </form>
</x-guest-layout>
