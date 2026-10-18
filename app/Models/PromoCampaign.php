<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PromoCampaign extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_AKTIF = 'aktif';

    protected $fillable = [
        'judul', 'subjudul', 'slug', 'deskripsi', 'gambar_path',
        'mulai_at', 'berakhir_at', 'status',
    ];

    protected $casts = [
        'mulai_at' => 'datetime',
        'berakhir_at' => 'datetime',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promo_campaign_product')
            ->withPivot('urutan')
            ->orderByPivot('urutan');
    }

    /** Kampanye sedang tayang: aktif dan dalam rentang waktu. */
    public function isLive(): bool
    {
        if ($this->status !== self::STATUS_AKTIF) {
            return false;
        }
        $now = now();
        if ($this->mulai_at && $now->lt($this->mulai_at)) {
            return false;
        }
        return $now->lte($this->berakhir_at);
    }

    public function isBerakhir(): bool
    {
        return now()->gt($this->berakhir_at);
    }

    /** Sisa waktu dalam detik (0 bila sudah berakhir). */
    public function sisaDetik(): int
    {
        return max(0, (int) now()->diffInSeconds($this->berakhir_at, false));
    }

    /** URL gambar banner (format path sama seperti cover produk). */
    public function gambarUrl(): ?string
    {
        if (! $this->gambar_path) {
            return null;
        }
        if (str_starts_with($this->gambar_path, 'images/')) {
            return asset($this->gambar_path);
        }
        return asset('storage/' . $this->gambar_path);
    }
}
