<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InstallToken extends Model
{
    protected $fillable = ['user_id', 'product_id', 'token', 'dipakai_terakhir_at', 'dicabut'];

    protected $casts = [
        'dipakai_terakhir_at' => 'datetime',
        'dicabut' => 'boolean',
    ];

    /**
     * Ambil atau buat token install untuk (pengguna, MiniApp).
     */
    public static function untuk(User $user, Product $product): self
    {
        $token = static::firstOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            ['token' => Str::random(48)]
        );

        // Token yang pernah dicabut dihidupkan lagi KHUSUS lewat jalur ini
        // (klik Install oleh pemilik/admin yang sah) — dengan string token
        // BARU, agar pemilik bisa memasang ulang setelah pencabutan/copot
        // aplikasi, sementara tautan lama tetap mati bagi siapa pun.
        if ($token->dicabut) {
            $token->update(['dicabut' => false, 'token' => Str::random(48)]);
        }

        return $token;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function berlaku(): bool
    {
        return ! $this->dicabut;
    }
}
