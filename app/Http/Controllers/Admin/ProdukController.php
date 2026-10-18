<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProdukController extends Controller
{
    /**
     * Manage Product — 3 tab: Ebook / MiniApps / Bundle (PRD §6.7).
     * Harga, harga coret, stok & status diatur di sini (satu sumber kebenaran).
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'ebook');
        $tab = in_array($tab, ['ebook', 'miniapp', 'bundle']) ? $tab : 'ebook';

        $products = Product::where('tipe', $tab)
            ->when($tab === 'bundle', fn ($q) => $q->with('bundle.items'))
            ->latest()->paginate(15);

        return view('admin.produk.index', compact('tab', 'products'));
    }

    /**
     * Update harga / harga coret / stok / status satu produk.
     */
    public function updateHarga(Request $request, Product $produk): RedirectResponse
    {
        abort_unless(in_array($produk->tipe, ['ebook', 'miniapp']), 404);

        $data = $request->validate([
            'harga' => 'required|integer|min:0',
            'harga_coret' => 'nullable|integer|min:0|gt:harga',
            'stok' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,tersedia,arsip',
        ]);

        $produk->update([
            'harga' => $data['harga'],
            'harga_coret' => $data['harga_coret'] ?? null,
            'stok' => $data['stok'] ?? null,
            'status' => $data['status'],
        ]);

        \App\Models\ActivityLog::catat('ubah_harga', "\"{$produk->judul}\": Rp ".number_format($data['harga'], 0, ',', '.').", status {$data['status']}");

        return back()->with('sukses', 'Harga & stok "'.$produk->judul.'" diperbarui.');
    }

    // ---------- Bundle builder ----------

    public function bundleCreate(): View
    {
        $kandidat = Product::whereIn('tipe', ['ebook', 'miniapp'])
            ->where('status', 'tersedia')->orderBy('judul')->get();
        return view('admin.produk.bundle-form', ['bundle' => new Product(['tipe' => 'bundle', 'status' => 'draft']), 'kandidat' => $kandidat, 'terpilih' => []]);
    }

    public function bundleStore(Request $request): RedirectResponse
    {
        $data = $this->validasiBundle($request);

        $bundle = Product::create([
            'tipe' => 'bundle',
            'judul' => $data['judul'],
            'slug' => Str::slug($data['judul']).'-'.Str::random(6),
            'deskripsi' => $data['deskripsi'] ?? '',
            'harga' => $data['harga'],
            'harga_coret' => $data['harga_coret'] ?? null,
            'stok' => $data['stok'] ?? null,
            'status' => $data['status'],
        ]);
        Bundle::create(['product_id' => $bundle->id]);
        $this->simpanIsi($bundle, $data['items']);
        $this->simpanCoverBundle($request, $bundle);

        return redirect()->route('admin.produk.index', ['tab' => 'bundle'])
            ->with('sukses', 'Bundle "'.$bundle->judul.'" dibuat.');
    }

    public function bundleEdit(Product $bundle): View
    {
        abort_unless($bundle->tipe === 'bundle', 404);
        $kandidat = Product::whereIn('tipe', ['ebook', 'miniapp'])
            ->where('status', 'tersedia')->orderBy('judul')->get();
        $terpilih = $bundle->bundle?->items->pluck('id')->toArray() ?? [];
        return view('admin.produk.bundle-form', ['bundle' => $bundle, 'kandidat' => $kandidat, 'terpilih' => $terpilih]);
    }

    public function bundleUpdate(Request $request, Product $bundle): RedirectResponse
    {
        abort_unless($bundle->tipe === 'bundle', 404);
        $data = $this->validasiBundle($request);

        $bundle->update([
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? '',
            'harga' => $data['harga'],
            'harga_coret' => $data['harga_coret'] ?? null,
            'stok' => $data['stok'] ?? null,
            'status' => $data['status'],
        ]);
        $this->simpanIsi($bundle, $data['items']);
        $this->simpanCoverBundle($request, $bundle);

        return redirect()->route('admin.produk.index', ['tab' => 'bundle'])
            ->with('sukses', 'Bundle "'.$bundle->judul.'" diperbarui.');
    }

    public function bundleDestroy(Product $bundle): RedirectResponse
    {
        abort_unless($bundle->tipe === 'bundle', 404);
        $judul = $bundle->judul;
        $bundle->delete();

        return redirect()->route('admin.produk.index', ['tab' => 'bundle'])
            ->with('sukses', 'Bundle "'.$judul.'" dihapus.');
    }

    private function validasiBundle(Request $request): array
    {
        return $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|integer|min:0',
            'harga_coret' => 'nullable|integer|min:0|gt:harga',
            'stok' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,tersedia,arsip',
            'items' => 'required|array|min:2',
            'items.*' => 'exists:products,id',
            'cover' => 'nullable|image|max:2048',
        ], ['items.min' => 'Bundle minimal berisi 2 produk.']);
    }

    /** Simpan/upload cover bundle bila ada file baru. */
    private function simpanCoverBundle(Request $request, Product $bundle): void
    {
        if ($request->hasFile('cover')) {
            if ($bundle->cover_path) {
                Storage::disk('public')->delete($bundle->cover_path);
            }
            $bundle->update(['cover_path' => $request->file('cover')->store('covers', 'public')]);
        }
    }

    private function simpanIsi(Product $bundle, array $itemIds): void
    {
        // Cegah bundle berisi dirinya sendiri
        $itemIds = array_filter(array_unique($itemIds), fn ($id) => (int) $id !== $bundle->id);
        $bundle->bundle->items()->sync($itemIds);
    }
}
