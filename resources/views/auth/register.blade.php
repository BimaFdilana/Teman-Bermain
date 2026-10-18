<x-guest-layout>
    <h1 class="font-display text-2xl font-bold text-center mb-1">Buat akun baru</h1>
    <p class="text-sm text-center opacity-70 mb-6">Gratis! Untuk membeli Ebook &amp; MiniApps seru</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="'Nama'" />
            <x-text-input id="name" class="brutal-input mt-1" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama Ayah/Bunda" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" class="brutal-input mt-1" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- WhatsApp -->
        <div class="mt-4">
            <x-input-label for="whatsapp" :value="'No. WhatsApp'" />
            <x-text-input id="whatsapp" class="brutal-input mt-1" type="tel" name="whatsapp" :value="old('whatsapp')" required autocomplete="tel" placeholder="081234567890" />
            <p class="text-xs opacity-60 mt-1">Untuk pengiriman kode redeem via WhatsApp.</p>
            <x-input-error :messages="$errors->get('whatsapp')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="'Password'" />
            <x-text-input id="password" class="brutal-input mt-1" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="'Konfirmasi Password'" />
            <x-text-input id="password_confirmation" class="brutal-input mt-1" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-5">
            <button type="submit" class="brutal-btn brutal-btn-primary w-full">Daftar</button>
        </div>

        <p class="text-sm text-center mt-4">
            Sudah punya akun?
            <a class="underline font-bold hover:opacity-70" href="{{ route('login') }}">Masuk di sini</a>
        </p>
    </form>
</x-guest-layout>
