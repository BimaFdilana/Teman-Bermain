<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ebook;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EbookController extends Controller
{
    /** CRUD Ebook (PRD §6.4). Harga & stok diatur di Manage Product. */
    public function index(): View
    {
        $ebooks = Product::where('tipe', 'ebook')->with('ebook')->latest()->paginate(15);
        return view('admin.ebook.index', compact('ebooks'));
    }

    public function create(): View
    {
        return view('admin.ebook.form', ['product' => new Product(['tipe' => 'ebook', 'status' => 'draft'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:draft,tersedia',
            'cover' => 'nullable|image|max:2048',
            'pdf' => 'required|file|mimes:pdf|max:10240',
            'jumlah_halaman' => 'nullable|integer|min:1',
        ]);

        $slug = Str::slug($data['judul']).'-'.Str::random(6);
        $product = Product::create([
            'tipe' => 'ebook',
            'judul' => $data['judul'],
            'slug' => $slug,
            'deskripsi' => $data['deskripsi'] ?? '',
            'harga' => 0,
            'status' => $data['status'],
        ]);

        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('covers', 'public');
            $product->update(['cover_path' => $path]);
        }

        $pdfPath = $request->file('pdf')->storeAs('ebooks', $slug.'.pdf', 'local');
        Ebook::create([
            'product_id' => $product->id,
            'pdf_path' => $pdfPath,
            'jumlah_halaman' => $data['jumlah_halaman'] ?? null,
        ]);

        return redirect()->route('admin.ebook.index')->with('sukses', 'Ebook "'.$product->judul.'" ditambahkan.');
    }

    public function edit(Product $ebook): View
    {
        abort_unless($ebook->tipe === 'ebook', 404);
        return view('admin.ebook.form', ['product' => $ebook->load('ebook')]);
    }

    public function update(Request $request, Product $ebook): RedirectResponse
    {
        abort_unless($ebook->tipe === 'ebook', 404);

        $data = $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:draft,tersedia,arsip',
            'cover' => 'nullable|image|max:2048',
            'pdf' => 'nullable|file|mimes:pdf|max:10240',
            'jumlah_halaman' => 'nullable|integer|min:1',
        ]);

        $statusLama = $ebook->status;
        $ebook->update([
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? '',
            'status' => $data['status'],
        ]);

        if ($statusLama !== $data['status'] && in_array($data['status'], ['tersedia', 'arsip'])) {
            \App\Models\ActivityLog::catat(
                $data['status'] === 'tersedia' ? 'publish_produk' : 'arsip_produk',
                "Ebook \"{$ebook->judul}\": {$statusLama} → {$data['status']}"
            );
        }

        if ($request->hasFile('cover')) {
            if ($ebook->cover_path) Storage::disk('public')->delete($ebook->cover_path);
            $ebook->update(['cover_path' => $request->file('cover')->store('covers', 'public')]);
        }

        if ($request->hasFile('pdf')) {
            if ($ebook->ebook?->pdf_path) Storage::disk('local')->delete($ebook->ebook->pdf_path);
            $pdfPath = $request->file('pdf')->storeAs('ebooks', $ebook->slug.'.pdf', 'local');
            $ebook->ebook->update(['pdf_path' => $pdfPath]);
        }

        if (array_key_exists('jumlah_halaman', $data)) {
            $ebook->ebook->update(['jumlah_halaman' => $data['jumlah_halaman']]);
        }

        return redirect()->route('admin.ebook.index')->with('sukses', 'Ebook diperbarui.');
    }

    public function destroy(Product $ebook): RedirectResponse
    {
        abort_unless($ebook->tipe === 'ebook', 404);
        $judul = $ebook->judul;
        if ($ebook->cover_path) Storage::disk('public')->delete($ebook->cover_path);
        if ($ebook->ebook?->pdf_path) Storage::disk('local')->delete($ebook->ebook->pdf_path);
        $ebook->delete();

        return redirect()->route('admin.ebook.index')->with('sukses', 'Ebook "'.$judul.'" dihapus.');
    }
}
