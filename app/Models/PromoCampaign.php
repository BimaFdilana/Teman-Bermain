<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PromoCampaign extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_AKTIF = 'aktif';

    protected $fillable = [
        'judul', 'subjudul', 'slug', 'deskripsi', 'gambar_path',
        'video_url', 'ajakan', 'materi', 'testimoni_ss',
        'produk_primary_id', 'tampilan_secondary', 'urutan_konten', 'section_sembunyi',
        'mulai_at', 'berakhir_at', 'status',
    ];

    protected $casts = [
        'mulai_at' => 'datetime',
        'berakhir_at' => 'datetime',
        'materi' => 'array',
        'testimoni_ss' => 'array',
        'urutan_konten' => 'array',
        'section_sembunyi' => 'array',
    ];

    /** Daftar section default halaman iklan (berurutan). */
    public const SECTIONS = [
        'banner' => '🖼️ Banner',
        'countdown' => '⏳ Hitungan Mundur',
        'deskripsi' => '📝 Deskripsi',
        'produk_utama' => '⭐ Produk Utama',
        'produk_tambahan' => '🎁 Produk Tambahan',
        'video_ajakan' => '🎬 Video & Ajakan',
        'materi' => '📖 Materi Ebook',
        'testimoni' => '💬 Testimoni',
        'harga_cta' => '💰 Harga + Tombol Pesan',
    ];

    /** Produk utama (primary). */
    public function produkPrimary(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'produk_primary_id');
    }

    /** Produk tambahan (secondary) via pivot. */
    public function produkSecondary(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promo_campaign_product')
            ->withPivot('urutan')
            ->orderByPivot('urutan');
    }

    /** Urutan section efektif (default bila belum diatur). */
    public function urutanKontenEfektif(): array
    {
        $urut = $this->urutan_konten ?? [];
        $valid = array_values(array_intersect($urut, array_keys(self::SECTIONS)));
        foreach (array_keys(self::SECTIONS) as $key) {
            if (! in_array($key, $valid)) {
                $valid[] = $key;
            }
        }

        return $valid;
    }

    /** Apakah section ditampilkan? */
    public function sectionTampil(string $key): bool
    {
        return ! in_array($key, $this->section_sembunyi ?? []);
    }

    /**
     * URL publik screenshot testimoni (array), mengikuti urutan pilihan.
     * testimoni_ss berisi ID dari galeri testimonial_images.
     *
     * @return string[]
     */
    public function testimoniSsUrls(): array
    {
        $ids = array_map('intval', $this->testimoni_ss ?? []);
        if ($ids === []) {
            return [];
        }
        $paths = TestimonialImage::whereIn('id', $ids)->pluck('path', 'id');
        $urls = [];
        foreach ($ids as $id) {
            if (isset($paths[$id])) {
                $urls[] = asset('storage/'.$paths[$id]);
            }
        }

        return $urls;
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
