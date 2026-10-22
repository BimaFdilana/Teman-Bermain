<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tombol melayang "Kembali ke Home" di halaman katalog/produk/harga:
 * tampil hanya untuk customer yang login (bukan tamu, bukan admin).
 */
class TombolHomeTest extends TestCase
{
    use RefreshDatabase;

    private function produk(): Product
    {
        return Product::create([
            'tipe' => 'ebook', 'judul' => 'Ebook Uji Tombol', 'slug' => 'ebook-uji-tombol-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
    }

    public function test_customer_melihat_tombol_kembali_ke_home(): void
    {
        $produk = $this->produk();
        $user = User::factory()->create();

        foreach ([route('produk.index'), route('produk.show', $produk->slug), route('harga')] as $url) {
            $this->actingAs($user)->get($url)
                ->assertOk()
                ->assertSee('Kembali ke Home')
                ->assertSee(route('home'), false);
        }
    }

    public function test_tamu_dan_admin_tidak_melihat_tombol(): void
    {
        $this->produk();

        $this->get(route('produk.index'))->assertOk()->assertDontSee('Kembali ke Home');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('produk.index'))
            ->assertOk()
            ->assertDontSee('Kembali ke Home');
    }
}
