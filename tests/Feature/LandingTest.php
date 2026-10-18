<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('Rumah Cahaya-bks');
    }

    public function test_landing_menampilkan_produk_tersedia(): void
    {
        Product::create([
            'tipe' => 'bundle', 'judul' => 'Paket Contoh', 'slug' => 'paket-contoh',
            'deskripsi' => 'Deskripsi', 'harga' => 50000, 'status' => 'tersedia',
        ]);
        Product::create([
            'tipe' => 'ebook', 'judul' => 'Ebook Draft', 'slug' => 'ebook-draft',
            'deskripsi' => 'Deskripsi', 'harga' => 20000, 'status' => 'draft',
        ]);

        $response = $this->get('/');
        $response->assertSee('Paket Contoh');
        $response->assertDontSee('Ebook Draft');
    }

    public function test_landing_menampilkan_testimoni_aktif(): void
    {
        Testimonial::create(['nama' => 'Bunda A', 'isi' => 'Bagus sekali', 'aktif' => true, 'urutan' => 1]);

        $response = $this->get('/');
        $response->assertSee('Bagus sekali');
    }

    public function test_halaman_harga_menampilkan_bundle(): void
    {
        Product::create([
            'tipe' => 'bundle', 'judul' => 'Paket Hemat', 'slug' => 'paket-hemat',
            'deskripsi' => 'Deskripsi', 'harga' => 49000, 'harga_coret' => 60000, 'status' => 'tersedia',
        ]);

        $response = $this->get('/harga');
        $response->assertOk();
        $response->assertSee('Paket Hemat');
    }
}
