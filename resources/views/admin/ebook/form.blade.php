<x-admin-layout>
<div class="mb-5">
    <a href="{{ route('admin.ebook.index') }}" class="text-sm font-bold underline">← Kembali</a>
    <h1 class="text-2xl font-bold mt-1" style="font-family:'Quicksand'">{{ $product->exists ? '✏️ Edit Ebook' : '➕ Tambah Ebook' }}</h1>
    <p class="text-sm" style="color:#6b7280">Harga & stok diatur di Manage Product.</p>
</div>

<div class="kidza-card p-6 max-w-2xl">
    <form method="POST" action="{{ $product->exists ? route('admin.ebook.update', $product) : route('admin.ebook.store') }}" enctype="multipart/form-data">
        @csrf
        @if($product->exists) @method('PUT') @endif

        <x-form-input label="Judul Ebook" name="judul" :value="$product->judul" required />
        <x-form-input label="Deskripsi Singkat" name="deskripsi" type="textarea" :value="$product->deskripsi" attrs="rows=3" />
        <x-form-input label="Cover Ebook (gambar)" name="cover" type="file" help="JPG/PNG maks 2MB." />
        <x-form-input label="File PDF" name="pdf" type="file" :required="!$product->exists" help="PDF maks 10MB.{{ $product->ebook?->pdf_path ? ' Sudah ada file — upload untuk mengganti.' : '' }}" />
        <x-form-input label="Jumlah Halaman" name="jumlah_halaman" type="number" :value="$product->ebook?->jumlah_halaman" attrs="min=1" />
        <x-form-input label="Status" name="status" type="select" required>
            @foreach(['draft' => 'Draft (pending)', 'tersedia' => 'Tersedia', 'arsip' => 'Arsip'] as $v => $l)
                <option value="{{ $v }}" {{ old('status', $product->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </x-form-input>

        <button type="submit" class="kidza-btn kidza-btn-green">💾 Simpan</button>
    </form>
</div>
</x-admin-layout>
