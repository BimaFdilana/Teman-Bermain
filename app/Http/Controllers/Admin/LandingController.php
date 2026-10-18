<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Manage Landing Page (PRD §6.9): hero, paket unggulan, testimoni, kontak, footer.
     */
    public function index(): View
    {
        $bundles = Product::where('tipe', 'bundle')->where('status', 'tersedia')
            ->orderBy('judul')->get();
        $paketTerpilih = json_decode(Setting::get('paket_unggulan_ids', '[]'), true) ?: [];

        $testimonials = Testimonial::orderBy('urutan')->get();
        $sampahTestimoni = Testimonial::onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        return view('admin.landing.index', compact('bundles', 'paketTerpilih', 'testimonials', 'sampahTestimoni'));
    }

    /**
     * Simpan teks-teks landing (hero, kontak, footer).
     */
    public function simpanTeks(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hero_headline' => 'required|string|max:200',
            'hero_subheadline' => 'nullable|string|max:500',
            'hero_image' => 'nullable|image|max:2048',
            'wa_admin' => 'required|string|max:20',
            'jam_operasional' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:500',
            'footer_tagline' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('hero_image')) {
            $lama = Setting::get('hero_image');
            if ($lama) Storage::disk('public')->delete($lama);
            Setting::set('hero_image', $request->file('hero_image')->store('landing', 'public'));
        }

        foreach ($data as $key => $value) {
            if ($key === 'hero_image') continue;
            Setting::set($key, $value ?? '');
        }

        return back()->with('sukses', 'Teks & gambar hero landing page disimpan.');
    }

    /**
     * Simpan foto-foto background landing (Misi Kami, Footer, Kata Mereka).
     */
    public function simpanFoto(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'misi_bg' => 'nullable|image|max:2048',
            'footer_bg' => 'nullable|image|max:2048',
            'testimoni_bg' => 'nullable|image|max:2048',
        ]);

        foreach (['misi_bg', 'footer_bg', 'testimoni_bg'] as $key) {
            if ($request->hasFile($key)) {
                $lama = Setting::get($key);
                // Jangan hapus file bawaan
                if ($lama && ! in_array($lama, ['landing/galeri-1.jpg', 'landing/galeri-2.jpg', 'landing/testimoni-bg.jpg', 'landing/hero-ebook.jpg'])) {
                    Storage::disk('public')->delete($lama);
                }
                Setting::set($key, $request->file($key)->store('landing', 'public'));
            }
        }

        return back()->with('sukses', 'Foto background landing page disimpan.');
    }

    /**
     * Simpan pilihan 3 paket unggulan.
     */
    public function simpanPaket(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'paket' => 'nullable|array|max:3',
            'paket.*' => 'exists:products,id',
        ]);

        Setting::set('paket_unggulan_ids', json_encode(array_values($data['paket'] ?? [])));

        return back()->with('sukses', 'Paket unggulan diperbarui.');
    }

    // ---------- Testimoni ----------

    public function testimoniStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'peran' => 'nullable|string|max:100',
            'isi' => 'required|string|max:1000',
            'foto' => 'nullable|image|max:1024',
            'aktif' => 'nullable|boolean',
        ]);

        $fotoPath = $request->hasFile('foto') ? $request->file('foto')->store('testimoni', 'public') : null;

        Testimonial::create([
            'nama' => $data['nama'],
            'peran' => $data['peran'] ?? null,
            'isi' => $data['isi'],
            'foto_path' => $fotoPath,
            'urutan' => Testimonial::max('urutan') + 1,
            'aktif' => (bool) ($data['aktif'] ?? false),
        ]);

        return back()->with('sukses', 'Testimoni ditambahkan.');
    }

    public function testimoniUpdate(Request $request, Testimonial $testimoni): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'peran' => 'nullable|string|max:100',
            'isi' => 'required|string|max:1000',
            'foto' => 'nullable|image|max:1024',
            'aktif' => 'nullable|boolean',
            'urutan' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('foto')) {
            if ($testimoni->foto_path) Storage::disk('public')->delete($testimoni->foto_path);
            $testimoni->foto_path = $request->file('foto')->store('testimoni', 'public');
        }

        $testimoni->update([
            'nama' => $data['nama'],
            'peran' => $data['peran'] ?? null,
            'isi' => $data['isi'],
            'foto_path' => $testimoni->foto_path,
            'aktif' => (bool) ($data['aktif'] ?? false),
            'urutan' => $data['urutan'] ?? $testimoni->urutan,
        ]);

        return back()->with('sukses', 'Testimoni diperbarui.');
    }

    public function testimoniDestroy(Testimonial $testimoni): RedirectResponse
    {
        // Soft delete (BR-RC-16): masuk sampah, bisa dikembalikan 30 hari.
        // Foto dipertahankan agar bisa direstore utuh; dihapus permanen saat purge.
        $testimoni->delete();

        return back()->with('sukses', 'Testimoni dipindah ke sampah. Bisa dikembalikan dalam 30 hari.');
    }

    /** Kembalikan testimoni dari sampah. */
    public function testimoniRestore(int $id): RedirectResponse
    {
        $testimoni = Testimonial::onlyTrashed()->findOrFail($id);
        $testimoni->restore();

        return back()->with('sukses', 'Testimoni dikembalikan.');
    }

    /** Hapus permanen dari sampah. */
    public function testimoniForceDelete(int $id): RedirectResponse
    {
        $testimoni = Testimonial::onlyTrashed()->findOrFail($id);
        if ($testimoni->foto_path) Storage::disk('public')->delete($testimoni->foto_path);
        $testimoni->forceDelete();

        return back()->with('sukses', 'Testimoni dihapus permanen.');
    }
}
