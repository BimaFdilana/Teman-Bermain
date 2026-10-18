<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PromoCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IklanController extends Controller
{
    public function index(): View
    {
        return view('admin.iklan.index', [
            'kampanyes' => PromoCampaign::withCount('products')->latest()->paginate(15),
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
        $kampanye = PromoCampaign::create($data);
        $this->simpanProduk($kampanye, $data['produk_ids']);

        return redirect()->route('admin.iklan.index')->with('sukses', 'Iklan promo dibuat.');
    }

    public function edit(PromoCampaign $iklan): View
    {
        return view('admin.iklan.form', [
            'kampanye' => $iklan->load('products'),
            'produks' => $this->produkTersedia(),
        ]);
    }

    public function update(Request $request, PromoCampaign $iklan): RedirectResponse
    {
        $data = $this->validated($request, $iklan->id);
        $iklan->update($data);
        $this->simpanProduk($iklan, $data['produk_ids']);

        return redirect()->route('admin.iklan.index')->with('sukses', 'Iklan promo diperbarui.');
    }

    public function destroy(PromoCampaign $iklan): RedirectResponse
    {
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
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', 'unique:promo_campaigns,slug,'.$ignoreId],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'produk_ids' => ['required', 'array', 'min:1', 'max:4'],
            'produk_ids.*' => ['integer', 'exists:products,id'],
            'mulai_at' => ['nullable', 'date'],
            'berakhir_at' => ['required', 'date', 'after:mulai_at'],
            'status' => ['required', 'in:draft,aktif'],
        ], [
            'produk_ids.min' => 'Pilih minimal 1 produk.',
            'produk_ids.max' => 'Maksimal 4 produk per iklan.',
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

    private function simpanProduk(PromoCampaign $kampanye, array $ids): void
    {
        $sync = [];
        foreach (array_values($ids) as $i => $id) {
            $sync[$id] = ['urutan' => $i];
        }
        $kampanye->products()->sync($sync);
    }
}
