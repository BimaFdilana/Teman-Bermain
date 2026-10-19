<x-admin-layout>
    <a href="{{ route('admin.iklan.index') }}" class="kidza-link text-sm mb-4 inline-block">← Kembali</a>
    <h1 class="kidza-h2 mb-6">{{ $kampanye->exists ? 'Edit' : 'Buat' }} Iklan Promo</h1>

    <div class="flex flex-col xl:flex-row gap-6 items-start">
    <form method="POST" action="{{ $kampanye->exists ? route('admin.iklan.update', $kampanye) : route('admin.iklan.store') }}"
          class="kidza-card p-6 flex-1 w-full" style="max-width:44rem" enctype="multipart/form-data">
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

    {{-- Preview langsung (kanan) --}}
    <aside class="w-full xl:w-[400px] shrink-0 xl:sticky xl:top-6">
        <p class="text-sm font-bold mb-2" style="color:#6b7280">👁️ Preview Iklan <span class="font-normal">(update otomatis)</span></p>
        <div id="pv-phone" class="mx-auto bg-white rounded-[2rem] overflow-hidden" style="max-width:400px;border:3px solid #1a1a1a;box-shadow:6px 6px 0 #1a1a1a">
            <div class="text-center py-3" style="background:#FFFEF7;border-bottom:2px solid #F0E7CC">
                <span class="font-extrabold" style="font-family:'Baloo 2',cursive;color:#1E7B34">rumahcahaya-bks</span>
            </div>
            <div class="p-4 overflow-y-auto" style="max-height:70vh;background:#FFFEF7">
                <p class="text-center font-extrabold mb-1" style="color:#A63D2F;font-size:.8rem">🔥 PROMO SPESIAL 🔥</p>
                <h2 id="pv-judul" class="text-center font-extrabold mb-2" style="color:#14532D;font-size:1.3rem;line-height:1.3">Judul Promo</h2>
                <p id="pv-subjudul" class="text-center text-sm mb-3" style="color:#5B5344"></p>
                <img id="pv-banner" class="rounded-2xl w-full object-cover mb-3" style="display:none;aspect-ratio:16/9" alt="Banner">
                <div class="text-center mb-3">
                    <span class="inline-block px-4 py-1 rounded-full text-xs font-bold" style="background:#FDF0EC;color:#A63D2F;border:1.5px solid #E8C4BC">⏳ Berakhir dalam 2 hari 04:15:30</span>
                </div>
                <p id="pv-deskripsi" class="text-sm mb-4" style="color:#5B5344;line-height:1.6"></p>
                <div id="pv-produk" class="mb-4"></div>
                <div id="pv-video" class="mb-4" style="display:none">
                    <div class="rounded-2xl flex items-center justify-center text-4xl" style="aspect-ratio:16/9;background:#1a1a1a">▶️</div>
                </div>
                <div id="pv-ajakan-wrap" class="mb-4" style="display:none">
                    <p id="pv-ajakan" class="text-center text-sm font-bold italic px-4" style="color:#1E7B34"></p>
                </div>
                <div id="pv-materi-wrap" class="mb-4" style="display:none">
                    <p class="font-extrabold text-sm mb-2" style="color:#14532D">📖 Materi Ebook</p>
                    <ul id="pv-materi" class="text-sm space-y-1" style="color:#5B5344"></ul>
                </div>
                <div id="pv-ss-wrap" class="mb-4" style="display:none">
                    <p class="font-extrabold text-center text-sm mb-2" style="color:#14532D">💬 Kata Mereka</p>
                    <div id="pv-ss" class="flex gap-2 overflow-x-auto pb-2" style="scrollbar-width:none"></div>
                </div>
                <div class="text-center mt-4">
                    <span class="kidza-btn kidza-btn-green" style="font-size:.9rem;display:inline-block;pointer-events:none">👉 Pesan Sekarang 👈</span>
                </div>
            </div>
        </div>
    </aside>
    </div>
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
    // ===== Live preview iklan (panel kanan) =====
    (function () {
        const $ = (id) => document.getElementById(id);
        const form = document.querySelector('form');
        const produkData = @json($produks->mapWithKeys(fn($p) => [$p->id => ['judul' => $p->judul, 'harga' => $p->harga, 'harga_coret' => $p->harga_coret, 'cover' => $p->coverUrl()]]));

        const txt = (name, elId, def = '') => {
            const inp = form.querySelector(`[name="${name}"]`);
            const el = $(elId);
            const upd = () => { el.textContent = inp.value.trim() || def; el.style.display = inp.value.trim() || def ? '' : 'none'; };
            inp.addEventListener('input', upd); upd();
        };
        txt('judul', 'pv-judul', 'Judul Promo');
        txt('subjudul', 'pv-subjudul');
        txt('deskripsi', 'pv-deskripsi');
        txt('ajakan', 'pv-ajakan');

        // Banner: file baru (FileReader) atau gambar lama
        const bannerInp = form.querySelector('[name="gambar"]');
        const pvBanner = $('pv-banner');
        @if($kampanye->gambarUrl())
            pvBanner.src = @json($kampanye->gambarUrl());
            pvBanner.style.display = 'block';
        @endif
        bannerInp.addEventListener('change', () => {
            const f = bannerInp.files[0];
            if (!f) return;
            const r = new FileReader();
            r.onload = (e) => { pvBanner.src = e.target.result; pvBanner.style.display = 'block'; };
            r.readAsDataURL(f);
        });

        // Video: tampilkan placeholder bila ada URL
        const videoInp = form.querySelector('[name="video_url"]');
        const pvVideo = $('pv-video');
        const updVideo = () => { pvVideo.style.display = videoInp.value.trim() ? 'block' : 'none'; };
        videoInp.addEventListener('input', updVideo); updVideo();

        // Produk terpilih → card preview
        const pvProduk = $('pv-produk');
        const updProduk = () => {
            const ids = [...form.querySelectorAll('.cek-produk:checked')].map(c => c.value);
            pvProduk.innerHTML = ids.map(id => {
                const p = produkData[id];
                if (!p) return '';
                const rp = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
                return `<div class="rounded-2xl p-3 mb-2 bg-white" style="border:2px solid #F0E7CC">
                    ${p.cover ? `<img src="${p.cover}" class="rounded-xl w-full object-cover mb-2" style="aspect-ratio:16/9" alt="">` : ''}
                    <p class="font-extrabold text-sm" style="color:#14532D">${p.judul}</p>
                    <p class="text-sm"><b style="color:#1E7B34">${rp(p.harga)}</b>
                    ${p.harga_coret ? `<span class="line-through text-xs" style="color:#a39e93">${rp(p.harga_coret)}</span>` : ''}</p>
                </div>`;
            }).join('') || '<p class="text-xs text-center" style="color:#a39e93">— belum ada produk dipilih —</p>';
        };
        form.querySelectorAll('.cek-produk').forEach(c => c.addEventListener('change', updProduk));
        updProduk();

        // Materi dinamis → list preview
        const pvMateriWrap = $('pv-materi-wrap'), pvMateri = $('pv-materi');
        const updMateri = () => {
            const items = [...form.querySelectorAll('[name="materi[]"]')].map(i => i.value.trim()).filter(Boolean);
            pvMateriWrap.style.display = items.length ? 'block' : 'none';
            pvMateri.innerHTML = items.map(m => `<li>✅ ${m.replace(/</g, '&lt;')}</li>`).join('');
        };
        form.addEventListener('input', (e) => { if (e.target.name === 'materi[]') updMateri(); });
        form.addEventListener('click', (e) => { if (e.target.classList.contains('hapus-materi')) setTimeout(updMateri, 50); });
        document.getElementById('tambah-materi').addEventListener('click', () => setTimeout(updMateri, 50));
        updMateri();

        // Screenshot testimoni: gambar lama + file baru
        const pvSsWrap = $('pv-ss-wrap'), pvSs = $('pv-ss');
        const ssLama = @json($kampanye->testimoniSsUrls());
        const ssHapus = new Set();
        let ssBaru = [];
        const updSs = () => {
            const tampil = ssLama.filter((_, i) => !ssHapus.has(i)).concat(ssBaru);
            pvSsWrap.style.display = tampil.length ? 'block' : 'none';
            pvSs.innerHTML = tampil.map(u =>
                `<img src="${u}" class="rounded-xl shrink-0" style="width:120px;border:2px solid #F0E7CC" alt="Testimoni">`
            ).join('');
        };
        form.querySelectorAll('[name="hapus_ss[]"]').forEach(cb => {
            cb.addEventListener('change', () => {
                const i = parseInt(cb.value);
                cb.checked ? ssHapus.add(i) : ssHapus.delete(i);
                updSs();
            });
        });
        const ssInp = form.querySelector('[name="testimoni_ss[]"]');
        if (ssInp) ssInp.addEventListener('change', () => {
            ssBaru = [];
            [...ssInp.files].forEach(f => {
                const r = new FileReader();
                r.onload = (e) => { ssBaru.push(e.target.result); updSs(); };
                r.readAsDataURL(f);
            });
            if (!ssInp.files.length) updSs();
        });
        updSs();
    })();
    </script>
</x-admin-layout>
