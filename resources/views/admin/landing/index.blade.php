<x-admin-layout>
<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-bold" style="font-family:'Quicksand'">🌐 Manage Landing Page</h1>
    <a href="{{ route('landing') }}" target="_blank" class="kidza-btn kidza-btn-blue">👁️ Preview</a>
</div>

@if(session('sukses'))
    <div class="kidza-card p-3 mb-4" style="background:#e8f8f0"><p class="text-sm font-bold" style="color:#167941">{{ session('sukses') }}</p></div>
@endif

{{-- Hero + Kontak + Footer --}}
<div class="kidza-card p-6 mb-6">
    <h2 class="font-bold text-lg mb-4" style="font-family:'Quicksand'">✨ Teks Hero, Kontak & Footer</h2>
    <form method="POST" action="{{ route('admin.landing.teks') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form-input label="Headline Hero" name="hero_headline" :value="\App\Models\Setting::get('hero_headline')" required />
            <x-form-input label="Subheadline Hero" name="hero_subheadline" type="textarea" :value="\App\Models\Setting::get('hero_subheadline')" attrs="rows=2" />
        </div>
        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">Gambar Hero (Ebook)</label>
            @if(\App\Models\Setting::get('hero_image'))
                <img src="{{ asset('storage/'.\App\Models\Setting::get('hero_image')) }}" alt="Hero saat ini" class="h-32 rounded-xl mb-2 border-2 border-gray-200">
            @endif
            <input type="file" name="hero_image" accept="image/*"
                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
            @error('hero_image')<p class="text-xs mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
            <p class="text-xs mt-1" style="color:#6b7280">JPG/PNG maks 2MB. Kosongkan bila tidak ingin mengganti.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form-input label="No WA Admin" name="wa_admin" :value="\App\Models\Setting::get('wa_admin')" required help="Format 62..., dipakai tombol kontak & kirim kode." />
            <x-form-input label="Jam Operasional" name="jam_operasional" :value="\App\Models\Setting::get('jam_operasional')" />
            <x-form-input label="Alamat" name="alamat" type="textarea" :value="\App\Models\Setting::get('alamat')" attrs="rows=2" />
            <x-form-input label="Tagline Footer" name="footer_tagline" type="textarea" :value="\App\Models\Setting::get('footer_tagline')" attrs="rows=2" />
        </div>
        <button type="submit" class="kidza-btn kidza-btn-green">💾 Simpan Teks</button>
    </form>
</div>

{{-- Foto background --}}
<div class="kidza-card p-6 mb-6">
    <h2 class="font-bold text-lg mb-2" style="font-family:'Quicksand'">🖼️ Foto Background</h2>
    <p class="text-sm mb-4" style="color:#6b7280">Ganti foto latar section Misi Kami, Footer, dan Kata Mereka. JPG/PNG maks 2MB per foto.</p>
    <form method="POST" action="{{ route('admin.landing.foto') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            @php
            $fotos = [
                'misi_bg' => ['label' => 'Background Misi Kami', 'default' => 'landing/galeri-1.jpg'],
                'footer_bg' => ['label' => 'Background Footer', 'default' => 'landing/galeri-2.jpg'],
                'testimoni_bg' => ['label' => 'Background Kata Mereka', 'default' => 'landing/testimoni-bg.jpg'],
            ];
            @endphp
            @foreach($fotos as $key => $f)
                <div class="border-2 border-gray-200 rounded-xl p-3">
                    <p class="text-sm font-bold mb-2">{{ $f['label'] }}</p>
                    <img src="{{ asset('storage/'.\App\Models\Setting::get($key, $f['default'])) }}" alt="{{ $f['label'] }}" class="w-full h-28 object-cover rounded-lg mb-2">
                    <input type="file" name="{{ $key }}" accept="image/*"
                           class="w-full border-2 border-gray-300 rounded-xl px-3 py-1.5 text-sm">
                    @error($key)<p class="text-xs mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
        <button type="submit" class="kidza-btn kidza-btn-green">💾 Simpan Foto</button>
    </form>
</div>

{{-- Paket unggulan --}}
<div class="kidza-card p-6 mb-6">
    <h2 class="font-bold text-lg mb-2" style="font-family:'Quicksand'">🎁 Paket Unggulan <span class="text-sm font-normal" style="color:#6b7280">(maks 3)</span></h2>
    <form method="POST" action="{{ route('admin.landing.paket') }}">
        @csrf
        @error('paket')<p class="text-xs mb-2" style="color:#e74c3c">{{ $message }}</p>@enderror
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-4">
            @forelse($bundles as $b)
                <label class="flex items-center gap-2 text-sm border-2 rounded-xl px-3 py-2 cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="paket[]" value="{{ $b->id }}"
                        {{ in_array($b->id, old('paket', $paketTerpilih)) ? 'checked' : '' }} class="w-4 h-4">
                    <span class="font-bold">{{ $b->judul }}</span>
                </label>
            @empty
                <p class="text-sm" style="color:#6b7280">Belum ada bundle tersedia.</p>
            @endforelse
        </div>
        <button type="submit" class="kidza-btn kidza-btn-green">💾 Simpan Pilihan</button>
    </form>
