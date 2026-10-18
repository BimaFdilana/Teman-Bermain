<x-app-layout>
    <!-- Sapaan -->
    <div class="brutal-card p-5 mb-5" style="background: linear-gradient(135deg, #fff 60%, #F7EDCC);">
        <h1 class="font-display text-2xl font-bold">Hai, {{ explode(' ', Auth::user()->name)[0] }}! 👋</h1>
        <p class="text-sm opacity-70 mt-1">Siap belajar dan bermain hari ini?</p>
    </div>

    <!-- Redeem Code -->
    <div class="brutal-card p-5 mb-5">
        <h2 class="font-display text-lg font-bold mb-1">Punya Kode Redeem?</h2>
        <p class="text-sm opacity-70 mb-3">Masukkan kode dari WhatsApp untuk membuka Ebook/MiniApp kamu.</p>
        <form method="POST" action="#" class="flex gap-2">
            @csrf
            <input type="text" name="code" class="brutal-input uppercase" placeholder="RC-XXXX-XXXX" style="font-family:'Fredoka'">
            <button type="submit" class="brutal-btn brutal-btn-yellow whitespace-nowrap">Redeem</button>
        </form>
    </div>

    <!-- MiniApps milikku -->
    <h2 class="font-display text-lg font-bold mb-3">MiniApps Milikku</h2>
    <div class="brutal-card p-8 text-center">
        <div class="text-5xl mb-3">🎮</div>
        <p class="font-bold">Belum ada MiniApp</p>
        <p class="text-sm opacity-70 mt-1">Beli MiniApp seru lalu redeem kodenya di sini.<br>Segera hadir!</p>
    </div>
</x-app-layout>
