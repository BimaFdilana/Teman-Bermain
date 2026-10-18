<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Miniapp extends Model
{
    protected $fillable = ['product_id', 'html_path', 'pwa_name', 'icon_path', 'versi'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
