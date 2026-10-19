<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'kode_order', 'user_id', 'product_id', 'total_bayar', 'discount_code_id',
        'status', 'bukti_path', 'upload_ke', 'alasan_tolak', 'batas_bayar',
        'guest_nama', 'guest_email', 'guest_wa', 'metode_bayar', 'bank_va',
    ];

    protected $casts = [
        'batas_bayar' => 'datetime',
    ];

    public const ST_PEMBAYARAN = 'pembayaran';
    public const ST_VERIFIKASI = 'verifikasi';
    public const ST_DITOLAK = 'ditolak';
    public const ST_DISETUJUI = 'disetujui';
    public const ST_KADALUARSA = 'kadaluarsa';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function redeemCode(): HasOne
    {
        return $this->hasOne(RedeemCode::class);
    }

    /** Satu order bisa banyak item (cart) → banyak kode redeem. */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function redeemCodes(): HasMany
    {
        return $this->hasMany(RedeemCode::class);
    }

    /** Nama pembeli: dari akun, atau data tamu. */
    public function namaPembeli(): string
    {
        return $this->user?->name ?? $this->guest_nama ?? '—';
    }

    public function emailPembeli(): ?string
    {
        return $this->user?->email ?? $this->guest_email;
    }

    public function waPembeli(): ?string
    {
        return $this->user?->whatsapp ?? $this->guest_wa;
    }

    public function labelMetode(): string
    {
        return match ($this->metode_bayar) {
            'va' => 'Virtual Account'.($this->bank_va ? " ({$this->bank_va})" : ''),
            default => 'QRIS',
        };
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            'pembayaran' => 'Menunggu pembayaran',
            'verifikasi' => 'Menunggu verifikasi',
            'ditolak' => 'Ditolak',
            'disetujui' => 'Disetujui',
            'kadaluarsa' => 'Kadaluarsa',
            default => $this->status,
        };
    }

    public function warnaStatus(): string
    {
        return match ($this->status) {
            'pembayaran' => '#F3A712',
            'verifikasi' => '#0077B6',
            'ditolak' => '#e74c3c',
            'disetujui' => '#167941',
            'kadaluarsa' => '#6b7280',
            default => '#6b7280',
        };
    }

    public function totalRupiah(): string
    {
        return 'Rp'.number_format($this->total_bayar, 0, ',', '.');
    }

    /** Boleh upload (ulang) bukti? Maksimal 1x upload ulang setelah ditolak. */
    public function bolehUploadBukti(): bool
    {
        if ($this->status === self::ST_PEMBAYARAN) {
            return true;
        }
        return $this->status === self::ST_DITOLAK && $this->upload_ke < 2;
    }
}
