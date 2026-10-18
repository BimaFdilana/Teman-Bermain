<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscountCode extends Model
{
    protected $fillable = [
        'kode', 'tipe', 'nilai', 'kuota', 'terpakai', 'mulai', 'berakhir', 'aktif',
    ];

    protected $casts = [
        'mulai' => 'datetime',
        'berakhir' => 'datetime',
        'aktif' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Apakah kode masih berlaku saat ini? */
    public function berlaku(): bool
    {
        if (! $this->aktif) {
            return false;
        }
        $now = now();
        if ($this->mulai && $now->lt($this->mulai)) {
            return false;
        }
        if ($this->berakhir && $now->gt($this->berakhir)) {
            return false;
        }
        if ($this->kuota !== null && $this->terpakai >= $this->kuota) {
            return false;
        }
        return true;
    }

    /** Hitung total setelah diskon (tidak di bawah 0). */
    public function hitungTotal(int $harga): int
    {
        $diskon = $this->tipe === 'persen'
            ? (int) round($harga * $this->nilai / 100)
            : $this->nilai;
        return max(0, $harga - $diskon);
    }

    public function label(): string
    {
        return $this->tipe === 'persen' ? "Diskon {$this->nilai}%" : 'Potongan Rp'.number_format($this->nilai, 0, ',', '.');
    }
}
