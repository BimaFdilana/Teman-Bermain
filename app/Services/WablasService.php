<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Notifikasi WhatsApp via Wablas (device Nano, 1 nomor pengirim).
 *
 * Konfigurasi di ⚙️ Pengaturan (bukan .env, agar admin bisa tempel sendiri):
 * - wablas_aktif: '1' = aktif
 * - wablas_token: token API device (disimpan terenkripsi)
 * - wablas_domain: domain server API (default console.wablas.com)
 * - wa_admin: nomor WA admin penerima notifikasi order baru (sudah ada)
 *
 * Tanpa konfigurasi lengkap, enabled() = false dan semua pengiriman
 * dilewati diam-diam — tombol wa.me manual tetap jadi fallback.
 * Kegagalan kirim tidak pernah melempar exception ke caller.
 */
class WablasService
{
    public static function enabled(): bool
    {
        return Setting::get('wablas_aktif') === '1'
            && self::token() !== null
            && self::domain() !== null;
    }

    public static function token(): ?string
    {
        $tersimpan = Setting::get('wablas_token');
        if (! $tersimpan) {
            return null;
        }

        try {
            return Crypt::decryptString($tersimpan);
        } catch (\Throwable) {
            // Token lama yang tersimpan polos tetap dipakai.
            return $tersimpan;
        }
    }

    public static function tokenTersimpan(): bool
    {
        return Setting::get('wablas_token') !== null;
    }

    public static function domain(): ?string
    {
        $domain = trim((string) Setting::get('wablas_domain', 'console.wablas.com'));
        // Terima tempelan URL lengkap (https://texas.wablas.com/) -> texas.wablas.com
        $domain = (string) preg_replace('#^https?://#i', '', $domain);
        $domain = explode('/', $domain)[0];

        return $domain !== '' ? $domain : null;
    }

    /** Penyebab gagal terakhir (untuk ditampilkan tombol test). */
    public ?string $errorTerakhir = null;

    /** Kirim pesan teks; false bila nonaktif/gagal (tidak throw). */
    public function kirim(?string $tujuan, string $pesan): bool
    {
        $this->errorTerakhir = null;
        $tujuan = User::normalizeWhatsapp($tujuan);
        if ($tujuan === null || $pesan === '') {
            $this->errorTerakhir = 'nomor tujuan tidak valid';

            return false;
        }

        $token = self::token();
        $domain = self::domain();
        if ($token === null || $domain === null) {
            $this->errorTerakhir = 'token/domain belum dikonfigurasi';
            Log::info('Wablas dilewati: token/domain belum dikonfigurasi.', [
                'tujuan' => $tujuan,
            ]);

            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => $token])
                ->post('https://'.$domain.'/api/send-message', [
                    'phone' => $tujuan,
                    'message' => $pesan,
                ]);

            if (! $response->successful()) {
                $this->errorTerakhir = 'HTTP '.$response->status();
                Log::warning('Wablas gagal mengirim.', [
                    'tujuan' => $tujuan,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $this->errorTerakhir = 'tidak bisa terhubung ke '.$domain;
            Log::warning('Wablas error: '.$e->getMessage(), ['tujuan' => $tujuan]);

            return false;
        }
    }
}
