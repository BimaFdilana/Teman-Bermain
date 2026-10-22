<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestimonialImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TestimoniGambarController extends Controller
{
    /** Galeri gambar testimoni: upload sekali, dipakai berulang di iklan. */
    public function index(): View
    {
        $gambars = TestimonialImage::latest()->get();
        $pemakaian = [];
        foreach ($gambars as $g) {
            $pemakaian[$g->id] = $g->jumlahPemakaian();
        }

        return view('admin.testimoni-gambar.index', compact('gambars', 'pemakaian'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'gambar' => ['required', 'array', 'min:1', 'max:20'],
            'gambar.*' => ['image', 'max:2048'],
        ], [
            'gambar.required' => 'Pilih minimal 1 gambar untuk diupload.',
            'gambar.*.image' => 'Semua file harus berupa gambar.',
            'gambar.*.max' => 'Ukuran gambar maksimal 2MB per file.',
        ]);

        $jumlah = 0;
        foreach ($data['gambar'] as $file) {
            $path = $file->store('testimoni', 'public');
            TestimonialImage::firstOrCreate(['path' => $path]);
            $jumlah++;
        }

        return back()->with('sukses', "{$jumlah} gambar testimoni ditambahkan ke galeri.");
    }

    public function destroy(TestimonialImage $testimoniGambar): RedirectResponse
    {
        $dipakai = $testimoniGambar->jumlahPemakaian();
        if ($dipakai > 0) {
            return back()->withErrors([
                'gambar' => "Gambar ini sedang dipakai di {$dipakai} iklan — lepaskan dulu dari iklan tersebut sebelum menghapus.",
            ]);
        }

        Storage::disk('public')->delete($testimoniGambar->path);
        $testimoniGambar->delete();

        return back()->with('sukses', 'Gambar testimoni dihapus dari galeri.');
    }
}
