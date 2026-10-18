<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin — Rumah Cahaya-bks</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-64.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased kidza" style="background:#1F2937">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="kidza-card w-full max-w-md p-8">
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo-stacked.png') }}" alt="Rumah Cahaya-bks" class="h-20 w-auto mx-auto mb-3">
                <h1 class="font-bold text-2xl" style="font-family:'Quicksand'">Login Admin</h1>
                <p class="text-sm" style="color:#6b7280">Email + password saja · maks 5x percobaan/menit</p>
            </div>

            @if($errors->any())
                <div class="kidza-card p-3 mb-4" style="background:#fdecea">
                    <p class="text-sm font-bold" style="color:#e74c3c">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="text-sm font-bold block mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
                </div>
                <div class="mb-5">
                    <label class="text-sm font-bold block mb-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 pr-12">
                        <button type="button" onclick="togglePassword()" aria-label="Tampilkan/sembunyikan password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-xl leading-none" style="color:#6b7280">
                            <span id="eye-icon">👁️</span>
                        </button>
                    </div>
                </div>
                <button type="submit" class="kidza-btn kidza-btn-green w-full text-lg">Masuk</button>
            </form>
        </div>
    </div>
<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    const tampil = input.type === 'password';
    input.type = tampil ? 'text' : 'password';
    icon.textContent = tampil ? '🙈' : '👁️';
}
</script>
</body>
</html>
