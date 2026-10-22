<x-app-layout>
    @php
        $jumlahMiniapp = $user->ownedProducts('miniapp')->count();
        $jumlahEbook = $user->ownedProducts('ebook')->count();
    @endphp

    <!-- Strip judul -->
    <div class="rc-card px-5 py-3.5 mb-4" style="background:#FFC531;box-shadow:0 4px 0 #DFA408">
        <p class="font-display font-bold" style="color:#5B4A00">Profile 👤</p>
    </div>

    <!-- Identitas -->
    <div class="rc-card p-5 mb-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full flex items-center justify-center font-display text-2xl font-bold flex-none"
                 style="background:#FFC531;border:3px solid #FFE1A1">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="font-display font-bold text-lg leading-tight">{{ $user->name }}</p>
                <p class="text-xs font-semibold" style="color:#7A7261">Member sejak {{ $user->created_at->translatedFormat('F Y') }}</p>
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <span class="rc-chip" style="background:#FFF3D6;color:#9A6B00;padding:.2rem .7rem">⭐ {{ ucfirst($user->role) }}</span>
                    <span class="rc-chip" style="background:#E8F6EE;color:#167941;padding:.2rem .7rem">🎮 {{ $jumlahMiniapp }} MiniApp</span>
                    <span class="rc-chip" style="background:#E8F0FE;color:#1D4ED8;padding:.2rem .7rem">📚 {{ $jumlahEbook }} Ebook</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit data -->
    <div class="rc-card p-5 mb-4">
        <h2 class="font-display text-lg font-bold mb-3">✏️ Data Saya</h2>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('patch')

            <div class="mb-3">
                <x-input-label for="name" :value="'Nama'" />
                <x-text-input id="name" name="name" type="text" class="rc-input mt-1" :value="old('name', $user->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="mb-3">
                <x-input-label for="email" :value="'Email'" />
                <x-text-input id="email" name="email" type="email" class="rc-input mt-1" :value="old('email', $user->email)" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="whatsapp" :value="'No. WhatsApp'" />
                <x-text-input id="whatsapp" name="whatsapp" type="tel" class="rc-input mt-1" :value="old('whatsapp', $user->whatsapp)" placeholder="081234567890" />
                <x-input-error :messages="$errors->get('whatsapp')" class="mt-2" />
            </div>

            @if (session('status') === 'profile-updated')
                <p class="text-sm font-bold mb-3" style="color:#167941">✓ Data berhasil disimpan!</p>
            @endif

            <button type="submit" class="rc-btn rc-btn-kuning w-full">Simpan Perubahan</button>
        </form>
    </div>

    <!-- Menu -->
    <a href="{{ route('checkout.riwayat') }}" class="rc-card p-4 flex items-center gap-3 mb-3">
        <span class="w-11 h-11 rounded-2xl flex-none flex items-center justify-center text-xl" style="background:#FFF3D6">🧾</span>
        <span>
            <span class="font-display font-bold block leading-tight">Pesanan Saya</span>
            <span class="text-xs font-semibold" style="color:#7A7261">Riwayat & status pembayaran</span>
        </span>
        <span class="ml-auto font-bold" style="color:#7A7261">→</span>
    </a>

    <a href="https://wa.me/{{ \App\Models\Setting::get('wa_admin') }}" target="_blank" class="rc-card p-4 flex items-center gap-3 mb-5">
        <span class="w-11 h-11 rounded-2xl flex-none flex items-center justify-center text-xl" style="background:#E8F6EE">💬</span>
        <span>
            <span class="font-display font-bold block leading-tight">Bantuan WhatsApp</span>
            <span class="text-xs font-semibold" style="color:#7A7261">Chat admin 08.00–20.00 WIB</span>
        </span>
        <span class="ml-auto font-bold" style="color:#7A7261">→</span>
    </a>

    <!-- Logout -->
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="rc-btn rc-btn-merah w-full">Keluar</button>
    </form>

    <div class="h-4"></div>
</x-app-layout>
