<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TestimonialImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Gerbang harga: produk tanpa harga (harga = 0) tidak tampil di publik
 * dan tidak bisa dibuatkan iklan. Harga diatur di Manage Produk.
 */
class GerbangHargaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function buatProduk(int $harga, string $judul = 'Ebook Gerbang Unik'): Product
    {
        return Product::create([
            'tipe' => 'ebook', 'judul' => $judul, 'slug' => 'gerbang-'.uniqid(),
            'deskripsi' => 'x', 'harga' => $harga, 'jenjang' => 'PAUD', 'status' => 'tersedia',
        ]);
    }

    public function test_produk_tanpa_harga_sembunyi_dari_landing_dan_katalog(): void
    {
        $gratis = $this->buatProduk(0);

        $this->get(route('landing'))->assertDontSee('Ebook Gerbang Unik');
        $this->get(route('produk.index'))->assertDontSee('Ebook Gerbang Unik');
        $this->get(route('produk.show', $gratis->slug))->assertNotFound();

        // Setelah diberi harga lewat Manage Produk -> tampil
        $this->actingAs($this->admin())->put(route('admin.produk.harga', $gratis), [
            'harga' => 30000, 'stok' => null, 'status' => 'tersedia',
        ])->assertRedirect();

        $this->get(route('produk.index'))->assertSee('Ebook Gerbang Unik');
        $this->get(route('produk.show', $gratis->slug))->assertOk();
    }

    public function test_produk_tanpa_harga_tidak_bisa_jadi_produk_iklan(): void
    {
        $gratis = $this->buatProduk(0, 'Produk Belum Berharga');
        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $ids[] = TestimonialImage::create(['path' => 'testimoni/g-'.uniqid().'.jpg'])->id;
        }

        $response = $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Promo Gerbang',
            'produk_primary_id' => $gratis->id,
            'tampilan_secondary' => 'card',
            'testimoni_ids' => $ids,
            'berakhir_at' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'aktif',
        ]);

        $response->assertSessionHasErrors('produk_primary_id');
        $this->assertDatabaseMissing('promo_campaigns', ['judul' => 'Promo Gerbang']);
    }

    public function test_form_iklan_tidak_menawarkan_produk_tanpa_harga(): void
    {
        $this->buatProduk(0, 'Produk Belum Berharga');
        $this->buatProduk(25000, 'Produk Sudah Berharga');

        $res = $this->actingAs($this->admin())->get(route('admin.iklan.create'));

        $res->assertOk();
        $res->assertDontSee('Produk Belum Berharga');
        $res->assertSee('Produk Sudah Berharga');
    }
}
