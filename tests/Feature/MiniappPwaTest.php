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
        // Fallback tanpa icon_path memakai ikon persegi bawaan yang ukurannya pasti
        $this->assertStringContainsString('pwa-icon-192.png', $data['icons'][0]['src']);
        $this->assertStringContainsString('pwa-icon-512.png', $data['icons'][1]['src']);
    }

    public function test_manifest_ikon_unggahan_dinyatakan_sesuai_ukuran_asli(): void
    {
        // Buat ikon unggahan 300x200 — manifest harus menyatakan 300x200, bukan 192/512 karangan
        $img = imagecreatetruecolor(300, 200);
        ob_start();
        imagepng($img);
        \Illuminate\Support\Facades\Storage::disk('public')->put('miniapp-icons/uji.png', ob_get_clean());
        imagedestroy($img);

        $user = User::factory()->create();
        $p = Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Ikon', 'slug' => 'game-ikon-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Miniapp::create(['product_id' => $p->id, 'html_path' => 'miniapps/uji.html', 'pwa_name' => 'Game Ikon', 'icon_path' => 'miniapp-icons/uji.png']);
        Entitlement::create(['user_id' => $user->id, 'product_id' => $p->id]);

        $res = $this->actingAs($user)->get(route('miniapp.manifest', $p));

        $res->assertOk();
        $icons = $res->json('icons');
        $this->assertCount(1, $icons);
        $this->assertSame('300x200', $icons[0]['sizes']);
    }

    public function test_token_dicabut_bangkit_lagi_saat_pemilik_install_ulang(): void
    {
        // Setelah token dicabut (atau aplikasi dicopot), pemilik yang klik
        // Install lagi harus mendapat token BARU yang aktif — bukan 403 abadi.
        $user = User::factory()->create();
        $p = Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Reinstall', 'slug' => 'game-reinstall-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Miniapp::create(['product_id' => $p->id, 'html_path' => 'miniapps/uji.html', 'pwa_name' => 'Game Reinstall']);
        Entitlement::create(['user_id' => $user->id, 'product_id' => $p->id]);
        \Illuminate\Support\Facades\Storage::disk('local')->put('miniapps/uji.html', '<html><body>game</body></html>');

        $lama = \App\Models\InstallToken::untuk($user, $p);
        $stringLama = $lama->token;
        $lama->update(['dicabut' => true]);

        // Tautan lama tetap mati
        $this->get(route('pwa.launch', ['token' => $stringLama]))->assertForbidden();

        // Pemilik klik Install lagi → token bangkit dengan string baru
        $res = $this->actingAs($user)->get(route('miniapp.install', $p));
        $baru = \App\Models\InstallToken::untuk($user, $p);
        $this->assertFalse($baru->dicabut);
        $this->assertNotSame($stringLama, $baru->token);
        $res->assertRedirect(route('pwa.launch', ['token' => $baru->token]));

        // Tautan baru bisa dibuka
        $this->get(route('pwa.launch', ['token' => $baru->token]))->assertOk();
    }

    public function test_manifest_bisa_diakses_tamu_dengan_token_install(): void
    {
        // Pengambil manifest Chrome tidak membawa sesi login — token adalah
        // satu-satunya otorisasi. Jalur ini pernah rusak: rute tertahan
        // middleware auth + userDariToken() crash (tipe User tidak diimpor).
        $user = User::factory()->create();
        $p = Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Token', 'slug' => 'game-token-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Miniapp::create(['product_id' => $p->id, 'html_path' => 'miniapps/uji.html', 'pwa_name' => 'Game Token']);
        Entitlement::create(['user_id' => $user->id, 'product_id' => $p->id]);
        $token = \App\Models\InstallToken::untuk($user, $p);

        $this->get(route('miniapp.manifest', [$p, 'token' => $token->token]))
            ->assertOk()
            ->assertJsonPath('display', 'standalone');
        $this->assertGuest();
    }

    public function test_manifest_tamu_tanpa_token_sah_ditolak(): void
    {
        $p = Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Token 2', 'slug' => 'game-token2-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Miniapp::create(['product_id' => $p->id, 'html_path' => 'miniapps/uji.html', 'pwa_name' => 'Game Token 2']);

        $this->get(route('miniapp.manifest', $p))->assertForbidden();
        $this->get(route('miniapp.manifest', [$p, 'token' => 'token-ngawur']))->assertForbidden();
    }

    public function test_launch_tombol_install_menunggu_sinyal_chrome(): void
    {
        \Illuminate\Support\Facades\Storage::disk('local')->put(
            'miniapps/uji3.html',
            '<html><head></head><body>game</body></html>'
        );
        $user = User::factory()->create();
        $p = Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Tunggu', 'slug' => 'game-tunggu-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Miniapp::create(['product_id' => $p->id, 'html_path' => 'miniapps/uji3.html', 'pwa_name' => 'Game Tunggu']);
        Entitlement::create(['user_id' => $user->id, 'product_id' => $p->id]);
        $token = \App\Models\InstallToken::untuk($user, $p);

        $res = $this->get(route('pwa.launch', ['token' => $token->token]));

        $res->assertOk();
        // Tombol menunggu sinyal beforeinstallprompt dulu (⏳ Menyiapkan) sebelum panel instruksi
        $res->assertSee('Menyiapkan', false);
        $res->assertSee('10000', false);
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
