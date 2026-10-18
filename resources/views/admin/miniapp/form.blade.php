<x-admin-layout>
<div class="mb-5">
    <a href="{{ route('admin.miniapp.index') }}" class="text-sm font-bold underline">← Kembali</a>
    <h1 class="text-2xl font-bold mt-1" style="font-family:'Quicksand'">{{ $product->exists ? '✏️ Edit MiniApp' : '➕ Tambah MiniApp' }}</h1>
    <p class="text-sm" style="color:#6b7280">Harga & stok diatur di Manage Product.</p>
</div>

<style>
    .miniapp-form-wrap { display: flex; flex-direction: column; gap: 1.5rem; align-items: flex-start; }
    .miniapp-rules { width: 100%; }
    .miniapp-rules ol { margin: 0; padding-left: 1.2rem; }
    .miniapp-rules li { margin-bottom: .7rem; font-size: .85rem; line-height: 1.55; color: #444; }
    .miniapp-rules li::marker { font-weight: 800; color: #1E7B34; }
    @media (min-width: 1024px) {
        .miniapp-form-wrap { flex-direction: row; }
        .miniapp-form-card { flex: 1; min-width: 0; }
        .miniapp-rules { max-width: 22rem; flex-shrink: 0; position: sticky; top: 1.5rem; }
    }
</style>

<div class="miniapp-form-wrap">
<div class="kidza-card p-6 w-full miniapp-form-card" style="max-width:42rem">
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

<aside class="kidza-card p-6 miniapp-rules">
    <h2 class="font-bold mb-4" style="font-family:'Quicksand';color:#14532D">📋 Aturan Pengisian</h2>
    <ol>
        <li><strong>Judul MiniApp</strong> — nama yang tampil di katalog. Buat jelas & menarik untuk orang tua.</li>
        <li><strong>Nama Apps (PWA)</strong> — maks 50 karakter. Nama yang muncul di layar HP setelah dipasang; singkat & mudah diingat.</li>
        <li><strong>Deskripsi Singkat</strong> — 1–2 kalimat: isi permainan + manfaatnya untuk anak.</li>
        <li><strong>Cover</strong> — JPG/PNG maks 2MB, gambar mendatar (landscape) tampil paling bagus.</li>
        <li><strong>Icon/Logo</strong> — PNG <strong>kotak (1:1)</strong> maks 1MB. Jadi ikon aplikasi di HP — hindari teks kecil.</li>
        <li><strong>File HTML</strong> — satu file <code>.html</code> mandiri maks 5MB:
            <br>• CSS & JavaScript harus <strong>inline</strong> di dalam file
            <br>• ❌ Dilarang <code>&lt;script src&gt;</code> eksternal (Google Fonts via <code>&lt;link&gt;</code> boleh)
            <br>• Gambar harus <strong>data-URI</strong>, bukan path file lokal
            <br>• Harus dokumen HTML valid (ada tag <code>&lt;html&gt;</code>)</li>
        <li><strong>Versi</strong> — naikkan setiap update (mis. 1.0.0 → 1.0.1) agar HP pengguna memuat file baru.</li>
        <li><strong>Status</strong> — <em>Draft</em>: belum tampil · <em>Tersedia</em>: tampil & bisa dibeli · <em>Arsip</em>: disembunyikan.</li>
    </ol>
    <div class="mt-4 p-3 rounded-xl text-xs" style="background:#FFF8E7;line-height:1.6">
        💡 <strong>Upload gagal</strong> ("failed to upload")? Biasanya file melebihi batas upload server (2MB). Naikkan di cPanel → <em>Select PHP Version</em> → <em>Options</em> → <code>upload_max_filesize</code>.
    </div>
</aside>
</div>
</x-admin-layout>
