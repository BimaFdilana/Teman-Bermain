<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman promosi per produk (/p/{slug}) ala link-in-bio.
 */
class PromoTest extends TestCase
{
    use RefreshDatabase;

    private function buatProduk(array $over = []): Product
    {
        return Product::create(array_merge([
            'tipe' => 'ebook', 'judul' => 'Ebook Promo', 'slug' => 'ebook-promo',
            'deskripsi' => 'Deskripsi promo', 'harga' => 25000,
            'jenjang' => 'PAUD', 'status' => 'tersedia',
        ], $over));
    }

    public function test_halaman_promo_tampil_untuk_produk_tersedia(): void
    {
        $this->buatProduk();

        $response = $this->get('/p/ebook-promo');

        $response->assertOk();
        $response->assertSee('Ebook Promo');
        $response->assertSee('Pesan Sekarang');
        $response->assertSee('25.000');
    }

    public function test_halaman_promo_404_untuk_produk_draft(): void
    {
        $this->buatProduk(['status' => 'draft', 'slug' => 'ebook-draft']);

        $this->get('/p/ebook-draft')->assertNotFound();
    }

    public function test_tamu_tanpa_login_masuk_cart(): void
    {
        $this->buatProduk();

        $response = $this->get('/p/ebook-promo/pesan');

        // Laporan Final 19 Okt 2026: tanpa login, produk masuk cart
        $response->assertOk();
        $response->assertSee('Keranjang Pesanan');
        $this->assertContains(
            Product::where('slug', 'ebook-promo')->first()->id,
            session('cart')
        );
    }

    public function test_pengguna_login_masuk_cart_juga(): void
    {
        $this->buatProduk();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/p/ebook-promo/pesan');

        $response->assertOk();
        $response->assertSee('Keranjang Pesanan');
        $this->assertDatabaseMissing('orders', ['user_id' => $user->id]);
    }

    public function test_link_promo_ada_di_manage_produk(): void
    {
        $this->buatProduk();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.produk.index', ['tab' => 'ebook']));

        $response->assertOk();
        $response->assertSee(route('promo.show', 'ebook-promo'), false);
    }

    public function test_cover_url_dua_format_path(): void
    {
        $dariSeeder = $this->buatProduk(['slug' => 'a', 'cover_path' => 'images/covers/x.jpg']);
        $dariUpload = $this->buatProduk(['slug' => 'b', 'cover_path' => 'covers/y.jpg']);
        $tanpaCover = $this->buatProduk(['slug' => 'c', 'cover_path' => null]);

        $this->assertEquals(asset('images/covers/x.jpg'), $dariSeeder->coverUrl());
        $this->assertEquals(asset('storage/covers/y.jpg'), $dariUpload->coverUrl());
        $this->assertNull($tanpaCover->coverUrl());
    }
}
