<?php

namespace Tests\Feature;

use App\Models\Entitlement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman MiniApp customer (menu baru, redesign krem 2026-10-23):
 * daftar MiniApp milik pengguna + bottom nav 4 menu.
 */
class MiniappIndexTest extends TestCase
{
    use RefreshDatabase;

    private function produk(string $tipe, string $judul): Product
    {
        return Product::create([
            'tipe' => $tipe, 'judul' => $judul, 'slug' => str($judul)->slug().'-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
    }

    public function test_halaman_miniapp_menampilkan_milik_sendiri_saja(): void
    {
        $user = User::factory()->create();
        $milikku = $this->produk('miniapp', 'Game Milikku');
        $this->produk('miniapp', 'Game Orang Lain');
        $this->produk('ebook', 'Ebook Bukan Game');

        Entitlement::create(['user_id' => $user->id, 'product_id' => $milikku->id]);

        $response = $this->actingAs($user)->get(route('miniapp.index'));

        $response->assertOk();
        $response->assertSee('Game Milikku');
        $response->assertDontSee('Game Orang Lain');
        $response->assertDontSee('Ebook Bukan Game');
        // Tombol Main + Install mengarah ke route yang benar
        $response->assertSee(route('miniapp.main', $milikku), false);
        $response->assertSee(route('miniapp.install', $milikku), false);
    }

    public function test_halaman_miniapp_kosong_untuk_user_baru(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('miniapp.index'))
            ->assertOk()
            ->assertSee('Belum ada MiniApp');
    }

    public function test_tamu_dialihkan_login_dari_halaman_miniapp(): void
    {
        $this->get(route('miniapp.index'))->assertRedirect(route('login'));
    }

    public function test_bottom_nav_empat_menu_dan_home_punya_seksi_ebook(): void
    {
        $user = User::factory()->create();
        $ebook = $this->produk('ebook', 'Ebook Di Home');
        Entitlement::create(['user_id' => $user->id, 'product_id' => $ebook->id]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee(route('miniapp.index'), false);
        $response->assertSee('MiniApp');
        $response->assertSee('Ebook Milikku');
        $response->assertSee('Ebook Di Home');
    }
}
