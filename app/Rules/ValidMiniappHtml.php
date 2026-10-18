<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Validasi file HTML MiniApp sesuai spesifikasi PRD §6.5:
 * - satu file .html mandiri, CSS & JS inline
 * - dilarang <script src> eksternal (Google Fonts via <link> boleh)
 * - gambar harus data-URI atau URL https (tidak boleh path lokal)
 */
class ValidMiniappHtml implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $html = file_get_contents($value->getRealPath());
        if ($html === false || stripos($html, '<html') === false) {
            $fail('File bukan dokumen HTML yang valid.');
            return;
        }

        if (preg_match('/<script[^>]+src\s*=/i', $html)) {
            $fail('Dilarang memakai <script src> eksternal — semua JavaScript harus inline di dalam file.');
        }

        // <img src> harus data-URI atau https (bukan file lokal relatif)
        if (preg_match_all('/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i', $html, $m)) {
            foreach ($m[1] as $src) {
                $src = trim($src);
                if ($src === '') continue;
                if (! str_starts_with($src, 'data:') && ! str_starts_with($src, 'https://') && ! str_starts_with($src, 'http://')) {
                    $fail('Gambar di dalam file harus data-URI — ditemukan <img src="'.$src.'"> yang mengarah ke file lokal.');
                    break;
                }
            }
        }
    }
}
