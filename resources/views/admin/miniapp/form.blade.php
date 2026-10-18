<x-admin-layout>
<div class="mb-5">
    <a href="{{ route('admin.miniapp.index') }}" class="text-sm font-bold underline">← Kembali</a>
    <h1 class="text-2xl font-bold mt-1" style="font-family:'Quicksand'">{{ $product->exists ? '✏️ Edit MiniApp' : '➕ Tambah MiniApp' }}</h1>
    <p class="text-sm" style="color:#6b7280">Harga & stok diatur di Manage Product.</p>
</div>

<div class="kidza-card p-6 max-w-2xl">
    <form method="POST" action="{{ $product->exists ? route('admin.miniapp.update', $product) : route('admin.miniapp.store') }}" enctype="multipart/form-data">
        @csrf
        @if($product->exists) @method('PUT') @endif

        <x-form-input label="Judul MiniApp" name="judul" :value="$product->judul" required />
        <x-form-input label="Nama Apps (untuk PWA)" name="pwa_name" :value="$product->miniapp?->pwa_name" required help="Nama yang tampil saat dipasang di HP, maks 50 karakter." />
        <x-form-input label="Deskripsi Singkat" name="deskripsi" type="textarea" :value="$product->deskripsi" attrs="rows=3" />
        <x-form-input label="Cover" name="cover" type="file" help="JPG/PNG maks 2MB." />
        <x-form-input label="Icon/Logo Apps" name="icon" type="file" help="PNG kotak maks 1MB — dipakai sebagai ikon PWA." />
        <x-form-input label="File HTML MiniApp" name="html" type="file" :required="!$product->exists"
            help="Satu file .html mandiri maks 5MB. JS & CSS harus inline — <script src> eksternal dilarang (Google Fonts boleh). Gambar harus data-URI.{{ $product->miniapp?->html_path ? ' Sudah ada file — upload untuk mengganti.' : '' }}" />
        <x-form-input label="Versi" name="versi" :value="$product->miniapp?->versi ?? '1.0.0'" />
        <x-form-input label="Status" name="status" type="select" required>
            @foreach(['draft' => 'Draft (pending)', 'tersedia' => 'Tersedia', 'arsip' => 'Arsip'] as $v => $l)
                <option value="{{ $v }}" {{ old('status', $product->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </x-form-input>

        <button type="submit" class="kidza-btn kidza-btn-green">💾 Simpan</button>
    </form>
</div>
</x-admin-layout>
