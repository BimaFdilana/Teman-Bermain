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
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'qris' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'wa_admin' => ['nullable', 'string', 'max:20'],
            'jam_operasional' => ['nullable', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:255'],
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

        return back()->with('sukses', 'Pengaturan disimpan.');
    }
}
