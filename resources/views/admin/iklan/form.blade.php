<x-admin-layout>
    <a href="{{ route('admin.iklan.index') }}" class="kidza-link text-sm mb-4 inline-block">← Kembali</a>
    <h1 class="kidza-h2 mb-6">{{ $kampanye->exists ? 'Edit' : 'Buat' }} Iklan Promo</h1>

    <div class="flex flex-col lg:flex-row gap-6 items-start">
    <form method="POST" id="form-iklan" action="{{ $kampanye->exists ? route('admin.iklan.update', $kampanye) : route('admin.iklan.store') }}"
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
            <input type="text" name="subjudul" value="{{ old('subjudul', $kampanye->subjudul) }}" maxlength="500"
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
            <label class="text-sm font-bold block mb-1">📱 Screenshot Testimoni WhatsApp <span class="font-normal" style="color:#6b7280">(pilih 3–6 gambar dari galeri)</span></label>
            @if($gambarTestimoni->isEmpty())
                <p class="text-sm border-2 border-dashed rounded-xl px-4 py-3" style="color:#6b7280;border-color:#d1d5db">
                    Galeri masih kosong. <a href="{{ route('admin.testimoni-gambar.index') }}" class="font-bold underline" style="color:#167941">Upload gambar dulu di menu Gambar Testimoni →</a>
                </p>
            @else
                <div class="flex flex-wrap gap-2 mb-2">
                    @foreach($gambarTestimoni as $g)
                        <label class="relative inline-block cursor-pointer" title="Klik untuk memilih">
                            <input type="checkbox" name="testimoni_ids[]" value="{{ $g->id }}" class="cek-testimoni sr-only"
                                   data-url="{{ $g->url() }}"
                                   @checked(in_array($g->id, old('testimoni_ids', array_map('intval', $kampanye->testimoni_ss ?? []))))>
                            <img src="{{ $g->url() }}" alt="Testimoni" class="rounded-lg gambar-testimoni" style="height:90px;border:3px solid #e5e7eb;opacity:.55">
                            <span class="tanda-pilih absolute top-0 right-0 rounded-bl-lg px-1 text-xs font-bold" style="background:#167941;color:#fff;display:none">✓</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs" style="color:#6b7280">Terpilih: <span id="hitung-testimoni" class="font-bold">0</span> dari 3–6 gambar.
                    <a href="{{ route('admin.testimoni-gambar.index') }}" class="font-bold underline" style="color:#167941">Kelola galeri →</a></p>
            @endif
            @error('testimoni_ids')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
            @error('testimoni_ids.*')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
            <div class="mt-3 flex items-center gap-2 flex-wrap">
                <label class="text-sm font-bold" for="testimoni_bg">🎨 Warna latar kartu foto testimoni</label>
                <input type="color" id="testimoni_bg" name="testimoni_bg" value="{{ old('testimoni_bg', $kampanye->testimoni_bg ?? '#ffffff') }}" class="rounded border" style="width:44px;height:32px;padding:2px">
                <span class="text-xs" style="color:#6b7280">Menjadi frame di sekeliling tiap foto screenshot. Default putih.</span>
            </div>
            @error('testimoni_bg')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
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
            @php
                $primaryDipilih = old('produk_primary_id', $kampanye->produk_primary_id);
                $secondaryDipilih = old('produk_secondary_ids', $kampanye->produkSecondary->pluck('id')->toArray());
                $tipeLabel = ['ebook' => '📚 Ebook', 'miniapp' => '🎮 MiniApp', 'bundle' => '🎁 Bundle'];
            @endphp
            <label class="text-sm font-bold block mb-1">⭐ Produk Utama <span style="color:#6b7280">(1 produk — bintang iklan)</span></label>
            <select name="produk_primary_id" id="pilih-primary" required class="w-full border-2 border-gray-300 rounded-xl px-4 py-2 mb-4">
                <option value="">— Pilih produk utama —</option>
                @foreach($produks as $p)
                    <option value="{{ $p->id }}" @selected((string)$primaryDipilih === (string)$p->id)>{{ $p->judul }} — Rp {{ number_format($p->harga, 0, ',', '.') }}</option>
                @endforeach
            </select>
            @error('produk_primary_id')<p class="text-xs font-bold mt-1 mb-2" style="color:#e74c3c">{{ $message }}</p>@enderror

            <label class="text-sm font-bold block mb-1">🎁 Produk Tambahan <span style="color:#6b7280">(maks 3, opsional)</span>
                <span id="hitung-produk" class="kidza-badge ml-2" style="background:#167941">0 dipilih</span>
            </label>
            <div class="border-2 border-gray-300 rounded-xl p-4 grid md:grid-cols-2 gap-2 mb-3" style="max-height:14rem;overflow-y:auto">
                @foreach($produks->groupBy('tipe') as $tipe => $daftar)
                    <p class="md:col-span-2 text-xs font-bold mt-1" style="color:#6b7280">{{ $tipeLabel[$tipe] ?? $tipe }}</p>
                    @foreach($daftar as $p)
                        <label class="flex items-center gap-2 text-sm border rounded-xl px-3 py-2 cursor-pointer hover:bg-gray-50 label-secondary" data-id="{{ $p->id }}">
                            <input type="checkbox" name="produk_secondary_ids[]" value="{{ $p->id }}"
                                   @checked(in_array($p->id, (array) $secondaryDipilih)) class="cek-produk w-4 h-4">
                            <span class="font-bold">{{ $p->judul }}</span>
                            <span class="ml-auto whitespace-nowrap" style="color:#6b7280">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                        </label>
                    @endforeach
                @endforeach
            </div>
            @error('produk_secondary_ids')<p class="text-xs font-bold mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror

            <label class="text-sm font-bold block mb-1">Tampilan produk tambahan</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 text-sm border-2 rounded-xl px-4 py-2 cursor-pointer">
                    <input type="radio" name="tampilan_secondary" value="card" @checked(old('tampilan_secondary', $kampanye->tampilan_secondary ?? 'card') === 'card') class="w-4 h-4">
                    🗂️ Card
                </label>
                <label class="flex items-center gap-2 text-sm border-2 rounded-xl px-4 py-2 cursor-pointer">
                    <input type="radio" name="tampilan_secondary" value="list" @checked(old('tampilan_secondary', $kampanye->tampilan_secondary ?? 'card') === 'list') class="w-4 h-4">
                    📋 List
                </label>
            </div>
        </div>

        <div class="mb-4">
            <label class="text-sm font-bold block mb-1">🔀 Susun Urutan Konten <span class="font-normal" style="color:#6b7280">(atas = tampil duluan)</span></label>
            <div id="daftar-section" class="border-2 border-gray-300 rounded-xl p-3 space-y-1" style="max-height:16rem;overflow-y:auto">
                @php
                    $urutAwal = old('urutan_konten', $kampanye->urutanKontenEfektif());
                    $sembunyiAwal = old('section_sembunyi', $kampanye->section_sembunyi ?? []);
                @endphp
                @foreach($urutAwal as $key)
                    <div class="section-row flex items-center gap-2 bg-white border rounded-xl px-3 py-2" data-key="{{ $key }}">
                        <input type="hidden" name="urutan_konten[]" value="{{ $key }}">
                        <span class="text-sm font-bold flex-1">{{ \App\Models\PromoCampaign::SECTIONS[$key] ?? $key }}</span>
                        <label class="text-xs flex items-center gap-1 cursor-pointer" style="color:#6b7280" title="Sembunyikan section">
                            <input type="checkbox" name="section_sembunyi[]" value="{{ $key }}" @checked(in_array($key, (array)$sembunyiAwal)) class="cek-sembunyi"> 🙈
                        </label>
                        <button type="button" class="pindah-atas kidza-btn px-2 py-1 text-xs" title="Naik">▲</button>
                        <button type="button" class="pindah-bawah kidza-btn px-2 py-1 text-xs" title="Turun">▼</button>
                    </div>
                @endforeach
            </div>
            <p class="text-xs mt-1" style="color:#6b7280">Centang 🙈 untuk menyembunyikan section dari halaman iklan.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="text-sm font-bold block mb-1">Mulai (opsional)</label>
                <input type="date" name="mulai_at"
                       value="{{ old('mulai_at', $kampanye->mulai_at?->format('Y-m-d')) }}"
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
                <p class="text-xs mt-1" style="color:#6b7280">Tayang mulai awal hari tanggal ini. Kosongkan = langsung tayang.</p>
            </div>
            <div>
                <label class="text-sm font-bold block mb-1">Berakhir</label>
                <input type="date" name="berakhir_at" required
                       value="{{ old('berakhir_at', $kampanye->berakhir_at?->format('Y-m-d')) }}"
                       class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
                <p class="text-xs mt-1" style="color:#6b7280">Tayang sampai akhir hari tanggal ini.</p>
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
    <aside class="w-full lg:w-[400px] shrink-0 lg:sticky lg:top-6">
        <p class="text-sm font-bold mb-2" style="color:#6b7280">👁️ Preview Iklan <span class="font-normal">(update otomatis)</span></p>
        <div id="pv-phone" class="mx-auto bg-white rounded-[2rem] overflow-hidden" style="max-width:400px;border:3px solid #1a1a1a;box-shadow:6px 6px 0 #1a1a1a">
            <div class="text-center py-3" style="background:#FFFEF7;border-bottom:2px solid #F0E7CC">
                <span class="font-extrabold" style="font-family:'Baloo 2',cursive;color:#1E7B34">rumahcahaya-bks</span>
            </div>
            <div class="p-4 overflow-y-auto" style="max-height:70vh;background:#FFFEF7">
                {{-- Judul + slogan: tetap di atas seperti halaman publik --}}
                <p class="text-center font-extrabold mb-1" style="color:#A63D2F;font-size:.8rem">🔥 PROMO SPESIAL 🔥</p>
                <h2 id="pv-judul" class="text-center font-extrabold mb-2" style="color:#14532D;font-size:1.3rem;line-height:1.3">Judul Promo</h2>
                <p id="pv-subjudul" class="text-center text-sm mb-3" style="color:#5B5344"></p>
                {{-- Section preview: urutan & visibilitas mengikuti pengaturan admin --}}
                <div id="pv-konten">
                    <div data-pv-section="banner" class="mb-3">
                        <img id="pv-banner" class="rounded-2xl w-full object-cover" style="display:none;aspect-ratio:16/9" alt="Banner">
                    </div>
                    <div data-pv-section="countdown" class="text-center mb-3">
                        <span class="inline-block px-4 py-1 rounded-full text-xs font-bold" style="background:#FDF0EC;color:#A63D2F;border:1.5px solid #E8C4BC">⏳ <span id="pv-countdown-text">Atur tanggal berakhir untuk melihat hitungan mundur</span></span>
                        <p id="pv-countdown-sampai" class="text-xs mt-1" style="color:#a39e93"></p>
                    </div>
                    <div data-pv-section="deskripsi" class="mb-4">
                        <p id="pv-deskripsi" class="text-sm" style="color:#5B5344;line-height:1.6;text-align:justify;overflow-wrap:break-word;word-break:break-word"></p>
                    </div>
                    <div data-pv-section="produk_utama" class="mb-4">
                        <div id="pv-produk-utama"></div>
                    </div>
                    <div data-pv-section="produk_tambahan" class="mb-4">
                        <div id="pv-produk-tambahan"></div>
                    </div>
                    <div data-pv-section="video_ajakan" class="mb-4">
                        <div id="pv-video" style="display:none">
                            <div class="rounded-2xl flex items-center justify-center text-4xl" style="aspect-ratio:16/9;background:#1a1a1a">▶️</div>
                        </div>
                        <div id="pv-ajakan-wrap" class="mt-2" style="display:none">
                            <p id="pv-ajakan" class="text-center text-sm font-bold italic px-4" style="color:#1E7B34"></p>
                        </div>
                    </div>
                    <div data-pv-section="materi" class="mb-4">
                        <div id="pv-materi-wrap" style="display:none">
                            <p class="font-extrabold text-sm mb-2" style="color:#14532D">📖 Materi Ebook</p>
                            <ul id="pv-materi" class="text-sm space-y-1" style="color:#5B5344"></ul>
                        </div>
                    </div>
                    <div data-pv-section="testimoni" class="mb-4">
                        <div id="pv-ss-wrap" style="display:none">
                            <p class="font-extrabold text-center text-sm mb-2" style="color:#14532D">💬 Kata Mereka</p>
                            <div id="pv-ss" class="flex gap-2 overflow-x-auto pb-2" style="scrollbar-width:none"></div>
                        </div>
                    </div>
                    <div data-pv-section="harga_cta" class="mb-4">
                        <div id="pv-harga-wrap" class="text-center" style="display:none">
                            <p class="font-extrabold mb-2" style="font-size:1.2rem;color:#14532D">HARGA SPESIAL</p>
                            <div id="pv-harga-list"></div>
                        </div>
                        <div class="text-center mt-4">
                            <span class="kidza-btn kidza-btn-green" style="font-size:.9rem;display:inline-block;pointer-events:none">👉 Pesan Sekarang 👈</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>
    </div>
    <script>
    (function () {
        const boxes = [...document.querySelectorAll('.cek-produk')];
        const primary = document.getElementById('pilih-primary');
        const label = document.getElementById('hitung-produk');
        function hitung() {
            // Cegah produk utama dipilih sebagai tambahan
            boxes.forEach(b => {
                const lbl = b.closest('.label-secondary');
                if (primary.value && b.value === primary.value) {
                    b.checked = false; b.disabled = true; lbl.style.opacity = '.4';
                } else { lbl.style.opacity = ''; }
            });
            const n = boxes.filter(b => b.checked).length;
            label.textContent = n + ' dipilih';
            label.style.background = n > 3 ? '#e74c3c' : '#167941';
            boxes.forEach(b => { if (!(primary.value && b.value === primary.value)) b.disabled = !b.checked && n >= 3; });
        }
        boxes.forEach(b => b.addEventListener('change', hitung));
        primary.addEventListener('change', hitung);
        hitung();
    })();
    // Susun urutan section (naik/turun)
    (function () {
        const daftar = document.getElementById('daftar-section');
        if (!daftar) return;
        daftar.addEventListener('click', (e) => {
            const btn = e.target.closest('.pindah-atas, .pindah-bawah');
            if (!btn) return;
            const row = btn.closest('.section-row');
            if (btn.classList.contains('pindah-atas') && row.previousElementSibling) {
                daftar.insertBefore(row, row.previousElementSibling);
            } else if (btn.classList.contains('pindah-bawah') && row.nextElementSibling) {
                daftar.insertBefore(row.nextElementSibling, row);
            }
        });
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
        const form = document.getElementById('form-iklan');
        @php
            $produkData = $produks->mapWithKeys(fn($p) => [$p->id => [
                'judul' => $p->judul, 'harga' => $p->harga,
                'harga_coret' => $p->harga_coret, 'cover' => $p->coverUrl(),
            ]]);
        @endphp
        const produkData = @json($produkData);

        const txt = (name, elId, def = '') => {
            const inp = form.querySelector(`[name="${name}"]`);
            const el = $(elId);
            const upd = () => { el.textContent = inp.value.trim() || def; el.style.display = inp.value.trim() || def ? '' : 'none'; };
            inp.addEventListener('input', upd); upd();
        };
        txt('judul', 'pv-judul', 'Judul Promo');
        txt('subjudul', 'pv-subjudul');
        txt('deskripsi', 'pv-deskripsi');
        // Ajakan: tampilkan/sembunyikan WRAP-nya (sebelumnya wrap tidak pernah dibuka)
        const ajakanInp = form.querySelector('[name="ajakan"]');
        const pvAjakanWrap = $('pv-ajakan-wrap');
        const updAjakan = () => {
            $('pv-ajakan').textContent = ajakanInp.value.trim();
            pvAjakanWrap.style.display = ajakanInp.value.trim() ? 'block' : 'none';
        };
        ajakanInp.addEventListener('input', updAjakan); updAjakan();

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

        // Produk: utama & tambahan terpisah + rekap HARGA SPESIAL (seperti halaman publik)
        const pvProdukUtama = $('pv-produk-utama');
        const pvProdukTambahan = $('pv-produk-tambahan');
        const pvHargaWrap = $('pv-harga-wrap');
        const pvHargaList = $('pv-harga-list');
        const rp = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
        const cardProduk = (p, besar) => `
            <div class="rounded-2xl p-3 mb-2 bg-white" style="border:2px solid #F0E7CC">
                ${p.cover ? `<img src="${p.cover}" class="rounded-xl w-full object-cover mb-2" style="aspect-ratio:16/${besar ? '10' : '12'}" alt="">` : ''}
                <p class="font-extrabold ${besar ? '' : 'text-sm'}" style="color:#14532D;${besar ? 'font-size:1.1rem' : ''}">${p.judul}</p>
                <p class="${besar ? '' : 'text-sm'}"><b style="color:#1E7B34">${rp(p.harga)}</b>
                ${p.harga_coret ? `<span class="line-through text-xs" style="color:#a39e93">${rp(p.harga_coret)}</span>` : ''}</p>
            </div>`;
        const listProduk = (p) => `
            <div class="flex items-center gap-2 rounded-2xl p-2 mb-2 bg-white" style="border:2px solid #F0E7CC">
                ${p.cover ? `<img src="${p.cover}" class="rounded-xl object-cover shrink-0" style="width:56px;height:56px" alt="">` : ''}
                <div class="flex-1 min-w-0">
                    <p class="font-extrabold text-xs truncate" style="color:#14532D">${p.judul}</p>
                    <p class="text-xs"><b style="color:#1E7B34">${rp(p.harga)}</b></p>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full shrink-0" style="background:#F5B301">+ Tambah</span>
            </div>`;
        const updProduk = () => {
            const primaryEl = document.getElementById('pilih-primary');
            const mode = form.querySelector('[name="tampilan_secondary"]:checked')?.value || 'card';
            const pUtama = produkData[primaryEl.value];
            pvProdukUtama.innerHTML = pUtama
                ? `<p class="text-xs font-bold mb-1" style="color:#a39e93">⭐ PRODUK UTAMA</p>` + cardProduk(pUtama, true)
                : '<p class="text-xs text-center" style="color:#a39e93">— belum ada produk utama —</p>';
            const ids = [...form.querySelectorAll('.cek-produk:checked')].map(c => c.value);
            pvProdukTambahan.innerHTML = ids.length
                ? `<p class="text-xs font-bold mb-1" style="color:#a39e93">🎁 PRODUK TAMBAHAN</p>` + ids.map(id => {
                    const p = produkData[id];
                    return p ? (mode === 'list' ? listProduk(p) : cardProduk(p, false)) : '';
                }).join('')
                : '';
            // Rekap HARGA SPESIAL: hanya produk utama (seperti halaman publik)
            pvHargaWrap.style.display = pUtama ? 'block' : 'none';
            pvHargaList.innerHTML = pUtama ? `
                <div class="mb-2">
                    <p class="text-xs font-bold" style="color:#a39e93">${pUtama.judul}</p>
                    ${pUtama.harga_coret ? `<span class="text-xs line-through" style="color:#a39e93">${rp(pUtama.harga_coret)}</span> ` : ''}
                    <p class="font-extrabold" style="font-size:1.1rem;color:#14532D">${rp(pUtama.harga)}</p>
                </div>` : '';
        };
        form.querySelectorAll('.cek-produk').forEach(c => c.addEventListener('change', updProduk));
        document.getElementById('pilih-primary').addEventListener('change', updProduk);
        form.querySelectorAll('[name="tampilan_secondary"]').forEach(r => r.addEventListener('change', updProduk));
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

        // Screenshot testimoni: pilihan dari galeri (checkbox) -> preview + penghitung
        const pvSsWrap = $('pv-ss-wrap'), pvSs = $('pv-ss');
        const cekTestimoni = [...form.querySelectorAll('.cek-testimoni')];
        const hitungTestimoni = $('hitung-testimoni');
        const updSs = () => {
            const dipilih = cekTestimoni.filter(cb => cb.checked);
            if (hitungTestimoni) hitungTestimoni.textContent = dipilih.length;
            cekTestimoni.forEach(cb => {
                const label = cb.closest('label');
                const img = label.querySelector('.gambar-testimoni');
                const tanda = label.querySelector('.tanda-pilih');
                img.style.borderColor = cb.checked ? '#167941' : '#e5e7eb';
                img.style.opacity = cb.checked ? '1' : '.55';
                tanda.style.display = cb.checked ? 'block' : 'none';
                cb.disabled = !cb.checked && dipilih.length >= 6;
                label.style.opacity = cb.disabled ? '.4' : '';
            });
            pvSsWrap.style.display = dipilih.length ? 'block' : 'none';
            const bgSs = document.getElementById('testimoni_bg')?.value || '#ffffff';
            pvSs.innerHTML = dipilih.map(cb =>
                `<div class="shrink-0 rounded-xl" style="width:120px;background:${bgSs};border:2px solid #F0E7CC;padding:6px"><img src="${cb.dataset.url}" class="rounded-lg w-full" alt="Testimoni"></div>`
            ).join('');
        };
        cekTestimoni.forEach(cb => cb.addEventListener('change', updSs));
        document.getElementById('testimoni_bg')?.addEventListener('input', updSs);
        updSs();

        // Hitungan mundur LIVE dari tanggal berakhir (akhir hari), bukan teks statis
        const berakhirInp = form.querySelector('[name="berakhir_at"]');
        const pvCountdownText = $('pv-countdown-text');
        const pvCountdownSampai = $('pv-countdown-sampai');
        const updCountdown = () => {
            if (!berakhirInp.value) {
                pvCountdownText.textContent = 'Atur tanggal berakhir untuk melihat hitungan mundur';
                pvCountdownSampai.textContent = '';
                return;
            }
            const akhir = new Date(berakhirInp.value + 'T23:59:59').getTime();
            const sisa = Math.max(0, akhir - Date.now());
            const hari = Math.floor(sisa / 86400000);
            const jam = String(Math.floor(sisa / 3600000) % 24).padStart(2, '0');
            const mnt = String(Math.floor(sisa / 60000) % 60).padStart(2, '0');
            const dtk = String(Math.floor(sisa / 1000) % 60).padStart(2, '0');
            pvCountdownText.textContent = sisa <= 0
                ? 'Promo telah berakhir'
                : `Berakhir dalam ${hari} hari ${jam}:${mnt}:${dtk}`;
            pvCountdownSampai.textContent = 's/d ' + berakhirInp.value.split('-').reverse().join(' ') + ', 23:59 WIB';
        };
        berakhirInp.addEventListener('change', updCountdown);
        updCountdown();
        setInterval(updCountdown, 1000);

        // Urutan & section tersembunyi preview mengikuti pengaturan admin (▲▼ dan 🙈)
        const pvKonten = $('pv-konten');
        const daftarSection = document.getElementById('daftar-section');
        const terapkanUrutanPreview = () => {
            if (!daftarSection || !pvKonten) return;
            [...daftarSection.querySelectorAll('.section-row')].forEach(row => {
                const blok = pvKonten.querySelector(`[data-pv-section="${row.dataset.key}"]`);
                if (!blok) return;
                const sembunyi = row.querySelector('.cek-sembunyi')?.checked;
                blok.style.display = sembunyi ? 'none' : '';
                pvKonten.appendChild(blok);
            });
        };
        daftarSection?.addEventListener('click', () => setTimeout(terapkanUrutanPreview, 0));
        daftarSection?.addEventListener('change', terapkanUrutanPreview);
        terapkanUrutanPreview();
    })();
    </script>
</x-admin-layout>
