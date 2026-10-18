<?php

namespace Tests\Feature;

use App\Models\Entitlement;
use App\Models\Miniapp;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiniappPwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_worker_tersedia_sebagai_javascript(): void
    {
        $res = $this->get('/sw-miniapp.js');

        $res->assertOk();
        $this->assertStringContainsString('javascript', $res->headers->get('Content-Type'));
        $this->assertStringContainsString("addEventListener('fetch'", $res->getContent());
        $this->assertStringContainsString("addEventListener('install'", $res->getContent());
        $this->assertStringContainsString('/pwa/offline', $res->getContent());
    }

    public function test_halaman_offline_tersedia_tanpa_login(): void
    {
        $this->get('/pwa/offline')->assertOk();
    }

    public function test_halaman_player_mendaftarkan_service_worker(): void
    {
        $user = User::factory()->create();
        $p = Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Uji', 'slug' => 'game-uji-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Miniapp::create(['product_id' => $p->id, 'html_path' => 'miniapps/uji.html', 'pwa_name' => 'Game Uji']);
        Entitlement::create(['user_id' => $user->id, 'product_id' => $p->id]);

        $res = $this->actingAs($user)->get(route('miniapp.main', $p));

        $res->assertOk();
        $res->assertSee('serviceWorker', false);
        $res->assertSee('/sw-miniapp.js', false);
    }
}