</div>

{{-- Testimoni --}}
<div class="kidza-card p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-lg" style="font-family:'Quicksand'">💬 Testimoni</h2>
        <button class="kidza-btn kidza-btn-green" onclick="document.getElementById('testimoni-baru').style.display='flex'">+ Tambah</button>
    </div>

    @if($testimonials->isEmpty())
        <x-empty-state pesan="Belum ada testimoni." />
    @else
        <x-data-table>
            <x-slot:head>
                <th class="text-left px-4 py-2">Nama</th>
                <th class="text-left px-4 py-2">Isi</th>
                <th class="text-left px-4 py-2">Aktif</th>
                <th class="text-left px-4 py-2">Aksi</th>
            </x-slot:head>
            @foreach($testimonials as $t)
                <tr class="border-t">
                    <td class="px-4 py-2 font-bold">{{ $t->nama }}<br><span class="text-xs font-normal" style="color:#6b7280">{{ $t->peran }}</span></td>
                    <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($t->isi, 80) }}</td>
                    <td class="px-4 py-2">{{ $t->aktif ? '✅' : '❌' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <button class="kidza-btn" style="background:#fff" onclick="document.getElementById('testimoni-{{ $t->id }}').style.display='flex'">✏️</button>
                        <form method="POST" action="{{ route('admin.landing.testimoni.destroy', $t) }}" class="inline" onsubmit="return confirm('Pindah ke sampah? Bisa dikembalikan 30 hari.')">
                            @csrf @method('DELETE')
                            <button class="kidza-btn" style="background:#fdecea">🗑️</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    @endif

    @if($sampahTestimoni->isNotEmpty())
        <div class="mt-4 p-4 rounded-xl" style="background:#FFF9EC;border:2px dashed #d9c58a">
            <h3 class="font-bold mb-2" style="font-family:'Quicksand'">🗑️ Sampah — terhapus permanen otomatis setelah 30 hari</h3>
            @foreach($sampahTestimoni as $t)
                <div class="flex items-center justify-between py-2" style="border-bottom:1px dashed #e5d9b8">
                    <span class="text-sm"><strong>{{ $t->nama }}</strong> <span style="color:#6b7280">({{ $t->deleted_at->format('d M Y') }})</span></span>
                    <span class="whitespace-nowrap">
                        <form method="POST" action="{{ route('admin.landing.testimoni.restore', $t->id) }}" class="inline">
                            @csrf
                            <button class="kidza-btn kidza-btn-green" style="padding:.3rem .8rem;font-size:.8rem">↩️ Kembalikan</button>
                        </form>
                        <form method="POST" action="{{ route('admin.landing.testimoni.force', $t->id) }}" class="inline" onsubmit="return confirm('Hapus permanen? Tidak bisa dikembalikan.')">
                            @csrf @method('DELETE')
                            <button class="kidza-btn" style="background:#fdecea;padding:.3rem .8rem;font-size:.8rem">Hapus permanen</button>
                        </form>
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Modal tambah testimoni --}}
<x-modal id="testimoni-baru" judul="💬 Tambah Testimoni">
    <form method="POST" action="{{ route('admin.landing.testimoni.store') }}" enctype="multipart/form-data">
        @csrf
        <x-form-input label="Nama" name="nama" required />
        <x-form-input label="Peran (mis. Orang tua murid)" name="peran" />
        <x-form-input label="Isi Testimoni" name="isi" type="textarea" required attrs="rows=3" />
        <x-form-input label="Foto (opsional)" name="foto" type="file" help="JPG/PNG maks 1MB." />
        <label class="flex items-center gap-2 text-sm mb-4"><input type="checkbox" name="aktif" value="1" checked class="w-4 h-4"> Tampilkan di landing</label>
        <button type="submit" class="kidza-btn kidza-btn-green">💾 Simpan</button>
    </form>
</x-modal>

{{-- Modal edit testimoni --}}
@foreach($testimonials as $t)
<x-modal id="testimoni-{{ $t->id }}" judul="✏️ Edit Testimoni">
    <form method="POST" action="{{ route('admin.landing.testimoni.update', $t) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <x-form-input label="Nama" name="nama" :value="$t->nama" required />
        <x-form-input label="Peran" name="peran" :value="$t->peran" />
        <x-form-input label="Isi Testimoni" name="isi" type="textarea" required attrs="rows=3" :value="$t->isi" />
        <x-form-input label="Foto (ganti)" name="foto" type="file" help="JPG/PNG maks 1MB." />
        <x-form-input label="Urutan tampil" name="urutan" type="number" :value="$t->urutan" attrs="min=0" />
        <label class="flex items-center gap-2 text-sm mb-4"><input type="checkbox" name="aktif" value="1" {{ $t->aktif ? 'checked' : '' }} class="w-4 h-4"> Tampilkan di landing</label>
        <button type="submit" class="kidza-btn kidza-btn-green">💾 Simpan</button>
    </form>
</x-modal>
@endforeach
</x-admin-layout>
