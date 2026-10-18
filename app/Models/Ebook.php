<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ebook extends Model
{
    protected $fillable = ['product_id', 'pdf_path', 'jumlah_halaman'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
