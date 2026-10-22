<?php

namespace Tests\Feature;

use App\Models\InstallToken;
use App\Models\Miniapp;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tombol "Uji Install" (🚀) untuk admin: admin bisa memakai alur install
 * customer (langsung ke HTML game) tanpa harus punya entitlement —
 * untuk melihat & menguji langsung, bukan halaman preview.
 */
class AdminInstallMiniappTest extends TestCase
{
    use RefreshDatabase;

    private function miniapp(): Product
    {
        Storage::disk('local')->put('miniapps/admin-uji.html', '<html><head></head><body>game admin</body></html>');
        $p = Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Uji Admin', 'slug' => 'game-uji-admin-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Miniapp::create(['product_id' => $p->id, 'html_path' => 'miniapps/admin-uji.html', 'pwa_name' => 'Game Uji Admin']);

        return $p;
    }

    public function test_admin_bisa_install_langsung_tanpa_entitlement(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $p = $this->miniapp();

        $response = $this->actingAs($admin)->get(route('miniapp.install', $p));

        $token = InstallToken::untuk($admin, $p);
        $response->assertRedirect(route('pwa.launch', ['token' => $token->token]));

        // Halaman launch menyajikan HTML game langsung untuk token admin
        $this->get(route('pwa.launch', ['token' => $token->token]))
            ->assertOk()
            ->assertSee('game admin', false)
            ->assertSee('rc-install-fab', false);
    }

    public function test_admin_bisa_ambil_manifest(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $p = $this->miniapp();

        $res = $this->actingAs($admin)->get(route('miniapp.manifest', $p));

        $res->assertOk();
        $this->assertStringContainsString('/pwa/', $res->json('start_url'));
    }

    public function test_user_tanpa_entitlement_tetap_ditolak(): void
    {
        $user = User::factory()->create();
        $p = $this->miniapp();

        $this->actingAs($user)->get(route('miniapp.install', $p))->assertForbidden();
        $this->actingAs($user)->get(route('miniapp.manifest', $p))->assertForbidden();
    }

    public function test_tombol_uji_install_ada_di_daftar_miniapp_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $p = $this->miniapp();

        $this->actingAs($admin)->get(route('admin.miniapp.index'))
            ->assertOk()
            ->assertSee(route('miniapp.install', $p), false)
            ->assertSee('Uji install', false);
    }
}
