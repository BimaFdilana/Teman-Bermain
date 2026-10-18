<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Miniapp;
use App\Models\Product;
use App\Rules\ValidMiniappHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MiniappController extends Controller
{
    /** CRUD MiniApps (PRD §6.5). Harga & stok diatur di Manage Product. */
    public function index(): View
    {
        $miniapps = Product::where('tipe', 'miniapp')->with('miniapp')->latest()->paginate(15);
        return view('admin.miniapp.index', compact('miniapps'));
    }

    public function create(): View
    {
        return view('admin.miniapp.form', ['product' => new Product(['tipe' => 'miniapp', 'status' => 'draft'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'pwa_name' => 'required|string|max:50',
            'status' => 'required|in:draft,tersedia',
            'cover' => 'nullable|image|max:2048',
            'icon' => 'nullable|image|max:1024',
            'html' => ['required', 'file', 'mimes:html,htm', 'max:5120', new ValidMiniappHtml],
            'versi' => 'nullable|string|max:20',
        ]);

        $slug = Str::slug($data['judul']).'-'.Str::random(6);
        $product = Product::create([
            'tipe' => 'miniapp',
            'judul' => $data['judul'],
            'slug' => $slug,
            'deskripsi' => $data['deskripsi'] ?? '',
            'harga' => 0,
            'status' => $data['status'],
        ]);

        if ($request->hasFile('cover')) {
            $product->update(['cover_path' => $request->file('cover')->store('covers', 'public')]);
        }

        $htmlPath = $request->file('html')->storeAs('miniapps', $slug.'.html', 'local');
        $iconPath = $request->hasFile('icon')
            ? $request->file('icon')->store('icons', 'public')
            : null;

        Miniapp::create([
            'product_id' => $product->id,
            'html_path' => $htmlPath,
            'pwa_name' => $data['pwa_name'],
            'icon_path' => $iconPath,
            'versi' => $data['versi'] ?? '1.0.0',
        ]);

        return redirect()->route('admin.miniapp.index')->with('sukses', 'MiniApp "'.$product->judul.'" ditambahkan.');
    }

    public function edit(Product $miniapp): View
    {
        abort_unless($miniapp->tipe === 'miniapp', 404);
        return view('admin.miniapp.form', ['product' => $miniapp->load('miniapp')]);
    }

    public function update(Request $request, Product $miniapp): RedirectResponse
    {
        abort_unless($miniapp->tipe === 'miniapp', 404);

        $data = $request->validate([
            'judul' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'pwa_name' => 'required|string|max:50',
            'status' => 'required|in:draft,tersedia,arsip',
            'cover' => 'nullable|image|max:2048',
            'icon' => 'nullable|image|max:1024',
            'html' => ['nullable', 'file', 'mimes:html,htm', 'max:5120', new ValidMiniappHtml],
            'versi' => 'nullable|string|max:20',
        ]);

        $miniapp->update([
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? '',
            'status' => $data['status'],
        ]);

        if ($request->hasFile('cover')) {
            if ($miniapp->cover_path) Storage::disk('public')->delete($miniapp->cover_path);
            $miniapp->update(['cover_path' => $request->file('cover')->store('covers', 'public')]);
        }

        if ($request->hasFile('icon')) {
            if ($miniapp->miniapp?->icon_path) Storage::disk('public')->delete($miniapp->miniapp->icon_path);
            $miniapp->miniapp->update(['icon_path' => $request->file('icon')->store('icons', 'public')]);
        }

        if ($request->hasFile('html')) {
            if ($miniapp->miniapp?->html_path) Storage::disk('local')->delete($miniapp->miniapp->html_path);
            $htmlPath = $request->file('html')->storeAs('miniapps', $miniapp->slug.'.html', 'local');
            $miniapp->miniapp->update(['html_path' => $htmlPath]);
        }

        $miniapp->miniapp->update([
            'pwa_name' => $data['pwa_name'],
            'versi' => $data['versi'] ?? $miniapp->miniapp->versi,
        ]);

        return redirect()->route('admin.miniapp.index')->with('sukses', 'MiniApp diperbarui.');
    }

    /**
     * Preview MiniApp sebelum publish (PRD §6.5).
     */
    public function preview(Product $miniapp): View
    {
        abort_unless($miniapp->tipe === 'miniapp', 404);
        $fileUrl = $miniapp->miniapp?->html_path
            ? URL::temporarySignedRoute('file.miniapp', now()->addMinutes(30), ['product' => $miniapp->id])
            : null;

        return view('admin.miniapp.preview', ['product' => $miniapp, 'fileUrl' => $fileUrl]);
    }

    public function destroy(Product $miniapp): RedirectResponse
    {
        abort_unless($miniapp->tipe === 'miniapp', 404);
        $judul = $miniapp->judul;
        if ($miniapp->cover_path) Storage::disk('public')->delete($miniapp->cover_path);
        if ($miniapp->miniapp?->icon_path) Storage::disk('public')->delete($miniapp->miniapp->icon_path);
        if ($miniapp->miniapp?->html_path) Storage::disk('local')->delete($miniapp->miniapp->html_path);
        $miniapp->delete();

        return redirect()->route('admin.miniapp.index')->with('sukses', 'MiniApp "'.$judul.'" dihapus.');
    }
}
