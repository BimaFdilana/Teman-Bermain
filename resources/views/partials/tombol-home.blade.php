{{-- Tombol melayang "Kembali ke Home" — hanya untuk customer yang sedang login,
     agar tidak tersesat saat masuk halaman katalog/produk (layout landing). --}}
@auth
    @if(auth()->user()->role !== 'admin')
        <div style="position:fixed;left:0;right:0;bottom:1.25rem;z-index:60;display:flex;justify-content:center;pointer-events:none">
            <a href="{{ route('home') }}"
               style="pointer-events:auto;display:inline-flex;align-items:center;gap:.5rem;background:#FFC531;color:#17233B;font-family:'Baloo 2','Fredoka',sans-serif;font-weight:700;font-size:1rem;padding:.8rem 1.6rem;border-radius:999px;box-shadow:0 5px 0 #DFA408,0 14px 28px rgba(90,60,0,.28);text-decoration:none">
                🏠 Kembali ke Home
            </a>
        </div>
    @endif
@endauth
