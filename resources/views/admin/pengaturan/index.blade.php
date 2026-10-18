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

        <button type="submit" class="kidza-btn kidza-btn-green w-full">Simpan Pengaturan</button>
    </form>
</x-admin-layout>
