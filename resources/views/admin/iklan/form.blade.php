<x-admin-layout>
    <a href="{{ route('admin.iklan.index') }}" class="kidza-link text-sm mb-4 inline-block">← Kembali</a>
    <h1 class="kidza-h2 mb-6">{{ $kampanye->exists ? 'Edit' : 'Buat' }} Iklan Promo</h1>

    <form method="POST" action="{{ $kampanye->exists ? route('admin.iklan.update', $kampanye) : route('admin.iklan.store') }}"
          class="kidza-card p-6" style="max-width:44rem">
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
            <label class="text-sm font-bold block mb-1">Deskripsi</label>
            <textarea name="deskripsi" rows="3" maxlength="1000"
                      class="w-full border-2 border-gray-300 rounded-xl px-4 py-2"
                      placeholder="Ceritakan promonya secara singkat...">{{ old('deskripsi', $kampanye->deskripsi) }}</textarea>
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
    </script>
</x-admin-layout>
