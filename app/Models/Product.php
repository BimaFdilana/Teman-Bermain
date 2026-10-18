<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'tipe', 'judul', 'slug', 'deskripsi', 'fitur', 'cover_path',
        'harga', 'harga_coret', 'stok', 'jenjang', 'status',
    ];

    protected $casts = [
        'fitur' => 'array',
    ];

    public const TIPE_EBOOK = 'ebook';
    public const TIPE_MINIAPP = 'miniapp';
    public const TIPE_BUNDLE = 'bundle';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_TERSEDIA = 'tersedia';
    public const STATUS_ARSIP = 'arsip';

    public function scopeTersedia($query)
    {
        return $query->where('status', self::STATUS_TERSEDIA);
    }

    public function ebook(): HasOne
    {
        return $this->hasOne(Ebook::class);
    }

    public function miniapp(): HasOne
    {
        return $this->hasOne(Miniapp::class);
    }

    public function bundle(): HasOne
    {
        return $this->hasOne(Bundle::class);
    }

    /** Harga coret bila promo, untuk tampilan. */
    public function isPromo(): bool
    {
        return $this->harga_coret !== null && $this->harga_coret > $this->harga;
    }

    public function hargaRupiah(): string
    {
        return 'Rp'.number_format($this->harga, 0, ',', '.');
    }

    public function hargaCoretRupiah(): ?string
    {
        return $this->harga_coret ? 'Rp'.number_format($this->harga_coret, 0, ',', '.') : null;
    }

    public function labelTipe(): string
    {
        return match ($this->tipe) {
            'ebook' => 'Ebook',
            'miniapp' => 'MiniApp',
            'bundle' => 'Paket',
            default => $this->tipe,
        };
    }
}
