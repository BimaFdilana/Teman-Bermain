<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    /** Log aktivitas admin (PRD BR-RC-15). Hanya created_at. */
    public $timestamps = false;

    protected $fillable = ['admin_id', 'aksi', 'detail', 'ip'];

    protected $casts = ['created_at' => 'datetime'];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Catat aksi admin yang sedang login.
     */
    public static function catat(string $aksi, ?string $detail = null): void
    {
        static::create([
            'admin_id' => Auth::id(),
            'aksi' => $aksi,
            'detail' => $detail,
            'ip' => request()->ip(),
        ]);
    }

    /** Label Indonesia untuk aksi. */
    public static function label(string $aksi): string
    {
        return match ($aksi) {
            'verifikasi_order' => '✅ Verifikasi order',
            'tolak_order' => '❌ Tolak order',
            'ubah_harga' => '💰 Ubah harga',
            'publish_produk' => '📢 Publish produk',
            'arsip_produk' => '🗄️ Arsip produk',
            'backup' => '💾 Backup',
            'cabut_token' => '🔌 Cabut install token',
            default => $aksi,
        };
    }
}
