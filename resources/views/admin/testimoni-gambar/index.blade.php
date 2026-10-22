<x-admin-layout>
    <div class="admin-topbar">
        <div>
            <h1 class="kidza-h2">🖼️ Gambar Testimoni</h1>
            <p style="color:#4b5563">Galeri screenshot testimoni WhatsApp. Upload sekali di sini, lalu tinggal pilih saat membuat iklan.</p>
        </div>
    </div>

    @if(session('sukses'))
        <div class="kidza-card p-4 mb-4" style="border-color:#167941;color:#167941;font-weight:700">{{ session('sukses') }}</div>
    @endif
    @error('gambar')
        <div class="kidza-card p-4 mb-4" style="border-color:#e74c3c;color:#e74c3c;font-weight:700">{{ $message }}</div>
    @enderror

    <div class="kidza-card p-5 mb-6">
        <h2 class="font-bold mb-2" style="font-family:'Quicksand'">📤 Upload Gambar Baru</h2>
        <form method="POST" action="{{ route('admin.testimoni-gambar.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="gambar[]" accept="image/*" multiple required
                   class="w-full border-2 border-gray-300 rounded-xl px-3 py-2 text-sm mb-2">
            <p class="text-xs mb-3" style="color:#6b7280">Bisa pilih banyak sekaligus (maks 20 file). JPG/PNG, maks 2MB per file.</p>
            @error('gambar.*')<p class="text-xs font-bold mb-2" style="color:#e74c3c">{{ $message }}</p>@enderror
            <button type="submit" class="kidza-btn kidza-btn-green">Upload ke Galeri</button>
        </form>
    </div>

    <div class="kidza-card p-5">
        <h2 class="font-bold mb-3" style="font-family:'Quicksand'">Galeri ({{ $gambars->count() }} gambar)</h2>
        @if($gambars->isEmpty())
            <p class="text-sm" style="color:#6b7280">Belum ada gambar testimoni. Upload yang pertama lewat form di atas.</p>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($gambars as $g)
                    <div class="border-2 rounded-xl overflow-hidden" style="border-color:#e5e7eb">
                        <img src="{{ $g->url() }}" alt="Testimoni" class="w-full" style="height:180px;object-fit:cover;object-position:top">
                        <div class="p-3 flex items-center justify-between gap-2">
                            <span class="text-xs" style="color:#6b7280">
                                {{ $g->created_at->format('d M Y') }}<br>
                                @if(($pemakaian[$g->id] ?? 0) > 0)
                                    <span class="font-bold" style="color:#167941">Dipakai di {{ $pemakaian[$g->id] }} iklan</span>
                                @else
                                    Belum dipakai
                                @endif
                            </span>
                            <form method="POST" action="{{ route('admin.testimoni-gambar.destroy', $g) }}"
                                  onsubmit="return tanyaForm(this, 'Hapus gambar ini dari galeri?', '🗑️')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="kidza-btn kidza-btn-red" style="padding:6px 12px;font-size:12px">🗑️ Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="text-xs mt-4" style="color:#6b7280">Gambar yang sedang dipakai iklan tidak bisa dihapus — lepaskan dulu pilihannya di form iklan terkait.</p>
        @endif
    </div>
</x-admin-layout>
