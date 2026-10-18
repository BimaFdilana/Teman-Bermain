<x-admin-layout>
<div class="mb-5">
    <a href="{{ route('admin.produk.index', ['tab' => 'bundle']) }}" class="text-sm font-bold underline">← Kembali</a>
    <h1 class="text-2xl font-bold mt-1" style="font-family:'Quicksand'">{{ $bundle->exists ? '✏️ Edit Bundle' : '🎁 Buat Bundle' }}</h1>
</div>

<div class="kidza-card p-6 max-w-2xl">
    <form method="POST" action="{{ $bundle->exists ? route('admin.produk.bundle.update', $bundle) : route('admin.produk.bundle.store') }}" enctype="multipart/form-data">
        @csrf
        @if($bundle->exists) @method('PUT') @endif

        <x-form-input label="Nama Bundle" name="judul" :value="$bundle->judul" required />
        <x-form-input label="Deskripsi" name="deskripsi" type="textarea" :value="$bundle->deskripsi" attrs="rows=3" />

        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">Cover Bundle <span class="font-normal" style="color:#6b7280">(JPG/PNG, maks 2MB)</span></label>
            @if($bundle->coverUrl())
                <img src="{{ $bundle->coverUrl() }}" alt="Cover" class="rounded-xl mb-2" style="max-height:120px">
            @endif
            <input type="file" name="cover" accept="image/*" class="w-full border-2 border-gray-300 rounded-xl px-3 py-2 text-sm">
            @error('cover')<p class="text-xs mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="text-sm font-bold block mb-2">Isi Bundle <span style="color:#e74c3c">*</span> <span class="font-normal" style="color:#6b7280">(minimal 2 produk)</span></label>
            @error('items')<p class="text-xs mb-2" style="color:#e74c3c">{{ $message }}</p>@enderror
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-64 overflow-y-auto border-2 border-gray-200 rounded-xl p-3">
                @forelse($kandidat as $k)
                    <label class="flex items-center gap-2 text-sm border rounded-lg px-3 py-2 cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="items[]" value="{{ $k->id }}"
                            {{ in_array($k->id, old('items', $terpilih)) ? 'checked' : '' }} class="w-4 h-4">
                        <span>{{ $k->judul }} <span style="color:#6b7280">({{ $k->tipe }})</span></span>
                    </label>
                @empty
                    <p class="text-sm" style="color:#6b7280">Belum ada produk tersedia — tambahkan Ebook/MiniApp dulu.</p>
                @endforelse
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-form-input label="Harga Bundle (Rp)" name="harga" type="number" :value="$bundle->harga" required attrs="min=0" />
            <x-form-input label="Harga Coret (Rp)" name="harga_coret" type="number" :value="$bundle->harga_coret" attrs="min=0" />
        </div>
        <x-form-input label="Stok (kosongkan = tidak terbatas)" name="stok" type="number" :value="$bundle->stok" attrs="min=0" />
        <x-form-input label="Status" name="status" type="select" required>
            @foreach(['draft' => 'Draft', 'tersedia' => 'Tersedia', 'arsip' => 'Arsip'] as $v => $l)
                <option value="{{ $v }}" {{ old('status', $bundle->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </x-form-input>

        <button type="submit" class="kidza-btn kidza-btn-green">💾 Simpan Bundle</button>
    </form>
</div>
</x-admin-layout>
