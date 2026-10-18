<x-app-layout>
    <h1 class="font-display text-2xl font-bold mb-5">Profile</h1>

    <!-- Data pengguna -->
    <div class="brutal-card p-5 mb-5">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-display text-2xl font-bold text-white"
                 style="background:#167941; border:3px solid #1a1a1a;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-display font-bold text-lg">{{ $user->name }}</p>
                <span class="brutal-badge" style="background:#F3A712">{{ ucfirst($user->role) }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('patch')

            <div class="mb-3">
                <x-input-label for="name" :value="'Nama'" />
                <x-text-input id="name" name="name" type="text" class="brutal-input mt-1" :value="old('name', $user->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="mb-3">
                <x-input-label for="email" :value="'Email'" />
                <x-text-input id="email" name="email" type="email" class="brutal-input mt-1" :value="old('email', $user->email)" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="whatsapp" :value="'No. WhatsApp'" />
                <x-text-input id="whatsapp" name="whatsapp" type="tel" class="brutal-input mt-1" :value="old('whatsapp', $user->whatsapp)" placeholder="081234567890" />
                <x-input-error :messages="$errors->get('whatsapp')" class="mt-2" />
            </div>

            @if (session('status') === 'profile-updated')
                <p class="text-sm font-bold mb-3" style="color:#167941">✓ Data berhasil disimpan!</p>
            @endif

            <button type="submit" class="brutal-btn brutal-btn-primary w-full">Simpan Perubahan</button>
        </form>
    </div>

    <!-- Riwayat pembelian (stub) -->
    <div class="brutal-card p-5 mb-5">
        <h2 class="font-display text-lg font-bold mb-2">Riwayat Pembelian</h2>
        <p class="text-sm opacity-70">Belum ada pembelian. Riwayat akan muncul di sini.</p>
    </div>

    <!-- Logout -->
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="brutal-btn brutal-btn-white w-full" style="color:#b91c1c">
            Keluar (Logout)
        </button>
    </form>

    <div class="h-4"></div>
</x-app-layout>
