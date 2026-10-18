<x-admin-layout>
<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-bold" style="font-family:'Quicksand'">🏷️ Manage Product</h1>
    @if($tab === 'bundle')
        <a href="{{ route('admin.produk.bundle.create') }}" class="kidza-btn kidza-btn-green">+ Buat Bundle</a>
    @endif
</div>

{{-- 3 tab --}}
<div class="flex gap-2 mb-5">
    @foreach(['ebook' => '📚 Ebook', 'miniapp' => '🎮 MiniApps', 'bundle' => '🎁 Bundle'] as $key => $label)
        <a href="{{ route('admin.produk.index', ['tab' => $key]) }}"
           class="kidza-btn {{ $tab === $key ? 'kidza-btn-green' : '' }}"
           style="{{ $tab === $key ? '' : 'background:#fff' }}">{{ $label }}</a>
    @endforeach
</div>

@if(session('sukses'))
    <div class="kidza-card p-3 mb-4" style="background:#e8f8f0"><p class="text-sm font-bold" style="color:#167941">{{ session('sukses') }}</p></div>
@endif

@if($products->isEmpty())
    <x-empty-state pesan="Belum ada produk di tab ini." />
@else
    <x-data-table>
        <x-slot:head>
            <th class="text-left px-4 py-2">Produk</th>
            <th class="text-left px-4 py-2">Harga</th>
            <th class="text-left px-4 py-2">Coret</th>
            <th class="text-left px-4 py-2">Stok</th>
            <th class="text-left px-4 py-2">Status</th>
            <th class="text-left px-4 py-2">Aksi</th>
        </x-slot:head>
        @foreach($products as $p)
            <tr class="border-t align-top">
                <td class="px-4 py-2">
                    <p class="font-bold">{{ $p->judul }}</p>
                    @if($p->tipe === 'bundle' && $p->bundle)
                        <p class="text-xs" style="color:#6b7280">Isi: {{ $p->bundle->items->pluck('judul')->join(', ') }}</p>
                    @endif
                </td>
                <td class="px-4 py-2 whitespace-nowrap">Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                <td class="px-4 py-2 whitespace-nowrap">{{ $p->harga_coret ? 'Rp '.number_format($p->harga_coret, 0, ',', '.') : '—' }}</td>
                <td class="px-4 py-2">{{ is_null($p->stok) ? '∞' : $p->stok }}</td>
                <td class="px-4 py-2"><x-badge-status :status="$p->status" /></td>
                <td class="px-4 py-2">
                    <button class="kidza-btn" style="background:#e8f6ee" title="Salin link promosi"
                            onclick="navigator.clipboard.writeText('{{ route('promo.show', $p->slug) }}');this.textContent='✅';setTimeout(()=>this.textContent='🔗',1500)">🔗</button>
                    @if($p->tipe === 'bundle')
                        <a href="{{ route('admin.produk.bundle.edit', $p) }}" class="kidza-btn" style="background:#fff">✏️</a>
                        <form method="POST" action="{{ route('admin.produk.bundle.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus bundle ini?')">
                            @csrf @method('DELETE')
                            <button class="kidza-btn" style="background:#fdecea">🗑️</button>
                        </form>
                    @else
                        <button class="kidza-btn" style="background:#fff" onclick="document.getElementById('harga-{{ $p->id }}').style.display='flex'">💰 Harga</button>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
    <div class="mt-4">{{ $products->appends(['tab' => $tab])->links() }}</div>
@endif

{{-- Modal harga per produk --}}
@foreach($products as $p)
    @if($p->tipe !== 'bundle')
    <x-modal id="harga-{{ $p->id }}" judul="💰 Harga: {{ $p->judul }}">
        <form method="POST" action="{{ route('admin.produk.harga', $p) }}">
            @csrf @method('PUT')
            <x-form-input label="Harga (Rp)" name="harga" type="number" :value="$p->harga" required attrs="min=0" />
            <x-form-input label="Harga Coret (Rp, opsional)" name="harga_coret" type="number" :value="$p->harga_coret" attrs="min=0" help="Harus lebih besar dari harga. Kosongkan bila tidak promo." />
            <x-form-input label="Stok (kosongkan = tidak terbatas)" name="stok" type="number" :value="$p->stok" attrs="min=0" />
            <x-form-input label="Status" name="status" type="select" required>
                @foreach(['draft' => 'Draft', 'tersedia' => 'Tersedia', 'arsip' => 'Arsip'] as $v => $l)
                    <option value="{{ $v }}" {{ $p->status === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </x-form-input>
            <button type="submit" class="kidza-btn kidza-btn-green">💾 Simpan</button>
        </form>
    </x-modal>
    @endif
@endforeach
</x-admin-layout>
