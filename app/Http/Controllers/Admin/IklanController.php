<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PromoCampaign;
use App\Models\TestimonialImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            'gambarTestimoni' => TestimonialImage::latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['materi'] = $this->bersihkanMateri($data['materi'] ?? null);
        // Keluarkan field non-kolom sebelum create
        $secondaryIds = $data['produk_secondary_ids'] ?? [];
        $data['testimoni_ss'] = array_map('intval', $data['testimoni_ids']);
        unset($data['produk_secondary_ids'], $data['testimoni_ids']);
        $kampanye = PromoCampaign::create($data);
        $this->simpanProduk($kampanye, $data['produk_primary_id'], $secondaryIds);
        $this->simpanGambar($request, $kampanye);

        return redirect()->route('admin.iklan.index')->with('sukses', 'Iklan promo dibuat.');
    }

    public function edit(PromoCampaign $iklan): View
    {
        return view('admin.iklan.form', [
            'kampanye' => $iklan->load(['produkPrimary', 'produkSecondary']),
            'produks' => $this->produkTersedia(),
            'gambarTestimoni' => TestimonialImage::latest()->get(),
        ]);
    }

    public function update(Request $request, PromoCampaign $iklan): RedirectResponse
    {
        $data = $this->validated($request, $iklan->id);
        $data['materi'] = $this->bersihkanMateri($data['materi'] ?? null);
        $secondaryIds = $data['produk_secondary_ids'] ?? [];
        $data['testimoni_ss'] = array_map('intval', $data['testimoni_ids']);
        unset($data['produk_secondary_ids'], $data['testimoni_ids']);
        $iklan->update($data);
        $this->simpanProduk($iklan, $data['produk_primary_id'], $secondaryIds);
        $this->simpanGambar($request, $iklan);

        return redirect()->route('admin.iklan.index')->with('sukses', 'Iklan promo diperbarui.');
    }

    public function destroy(PromoCampaign $iklan): RedirectResponse
    {
        if ($iklan->gambar_path) {
            Storage::disk('public')->delete($iklan->gambar_path);
        }
        // Gambar testimoni milik galeri bersama — tidak ikut dihapus.
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
            'testimoni_ids' => ['required', 'array', 'min:3', 'max:6'],
            'testimoni_ids.*' => ['integer', 'exists:testimonial_images,id'],
            'produk_primary_id' => ['required', 'integer', Rule::exists('products', 'id')->where(
                fn ($q) => $q->where('status', Product::STATUS_TERSEDIA)->where('harga', '>', 0)
            )],
            'produk_secondary_ids' => ['nullable', 'array', 'max:3'],
            'produk_secondary_ids.*' => ['integer', Rule::exists('products', 'id')->where(
                fn ($q) => $q->where('status', Product::STATUS_TERSEDIA)->where('harga', '>', 0)
            ), 'different:produk_primary_id'],
            'tampilan_secondary' => ['required', 'in:card,list'],
            'urutan_konten' => ['nullable', 'array'],
            'urutan_konten.*' => ['string', 'in:'.implode(',', array_keys(PromoCampaign::SECTIONS))],
            'section_sembunyi' => ['nullable', 'array'],
            'section_sembunyi.*' => ['string', 'in:'.implode(',', array_keys(PromoCampaign::SECTIONS))],
            'mulai_at' => ['nullable', 'date'],
            'berakhir_at' => ['required', 'date', 'after_or_equal:mulai_at'],
            'status' => ['required', 'in:draft,aktif'],
        ], [
            'produk_secondary_ids.max' => 'Maksimal 3 produk tambahan.',
            'produk_primary_id.exists' => 'Produk utama harus produk yang tersedia dan sudah diberi harga (atur harga di Manage Produk).',
            'produk_secondary_ids.*.exists' => 'Produk tambahan harus tersedia dan sudah diberi harga (atur harga di Manage Produk).',
            'produk_secondary_ids.*.different' => 'Produk tambahan tidak boleh sama dengan produk utama.',
            'testimoni_ids.required' => 'Pilih gambar testimoni dari galeri.',
            'testimoni_ids.min' => 'Pilih minimal 3 gambar testimoni.',
            'testimoni_ids.max' => 'Pilih maksimal 6 gambar testimoni.',
            'testimoni_ids.*.exists' => 'Ada gambar testimoni yang sudah tidak ada di galeri.',
            'berakhir_at.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
        ]);

        // Form kini hanya mengirim tanggal: mulai = awal hari, berakhir = akhir hari.
        if (! empty($data['mulai_at'])) {
            $data['mulai_at'] = Carbon::parse($data['mulai_at'])->startOfDay();
        }
        if (! empty($data['berakhir_at'])) {
            $data['berakhir_at'] = Carbon::parse($data['berakhir_at'])->endOfDay();
        }

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
}
