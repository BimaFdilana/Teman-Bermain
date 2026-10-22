<x-admin-layout>
    <h1 class="kidza-h2 mb-2">Pengaturan</h1>
    <p class="mb-6" style="color:#4b5563">QR QRIS & kontak yang tampil di web.</p>

    <form method="POST" action="{{ route('admin.pengaturan.update') }}" enctype="multipart/form-data"
          class="kidza-card p-6" style="max-width:36rem">
        @csrf

        <h2 class="font-bold text-lg mb-3" style="font-family:'Quicksand'">QR Code QRIS</h2>
        @if($qrisImage)
            <img src="{{ asset('storage/' . $qrisImage) }}" alt="QR QRIS" class="rounded-2xl border-2 border-gray-200 mb-3" style="max-width:220px">
        @else
            <p class="text-sm mb-3" style="color:#6b7280">Belum ada QR terupload — halaman bayar akan menampilkan placeholder.</p>
        @endif
        <input type="file" name="qris" accept=".jpg,.jpeg,.png"
               class="w-full border-2 border-dashed border-gray-300 rounded-2xl px-4 py-4 mb-1">
        <p class="text-xs mb-6" style="color:#6b7280">JPG/PNG maks 2 MB. QR lama otomatis diganti.</p>
        @error('qris')<p class="text-xs font-bold mb-3" style="color:#e74c3c">{{ $message }}</p>@enderror

        <h2 class="font-bold text-lg mb-3" style="font-family:'Quicksand'">Kontak</h2>
        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">No. WhatsApp Admin</label>
            <input type="text" name="wa_admin" value="{{ old('wa_admin', $waAdmin) }}" placeholder="081234567890"
                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
        </div>
        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">Jam Operasional</label>
            <input type="text" name="jam_operasional" value="{{ old('jam_operasional', $jamOperasional) }}"
                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
        </div>
        <div class="mb-5">
            <label class="text-sm font-bold block mb-1">Alamat</label>
            <input type="text" name="alamat" value="{{ old('alamat', $alamat) }}"
                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
        </div>

        <h2 class="font-bold text-lg mb-3" style="font-family:'Quicksand'">💳 Virtual Account (BR-RC-23)</h2>
        <p class="text-xs mb-3" style="color:#6b7280">Isi nomor VA per bank. Bank yang kosong tidak tampil di checkout.</p>
        @foreach(['BCA' => 'BCA', 'BRI' => 'BRI', 'MANDIRI' => 'Mandiri', 'BNI' => 'BNI', 'SEABANK' => 'SeaBank'] as $kode => $nama)
            <div class="mb-3">
                <label class="text-sm font-bold block mb-1">VA {{ $nama }}</label>
                <input type="text" name="va_numbers[{{ $kode }}]" value="{{ old('va_numbers.'.$kode, $vaNumbers[$kode] ?? '') }}"
                       placeholder="Nomor VA {{ $nama }}" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
            </div>
        @endforeach

        <h2 class="font-bold text-lg mb-3" style="font-family:'Quicksand'">🧾 Pajak (BR-RC-22)</h2>
        <div class="mb-5">
            <label class="text-sm font-bold block mb-1">Pajak PPN (%)</label>
            <input type="number" name="pajak_persen" value="{{ old('pajak_persen', $pajakPersen) }}" min="0" max="100" step="0.5"
                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
            <p class="text-xs mt-1" style="color:#6b7280">0 = tidak ada pajak. Tampil di ringkasan cart bila &gt; 0.</p>
        </div>

        <button type="submit" class="kidza-btn kidza-btn-green w-full">Simpan Pengaturan</button>
    </form>

    <div class="kidza-card p-6 mt-6" style="max-width:36rem">
        <h2 class="font-bold text-lg mb-2" style="font-family:'Quicksand'">🖼️ Logo Slider (Landing Page)</h2>
        <p class="text-sm mb-4" style="color:#4b5563">Logo yang tampil berjalan di Landing Page, di bawah section testimoni "Kata Mereka" — isi dengan logo usaha kamu dan logo usaha pendukung. Bila kosong, slider menampilkan teks seperti sekarang.</p>

        @if(!empty($logoSlider))
            <div class="flex flex-wrap gap-3 mb-5">
                @foreach($logoSlider as $i => $path)
                    <div class="relative border-2 rounded-xl p-2" style="border-color:#e5e7eb;background:#FFFDF0">
                        <img src="{{ asset('storage/'.$path) }}" alt="Logo {{ $i + 1 }}" style="height:56px;width:auto;max-width:140px;object-fit:contain">
                        <form method="POST" action="{{ route('admin.pengaturan.logo-slider.destroy', $i) }}"
                              onsubmit="return tanyaForm(this, 'Hapus logo ini dari slider?', '🗑️')" class="absolute -top-2 -right-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Hapus logo"
                                    style="background:#e74c3c;color:#fff;border:2px solid #1a1a1a;border-radius:999px;width:24px;height:24px;font-size:12px;line-height:1;cursor:pointer">✕</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm mb-4" style="color:#6b7280">Belum ada logo terupload.</p>
        @endif

        <form method="POST" action="{{ route('admin.pengaturan.logo-slider.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="logo[]" accept=".jpg,.jpeg,.png,.webp,.svg" multiple
                   class="w-full border-2 border-dashed border-gray-300 rounded-2xl px-4 py-4 mb-1">
            <p class="text-xs mb-4" style="color:#6b7280">Bisa pilih banyak sekaligus (maks 10). PNG transparan disarankan. JPG/PNG/WebP/SVG maks 2 MB per file.</p>
            @error('logo')<p class="text-xs font-bold mb-3" style="color:#e74c3c">{{ $message }}</p>@enderror
            @error('logo.*')<p class="text-xs font-bold mb-3" style="color:#e74c3c">{{ $message }}</p>@enderror
            <button type="submit" class="kidza-btn kidza-btn-green w-full">Upload Logo</button>
        </form>
    </div>
</x-admin-layout>
