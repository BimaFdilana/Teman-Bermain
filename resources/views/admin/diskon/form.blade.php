<x-admin-layout>
    <a href="{{ route('admin.diskon.index') }}" class="kidza-link text-sm mb-4 inline-block">← Kembali</a>
    <h1 class="kidza-h2 mb-6">{{ $diskon->exists ? 'Edit' : 'Buat' }} Kode Diskon</h1>

    <form method="POST" action="{{ $diskon->exists ? route('admin.diskon.update', $diskon) : route('admin.diskon.store') }}"
          class="kidza-card p-6" style="max-width:36rem">
        @csrf
        @if($diskon->exists) @method('PUT') @endif

        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-sm font-bold block mb-1">Kode</label>
                <input type="text" name="kode" value="{{ old('kode', $diskon->kode) }}" required
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 uppercase">
                @error('kode')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-sm font-bold block mb-1">Tipe</label>
                <select name="tipe" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
                    <option value="persen" @selected(old('tipe', $diskon->tipe) === 'persen')>Persen (%)</option>
                    <option value="nominal" @selected(old('tipe', $diskon->tipe) === 'nominal')>Nominal (Rp)</option>
                </select>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-sm font-bold block mb-1">Nilai</label>
                <input type="number" name="nilai" value="{{ old('nilai', $diskon->nilai) }}" min="1" required
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
            </div>
            <div>
                <label class="text-sm font-bold block mb-1">Kuota (kosongkan = tanpa batas)</label>
                <input type="number" name="kuota" value="{{ old('kuota', $diskon->kuota) }}" min="1"
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-sm font-bold block mb-1">Berlaku dari</label>
                <input type="date" name="mulai" value="{{ old('mulai', $diskon->mulai?->format('Y-m-d')) }}"
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
            </div>
            <div>
                <label class="text-sm font-bold block mb-1">Berlaku sampai</label>
                <input type="date" name="berakhir" value="{{ old('berakhir', $diskon->berakhir?->format('Y-m-d')) }}"
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
            </div>
        </div>

        <label class="inline-flex items-center gap-2 mb-5">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $diskon->aktif ?? true))>
            <span class="text-sm font-bold">Aktif</span>
        </label>

        <button type="submit" class="kidza-btn kidza-btn-green w-full">Simpan</button>
    </form>
</x-admin-layout>
