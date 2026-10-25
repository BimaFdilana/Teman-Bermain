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
 * - wablas_secret: secret key device (disimpan terenkripsi)
 * - wablas_domain: domain server API (akun ini: texas.wablas.com)
 * - wa_admin: nomor WA admin penerima notifikasi order baru (sudah ada)
 *
 * Autentikasi sesuai dokumentasi Wablas (V1 POST):
 * header "Authorization: TOKEN.SECRET_KEY", form phone + message.
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
            && self::secret() !== null
            && self::domain() !== null;
    }

    public static function token(): ?string
    {
        return self::kredensial('wablas_token');
    }

    public static function secret(): ?string
    {
        return self::kredensial('wablas_secret');
    }

    private static function kredensial(string $key): ?string
    {
        $tersimpan = Setting::get($key);
        if (! $tersimpan) {
            return null;
        }

        try {
            return trim(Crypt::decryptString($tersimpan));
        } catch (\Throwable) {
            // Nilai lama yang tersimpan polos tetap dipakai.
            return trim($tersimpan);
        }
    }

    public static function tokenTersimpan(): bool
    {
        return Setting::get('wablas_token') !== null;
    }

    public static function secretTersimpan(): bool
    {
        return Setting::get('wablas_secret') !== null;
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
        $secret = self::secret();
        $domain = self::domain();
        if ($token === null || $secret === null || $domain === null) {
            $this->errorTerakhir = 'token/secret/domain belum dikonfigurasi';
            Log::info('Wablas dilewati: token/secret/domain belum dikonfigurasi.', [
                'tujuan' => $tujuan,
            ]);

            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => $token.'.'.$secret])
                ->asForm()
                ->post('https://'.$domain.'/api/send-message', [
                    'phone' => $tujuan,
                    'message' => $pesan,
                ]);

            $apiMessage = $response->json('message');
            if (! $response->successful()) {
                $this->errorTerakhir = 'HTTP '.$response->status()
                    .($apiMessage ? ': '.$apiMessage : '');
                Log::warning('Wablas gagal mengirim.', [
                    'tujuan' => $tujuan,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }
            if ($response->json('status') === false) {
                $this->errorTerakhir = $apiMessage ? 'API: '.$apiMessage : 'API status false';
                Log::warning('Wablas menolak pengiriman.', [
                    'tujuan' => $tujuan,
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
