<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function buatProduk(): void
    {
        Product::create([
            'tipe' => 'ebook', 'judul' => 'Ebook Membaca', 'slug' => 'ebook-membaca',
            'deskripsi' => 'Deskripsi', 'harga' => 25000, 'jenjang' => 'PAUD', 'status' => 'tersedia',
        ]);
        Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Berhitung', 'slug' => 'game-berhitung',
            'deskripsi' => 'Deskripsi', 'harga' => 30000, 'jenjang' => 'TK', 'status' => 'tersedia',
        ]);
    }

    public function test_katalog_menampilkan_semua_produk_tersedia(): void
    {
        $this->buatProduk();
        $response = $this->get('/produk');
        $response->assertOk();
        $response->assertSee('Ebook Membaca');
        $response->assertSee('Game Berhitung');
    }

    public function test_katalog_bisa_filter_tipe(): void
    {
        $this->buatProduk();
        $response = $this->get('/produk?tipe=ebook');
        $response->assertOk();
        $response->assertSee('Ebook Membaca');
        $response->assertDontSee('Game Berhitung');
    }

    public function test_katalog_bisa_cari_judul(): void
    {
        $this->buatProduk();
        $response = $this->get('/produk?q=berhitung');
        $response->assertSee('Game Berhitung');
        $response->assertDontSee('Ebook Membaca');
    }

    public function test_detail_produk_tampil(): void
    {
        $this->buatProduk();
        $response = $this->get('/produk/ebook-membaca');
        $response->assertOk();
        $response->assertSee('Ebook Membaca');
        $response->assertSee('Rp25.000');
    }

    public function test_detail_produk_miniapp_menampilkan_cara_akses(): void
    {
        $this->buatProduk();
        $response = $this->get('/produk/game-berhitung');
        $response->assertOk();
        $response->assertSee('Cara Akses MiniApp');
    }

    public function test_detail_produk_tidak_ditemukan_404(): void
    {
        $response = $this->get('/produk/tidak-ada');
        $response->assertNotFound();
    }
}
