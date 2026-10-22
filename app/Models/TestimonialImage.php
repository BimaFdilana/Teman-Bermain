<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestimonialImage extends Model
{
    protected $fillable = ['path'];

    /** URL publik gambar. */
    public function url(): string
    {
        return asset('storage/'.$this->path);
    }

    /** Berapa iklan yang sedang memakai gambar ini. */
    public function jumlahPemakaian(): int
    {
        return PromoCampaign::whereJsonContains('testimoni_ss', $this->id)->count();
    }

    /** Apakah gambar sedang dipakai minimal satu iklan. */
    public function sedangDipakai(): bool
    {
        return $this->jumlahPemakaian() > 0;
    }
}
