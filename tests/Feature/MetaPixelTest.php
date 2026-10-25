<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PromoCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Meta Pixel basis (PageView) terpasang di halaman publik. */
class MetaPixelTest extends TestCase
{
    use RefreshDatabase;

    public function test_pixel_terpasang_di_landing_dan_halaman_iklan(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('connect.facebook.net/en_US/fbevents.js', false)
            ->assertSee('1645492726958429', false);

        $p = Product::create([
            'tipe' => 'ebook', 'judul' => 'Produk Pixel', 'slug' => 'produk-pixel',
            'deskripsi' => 'x', 'harga' => 25000, 'jenjang' => 'PAUD', 'status' => 'tersedia',
        ]);
        $k = PromoCampaign::create([
            'judul' => 'Promo Pixel', 'slug' => 'promo-pixel',
            'deskripsi' => 'x', 'produk_primary_id' => $p->id,
            'mulai_at' => now()->subDay(), 'berakhir_at' => now()->addDays(3),
            'status' => 'aktif',
        ]);

        $this->get(route('iklan.show', $k->slug))->assertOk()
            ->assertSee('1645492726958429', false);
    }
}
