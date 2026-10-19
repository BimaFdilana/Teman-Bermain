<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PromoCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IklanController extends Controller
{
    public function index(): View
    {
        return view('admin.iklan.index', [
            'kampanyes' => PromoCampaign::with('produkPrimary')->withCount('produkSecondary')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.iklan.form', [
            'kampanye' => new PromoCampaign(),
            'produks' => $this->produkTersedia(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if (count($request->file('testimoni_ss', [])) < 3) {
            return back()->withErrors(['testimoni_ss' => 'Upload minimal 3 screenshot testimoni WhatsApp (maksimal 6).'])->withInput();
        }
        $data['materi'] = $this->bersihkanMateri($data['materi'] ?? null);
        // Keluarkan field non-kolom sebelum create
        $secondaryIds = $data['produk_secondary_ids'] ?? [];
        unset($data['produk_secondary_ids'], $data['testimoni_ss'], $data['hapus_ss']);
        $kampanye = PromoCampaign::create($data);
        $this->simpanProduk($kampanye, $data['produk_primary_id'], $secondaryIds);
        $this->simpanGambar($request, $kampanye);
        $this->simpanTestimoniSs($request, $kampanye);

        return redirect()->route('admin.iklan.index')->with('sukses', 'Iklan promo dibuat.');
    }

    public function edit(PromoCampaign $iklan): View
    {
        return view('admin.iklan.form', [
            'kampanye' => $iklan->load(['produkPrimary', 'produkSecondary']),
            'produks' => $this->produkTersedia(),
        ]);
    }

    public function update(Request $request, PromoCampaign $iklan): RedirectResponse
    {
        $data = $this->validated($request, $iklan->id);
        $data['materi'] = $this->bersihkanMateri($data['materi'] ?? null);
        $secondaryIds = $data['produk_secondary_ids'] ?? [];
        unset($data['produk_secondary_ids'], $data['testimoni_ss'], $data['hapus_ss']);
        $iklan->update($data);
        $this->simpanProduk($iklan, $data['produk_primary_id'], $secondaryIds);
        $this->simpanGambar($request, $iklan);
        $hasilSs = $this->kelolaTestimoniSs($request, $iklan);
        if ($hasilSs !== true) {
            return back()->withErrors(['testimoni_ss' => $hasilSs])->withInput();
        }

        return redirect()->route('admin.iklan.index')->with('sukses', 'Iklan promo diperbarui.');
    }

    public function destroy(PromoCampaign $iklan): RedirectResponse
    {
        if ($iklan->gambar_path) {
            Storage::disk('public')->delete($iklan->gambar_path);
        }
        foreach ($iklan->testimoni_ss ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
        $iklan->delete();

        return redirect()->route('admin.iklan.index')->with('sukses', 'Iklan promo dihapus.');
    }

    /** Produk/bundle aktif yang bisa dipilih, dikelompokkan per tipe. */
    private function produkTersedia()
    {
        return Product::tersedia()->orderBy('tipe')->orderBy('judul')->get();
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:120'],
            'subjudul' => ['nullable', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', 'unique:promo_campaigns,slug,'.$ignoreId],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'gambar' => ['nullable', 'image', 'max:2048'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'ajakan' => ['nullable', 'string', 'max:500'],
            'materi' => ['nullable', 'array', 'max:50'],
            'materi.*' => ['nullable', 'string', 'max:120'],
            'testimoni_ss' => ['nullable', 'array', 'max:6'],
            'testimoni_ss.*' => ['image', 'max:2048'],
            'hapus_ss' => ['nullable', 'array'],
            'hapus_ss.*' => ['integer', 'min:0'],
            'produk_primary_id' => ['required', 'integer', 'exists:products,id'],
            'produk_secondary_ids' => ['nullable', 'array', 'max:3'],
            'produk_secondary_ids.*' => ['integer', 'exists:products,id', 'different:produk_primary_id'],
            'tampilan_secondary' => ['required', 'in:card,list'],
            'urutan_konten' => ['nullable', 'array'],
            'urutan_konten.*' => ['string', 'in:'.implode(',', array_keys(PromoCampaign::SECTIONS))],
            'section_sembunyi' => ['nullable', 'array'],
            'section_sembunyi.*' => ['string', 'in:'.implode(',', array_keys(PromoCampaign::SECTIONS))],
            'mulai_at' => ['nullable', 'date'],
            'berakhir_at' => ['required', 'date', 'after:mulai_at'],
            'status' => ['required', 'in:draft,aktif'],
        ], [
            'produk_secondary_ids.max' => 'Maksimal 3 produk tambahan.',
            'produk_secondary_ids.*.different' => 'Produk tambahan tidak boleh sama dengan produk utama.',
            'berakhir_at.after' => 'Waktu berakhir harus setelah waktu mulai.',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = $this->slugUnik($data['judul'], $ignoreId);
        }

        return $data;
    }

    private function slugUnik(string $judul, ?int $ignoreId): string
    {
        $dasar = Str::slug($judul) ?: 'promo';
        $slug = $dasar;
        $i = 2;
        while (PromoCampaign::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $dasar.'-'.$i++;
        }
        return $slug;
    }

    /** Simpan produk utama + produk tambahan (pivot). */
    private function simpanProduk(PromoCampaign $kampanye, int $primaryId, array $secondaryIds): void
    {
        $kampanye->update(['produk_primary_id' => $primaryId]);
        $sync = [];
        foreach (array_values($secondaryIds) as $i => $id) {
            $sync[$id] = ['urutan' => $i];
        }
        $kampanye->produkSecondary()->sync($sync);
    }

    /** Buang entri materi yang kosong; null bila tidak ada isi. */
    private function bersihkanMateri(?array $materi): ?array
    {
        if (! $materi) {
            return null;
        }
        $bersih = array_values(array_filter(array_map('trim', $materi), fn ($m) => $m !== ''));
        return $bersih === [] ? null : $bersih;
    }

    /** Simpan/upload gambar banner bila ada file baru. */
    private function simpanGambar(Request $request, PromoCampaign $kampanye): void
    {
        if ($request->hasFile('gambar')) {
            if ($kampanye->gambar_path) {
                Storage::disk('public')->delete($kampanye->gambar_path);
            }
            $kampanye->update(['gambar_path' => $request->file('gambar')->store('promo', 'public')]);
        }
    }

    /** Simpan screenshot testimoni baru (store): langsung jadi array path. */
    private function simpanTestimoniSs(Request $request, PromoCampaign $kampanye): void
    {
        $paths = [];
        foreach ($request->file('testimoni_ss', []) as $file) {
            $paths[] = $file->store('promo/testimoni', 'public');
        }
        $kampanye->update(['testimoni_ss' => $paths]);
    }

    /**
     * Kelola screenshot testimoni saat update: hapus yang ditandai + tambah baru.
     * Total harus 3-6 gambar. Return true bila OK, pesan error bila tidak.
     */
    private function kelolaTestimoniSs(Request $request, PromoCampaign $kampanye): bool|string
    {
        $ssSisa = [];
        foreach ($kampanye->testimoni_ss ?? [] as $i => $path) {
            if (in_array($i, $request->input('hapus_ss', []))) {
                Storage::disk('public')->delete($path);
            } else {
                $ssSisa[] = $path;
            }
        }
        $ssBaru = [];
        foreach ($request->file('testimoni_ss', []) as $file) {
            $ssBaru[] = $file->store('promo/testimoni', 'public');
        }
        $total = array_merge($ssSisa, $ssBaru);
        $jumlah = count($total);
        if ($jumlah < 3 || $jumlah > 6) {
            foreach ($ssBaru as $p) {
                Storage::disk('public')->delete($p);
            }

            return "Total screenshot testimoni harus 3–6 gambar (saat ini {$jumlah}).";
        }
        $kampanye->update(['testimoni_ss' => $total]);

        return true;
    }
}
