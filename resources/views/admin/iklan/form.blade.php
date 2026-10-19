<x-admin-layout>
    <a href="{{ route('admin.iklan.index') }}" class="kidza-link text-sm mb-4 inline-block">← Kembali</a>
    <h1 class="kidza-h2 mb-6">{{ $kampanye->exists ? 'Edit' : 'Buat' }} Iklan Promo</h1>

    <form method="POST" action="{{ $kampanye->exists ? route('admin.iklan.update', $kampanye) : route('admin.iklan.store') }}"
          class="kidza-card p-6" style="max-width:44rem" enctype="multipart/form-data">
        @csrf
        @if($kampanye->exists) @method('PUT') @endif

        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-sm font-bold block mb-1">Judul Promo</label>
                <input type="text" name="judul" value="{{ old('judul', $kampanye->judul) }}" required maxlength="120"
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2" placeholder="mis. Promo Kemerdekaan">
                @error('judul')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-sm font-bold block mb-1">Slug (kosongkan = otomatis)</label>
                <input type="text" name="slug" value="{{ old('slug', $kampanye->slug) }}" maxlength="120"
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2" placeholder="promo-kemerdekaan">
                @error('slug')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">Sub-judul <span class="font-normal" style="color:#6b7280">(ajakan untuk orang tua, mis. "Parents, pernah bingung...?")</span></label>
            <input type="text" name="subjudul" value="{{ old('subjudul', $kampanye->subjudul) }}" maxlength="200"
                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
        </div>

        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">Deskripsi</label>
            <textarea name="deskripsi" rows="3" maxlength="1000"
                      class="w-full border-2 border-gray-300 rounded-xl px-4 py-2"
                      placeholder="Ceritakan promonya secara singkat...">{{ old('deskripsi', $kampanye->deskripsi) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">Gambar Banner <span class="font-normal" style="color:#6b7280">(JPG/PNG, maks 2MB — mis. desain promo + manfaat)</span></label>
            @if($kampanye->gambarUrl())
                <img src="{{ $kampanye->gambarUrl() }}" alt="Banner" class="rounded-xl mb-2" style="max-height:140px">
            @endif
            <input type="file" name="gambar" accept="image/*" class="w-full border-2 border-gray-300 rounded-xl px-3 py-2 text-sm">
            @error('gambar')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">📱 Screenshot Testimoni WhatsApp <span class="font-normal" style="color:#6b7280">(wajib 3–6 gambar, JPG/PNG maks 2MB per file)</span></label>
            @if(!empty($kampanye->testimoni_ss))
                <div class="flex flex-wrap gap-2 mb-2">
                    @foreach($kampanye->testimoni_ss as $i => $ss)
                        <label class="relative inline-block cursor-pointer" title="Centang untuk hapus">
                            <img src="{{ asset('storage/'.$ss) }}" alt="Testimoni {{ $i+1 }}" class="rounded-lg" style="height:90px;border:2px solid #e5e7eb">
                            <span class="absolute top-0 right-0 bg-white rounded-bl-lg px-1 text-xs">
                                <input type="checkbox" name="hapus_ss[]" value="{{ $i }}"> 🗑️
                            </span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs mb-2" style="color:#6b7280">Centang 🗑️ untuk menghapus gambar yang sudah ada.</p>
            @endif
            <input type="file" name="testimoni_ss[]" accept="image/*" multiple class="w-full border-2 border-gray-300 rounded-xl px-3 py-2 text-sm">
            @error('testimoni_ss')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
            @error('testimoni_ss.*')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">Video <span class="font-normal" style="color:#6b7280">(opsional — link YouTube/Instagram)</span></label>
            <input type="url" name="video_url" value="{{ old('video_url', $kampanye->video_url) }}" maxlength="255"
                   class="w-full border-2 border-gray-300 rounded-xl px-4 py-2" placeholder="https://youtube.com/watch?v=...">
            @error('video_url')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">Kalimat Ajakan <span class="font-normal" style="color:#6b7280">(tampil di bawah video)</span></label>
            <textarea name="ajakan" rows="2" maxlength="500"
                      class="w-full border-2 border-gray-300 rounded-xl px-4 py-2"
                      placeholder="mis. Jangan sampai kehabisan, amankan sekarang!">{{ old('ajakan', $kampanye->ajakan) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">Materi Ebook <span class="font-normal" style="color:#6b7280">(tampil dalam card — bisa tambah sebanyaknya)</span></label>
            <div id="daftar-materi" class="space-y-2">
                @php $materis = old('materi', $kampanye->materi ?? []); @endphp
                @forelse((array) $materis as $m)
                    <div class="flex gap-2 materi-row">
                        <input type="text" name="materi[]" value="{{ $m }}" maxlength="120"
                               class="flex-1 border-2 border-gray-300 rounded-xl px-4 py-2" placeholder="Judul materi...">
                        <button type="button" class="hapus-materi kidza-btn px-3" style="background:#e74c3c" title="Hapus">✕</button>
                    </div>
                @empty
                    <div class="flex gap-2 materi-row">
                        <input type="text" name="materi[]" maxlength="120"
                               class="flex-1 border-2 border-gray-300 rounded-xl px-4 py-2" placeholder="Judul materi...">
                        <button type="button" class="hapus-materi kidza-btn px-3" style="background:#e74c3c" title="Hapus">✕</button>
                    </div>
                @endforelse
            </div>
            <button type="button" id="tambah-materi" class="kidza-btn mt-2 text-sm" style="background:#F5B301;color:#14532D">+ Tambah Materi</button>
        </div>

        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">Produk yang tampil <span style="color:#6b7280">(pilih 1–4)</span>
                <span id="hitung-produk" class="kidza-badge ml-2" style="background:#167941">0 dipilih</span>
            </label>
            <div class="border-2 border-gray-300 rounded-xl p-4 grid md:grid-cols-2 gap-2" style="max-height:16rem;overflow-y:auto">
                @php
                    $dipilih = old('produk_ids', $kampanye->products->pluck('id')->toArray());
                    $tipeLabel = ['ebook' => '📚 Ebook', 'miniapp' => '🎮 MiniApp', 'bundle' => '🎁 Bundle'];
                @endphp
                @foreach($produks->groupBy('tipe') as $tipe => $daftar)
                    <p class="md:col-span-2 text-xs font-bold mt-1" style="color:#6b7280">{{ $tipeLabel[$tipe] ?? $tipe }}</p>
                    @foreach($daftar as $p)
                        <label class="flex items-center gap-2 text-sm border rounded-xl px-3 py-2 cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" name="produk_ids[]" value="{{ $p->id }}"
                                   @checked(in_array($p->id, (array) $dipilih)) class="cek-produk w-4 h-4">
                            <span class="font-bold">{{ $p->judul }}</span>
                            <span class="ml-auto whitespace-nowrap" style="color:#6b7280">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                        </label>
                    @endforeach
                @endforeach
            </div>
            @error('produk_ids')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
        </div>

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="text-sm font-bold block mb-1">Mulai (opsional)</label>
                <input type="datetime-local" name="mulai_at"
                       value="{{ old('mulai_at', $kampanye->mulai_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
            </div>
            <div>
                <label class="text-sm font-bold block mb-1">Berakhir</label>
                <input type="datetime-local" name="berakhir_at" required
                       value="{{ old('berakhir_at', $kampanye->berakhir_at?->format('Y-m-d\TH:i')) }}"
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
                @error('berakhir_at')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-sm font-bold block mb-1">Status</label>
                <select name="status" class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
                    <option value="draft" @selected(old('status', $kampanye->status ?? 'draft') === 'draft')>Draft</option>
                    <option value="aktif" @selected(old('status', $kampanye->status) === 'aktif')>Aktif</option>
                </select>
            </div>
        </div>

        <button type="submit" class="kidza-btn kidza-btn-green">💾 Simpan Iklan</button>
    </form>

    <script>
    (function () {
        const boxes = document.querySelectorAll('.cek-produk');
        const label = document.getElementById('hitung-produk');
        function hitung() {
            const n = document.querySelectorAll('.cek-produk:checked').length;
            label.textContent = n + ' dipilih';
            label.style.background = n > 4 ? '#e74c3c' : '#167941';
            boxes.forEach(b => { b.disabled = !b.checked && n >= 4; });
        }
        boxes.forEach(b => b.addEventListener('change', hitung));
        hitung();
    })();
    // Tambah/hapus baris materi dinamis
    (function () {
        const daftar = document.getElementById('daftar-materi');
        document.getElementById('tambah-materi').addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'flex gap-2 materi-row';
            row.innerHTML = `<input type="text" name="materi[]" maxlength="120"
                class="flex-1 border-2 border-gray-300 rounded-xl px-4 py-2" placeholder="Judul materi...">
                <button type="button" class="hapus-materi kidza-btn px-3" style="background:#e74c3c" title="Hapus">✕</button>`;
            daftar.appendChild(row);
        });
        daftar.addEventListener('click', (e) => {
            if (e.target.classList.contains('hapus-materi')) {
                e.target.closest('.materi-row').remove();
            }
        });
    })();
    </script>
</x-admin-layout>
