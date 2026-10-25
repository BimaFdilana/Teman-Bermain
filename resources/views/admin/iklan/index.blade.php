<x-admin-layout>
    <div class="admin-topbar">
        <div>
            <h1 class="kidza-h2">📢 Iklan Promo</h1>
            <p style="color:#4b5563">Halaman promo multi-produk (1–4) dengan hitungan mundur.</p>
        </div>
        <a href="{{ route('admin.iklan.create') }}" class="kidza-btn kidza-btn-green">+ Buat Iklan</a>
    </div>

    <div class="kidza-card tabel-scroll">
        <table class="w-full text-sm">
            <thead>
                <tr style="background:#f3f4f6;text-align:left">
                    <th class="p-3">Judul</th>
                    <th class="p-3">Produk</th>
                    <th class="p-3">Periode</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Link</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($kampanyes as $k)
                    <tr class="border-t align-top">
                        <td class="p-3 font-bold" style="font-family:'Quicksand'">{{ $k->judul }}</td>
                        <td class="p-3">{{ $k->produkPrimary?->judul ?? '—' }}<span style="color:#6b7280"> +{{ $k->produk_secondary_count }} tambahan</span></td>
                        <td class="p-3 text-xs" style="color:#6b7280">
                            {{ $k->mulai_at?->format('d M Y H:i') ?? '—' }}<br>s/d {{ $k->berakhir_at->format('d M Y H:i') }}
                        </td>
                        <td class="p-3">
                            @if($k->isLive())
                                <span class="kidza-badge" style="background:#167941">Tayang</span>
                            @elseif($k->isBerakhir())
                                <span class="kidza-badge" style="background:#6b7280">Berakhir</span>
                            @elseif($k->status === 'aktif')
                                <span class="kidza-badge" style="background:#0077B6">Terjadwal</span>
                            @else
                                <span class="kidza-badge" style="background:#F3A712">Draft</span>
                            @endif
                            <br><span class="text-xs" style="color:#6b7280">status: {{ $k->status }}</span>
                        </td>
                        <td class="p-3">
                            <button class="kidza-btn" style="background:#e8f6ee" title="Salin link iklan"
                                    onclick="navigator.clipboard.writeText('{{ route('iklan.show', $k->slug) }}');this.textContent='✅';setTimeout(()=>this.textContent='🔗',1500)">🔗</button>
                        </td>
                        <td class="p-3 whitespace-nowrap">
                            <a href="{{ route('iklan.show', $k->slug) }}" target="_blank" class="kidza-link text-sm mr-3">{{ ($k->status === 'draft' || ($k->mulai_at && now()->lt($k->mulai_at))) ? 'Pratinjau' : 'Lihat' }}</a>
                            <a href="{{ route('admin.iklan.edit', $k) }}" class="kidza-link text-sm mr-3">Edit</a>
                            <form method="POST" action="{{ route('admin.iklan.destroy', $k) }}" class="inline"
                                  onsubmit="return tanyaForm(this, 'Hapus iklan {{ $k->judul }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm font-bold" style="color:#e74c3c">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center" style="color:#6b7280">Belum ada iklan promo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $kampanyes->links() }}</div>
</x-admin-layout>
