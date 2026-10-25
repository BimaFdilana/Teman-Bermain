<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Pengaturan: QR QRIS + kontak (PRD §6.6).
     */
    public function index(): View
    {
        return view('admin.pengaturan.index', [
            'qrisImage' => Setting::get('qris_image'),
            'waAdmin' => Setting::get('wa_admin'),
            'jamOperasional' => Setting::get('jam_operasional'),
            'alamat' => Setting::get('alamat'),
            'vaNumbers' => json_decode(Setting::get('va_numbers', '{}') ?? '{}', true) ?: [],
            'pajakPersen' => Setting::get('pajak_persen', '0'),
            'logoSlider' => json_decode(Setting::get('logo_slider', '[]') ?? '[]', true) ?: [],
            'wablasAktif' => Setting::get('wablas_aktif') === '1',
            'wablasDomain' => Setting::get('wablas_domain', 'console.wablas.com'),
            'wablasTokenTersimpan' => \App\Services\WablasService::tokenTersimpan(),
            'wablasSecretTersimpan' => \App\Services\WablasService::secretTersimpan(),
        ]);
    }

    /** Upload logo ke slider landing (logo usaha + logo pendukung). */
    public function logoSliderStore(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'array', 'min:1', 'max:10'],
            'logo.*' => ['image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ], [
            'logo.required' => 'Pilih minimal 1 file logo.',
            'logo.*.image' => 'Semua file harus berupa gambar.',
            'logo.*.max' => 'Ukuran logo maksimal 2MB per file.',
        ]);

        $daftar = json_decode(Setting::get('logo_slider', '[]') ?? '[]', true) ?: [];
        foreach ($request->file('logo', []) as $file) {
            $daftar[] = $file->store('logo-slider', 'public');
        }
        Setting::set('logo_slider', json_encode(array_values($daftar)));

        return back()->with('sukses', 'Logo slider ditambahkan.');
    }

    /** Hapus satu logo dari slider berdasarkan urutannya. */
    public function logoSliderDestroy(int $index): RedirectResponse
    {
        $daftar = json_decode(Setting::get('logo_slider', '[]') ?? '[]', true) ?: [];
        if (isset($daftar[$index])) {
            Storage::disk('public')->delete($daftar[$index]);
            unset($daftar[$index]);
            Setting::set('logo_slider', json_encode(array_values($daftar)));
        }

        return back()->with('sukses', 'Logo slider dihapus.');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'qris' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'wa_admin' => ['nullable', 'string', 'max:20'],
            'jam_operasional' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'pajak_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'va_numbers' => ['nullable', 'array'],
            'va_numbers.*' => ['nullable', 'string', 'max:30'],
            'wablas_domain' => ['nullable', 'string', 'max:100'],
            'wablas_token' => ['nullable', 'string', 'max:255'],
            'wablas_secret' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('qris')) {
            $lama = Setting::get('qris_image');
            if ($lama) {
                Storage::disk('public')->delete($lama);
            }
            $path = $request->file('qris')->store('qris', 'public');
            Setting::set('qris_image', $path);
        }

        foreach (['wa_admin', 'jam_operasional', 'alamat'] as $key) {
            if ($request->filled($key)) {
                $value = $key === 'wa_admin'
                    ? \App\Models\User::normalizeWhatsapp($request->input($key))
                    : $request->input($key);
                Setting::set($key, $value);
            }
        }

        // Nomor rekening per bank (BR-RC-23) & pajak (BR-RC-22)
        $va = array_filter(array_map('trim', (array) $request->input('va_numbers', [])));
        Setting::set('va_numbers', json_encode($va));
        Setting::set('pajak_persen', (string) max(0, (float) $request->input('pajak_persen', 0)));

        // Notifikasi WhatsApp via Wablas (token & secret disimpan terenkripsi; kosong = pertahankan lama).
        Setting::set('wablas_aktif', $request->boolean('wablas_aktif') ? '1' : '0');
        if ($request->filled('wablas_domain')) {
            Setting::set('wablas_domain', trim($request->input('wablas_domain')));
        }
        if ($request->filled('wablas_token')) {
            Setting::set('wablas_token', \Illuminate\Support\Facades\Crypt::encryptString($request->input('wablas_token')));
        }
        if ($request->filled('wablas_secret')) {
            Setting::set('wablas_secret', \Illuminate\Support\Facades\Crypt::encryptString($request->input('wablas_secret')));
        }

        return back()->with('sukses', 'Pengaturan disimpan.');
    }

    /** Kirim pesan test Wablas ke No. WhatsApp Admin (hasil langsung, tanpa antrean). */
    public function wablasTest(): RedirectResponse
    {
        if (! \App\Services\WablasService::enabled()) {
            return back()->with('gagal', 'Aktifkan Wablas dan isi token + secret + domain dulu, lalu simpan.');
        }

        $waAdmin = Setting::get('wa_admin');
        if (! $waAdmin) {
            return back()->with('gagal', 'Isi No. WhatsApp Admin dulu, lalu simpan.');
        }

        $wablas = app(\App\Services\WablasService::class);
        $ok = $wablas->kirim(
            $waAdmin,
            '✅ Tes Wablas Rumah Cahaya-bks berhasil! Notifikasi otomatis sudah aktif.'
        );

        return $ok
            ? back()->with('sukses', 'Pesan test terkirim ke WA admin. Cek HP admin.')
            : back()->with('gagal', 'Pesan test gagal terkirim ('.$wablas->errorTerakhir.'). Cek token, domain, dan status device Wablas.');
    }
}
