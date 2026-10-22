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

    public function test_manifest_punya_id_dan_purpose_ikon(): void
    {
        $user = User::factory()->create();
        $p = Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Uji', 'slug' => 'game-uji-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Miniapp::create(['product_id' => $p->id, 'html_path' => 'miniapps/uji.html', 'pwa_name' => 'Game Uji']);
        Entitlement::create(['user_id' => $user->id, 'product_id' => $p->id]);

        $res = $this->actingAs($user)->get(route('miniapp.manifest', $p));

        $res->assertOk();
        $data = $res->json();
        $this->assertSame($data['start_url'], $data['id']);
        $this->assertStringContainsString('/pwa/', $data['start_url']);
        $this->assertSame('standalone', $data['display']);
        $this->assertSame('any', $data['icons'][0]['purpose']);
        $this->assertSame('192x192', $data['icons'][0]['sizes']);
        $this->assertSame('512x512', $data['icons'][1]['sizes']);
    }

    public function test_launch_injeksi_meta_ios_dan_cabut_manifest_bawaan_game(): void
    {
        \Illuminate\Support\Facades\Storage::disk('local')->put(
            'miniapps/uji2.html',
            '<html><head><link rel="manifest" href="manifest.webmanifest"></head><body>game</body></html>'
        );
        $user = User::factory()->create();
        $p = Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Uji', 'slug' => 'game-uji-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Miniapp::create(['product_id' => $p->id, 'html_path' => 'miniapps/uji2.html', 'pwa_name' => 'Game Uji']);
        Entitlement::create(['user_id' => $user->id, 'product_id' => $p->id]);
        $token = \App\Models\InstallToken::untuk($user, $p);

        $res = $this->get(route('pwa.launch', ['token' => $token->token]));

        $res->assertOk();
        // Manifest bawaan game dicabut; hanya manifest sistem yang tersisa
        $res->assertDontSee('manifest.webmanifest', false);
        $res->assertSee('miniapp/'.$p->id.'/manifest.json', false);
        // Meta iOS terinjeksi
        $res->assertSee('apple-touch-icon', false);
        $res->assertSee('apple-mobile-web-app-capable', false);
        $res->assertSee('apple-mobile-web-app-title', false);
        // Panduan install sadar platform (Android + iOS)
        $res->assertSee('rc-panduan-android', false);
        $res->assertSee('rc-panduan-ios', false);
        $res->assertSee('Tambah ke Layar Utama', false);

        \Illuminate\Support\Facades\Storage::disk('local')->delete('miniapps/uji2.html');
    }
}
