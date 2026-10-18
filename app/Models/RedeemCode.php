<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedeemCode extends Model
{
    protected $fillable = ['kode', 'order_id', 'product_id', 'status', 'dipakai_pada'];

    protected $casts = ['dipakai_pada' => 'datetime'];

    /**
     * Generate kode unik format RC-XXXX-XXXX (tanpa karakter ambigu 0/O/1/I/L).
     */
    public static function generate(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        do {
            $rand = '';
            for ($i = 0; $i < 8; $i++) {
                $rand .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $kode = 'RC-'.substr($rand, 0, 4).'-'.substr($rand, 4, 4);
        } while (static::where('kode', $kode)->exists());

        return $kode;
    }

    /** Normalisasi input pengguna ke format kode. */
    public static function normalize(string $input): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $input));
        if (str_starts_with($clean, 'RC') && strlen($clean) === 10) {
            return 'RC-'.substr($clean, 2, 4).'-'.substr($clean, 6, 4);
        }
        return strtoupper(trim($input));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
