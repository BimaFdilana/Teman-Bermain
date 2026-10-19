<?php

namespace Tests\Feature;

use App\Models\Entitlement;
use App\Models\InstallToken;
use App\Models\Miniapp;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class MiniappPlayerTest extends TestCase
{
    use RefreshDatabase;

    private string $htmlPath = 'miniapps/uji.html';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::disk('local')->put($this->htmlPath, '<html><body>game</body></html>');
    }

    protected function tearDown(): void
    {
        Storage::disk('local')->delete($this->htmlPath);
        parent::tearDown();
    }

    private function miniappMilik(User $user): Product
    {
        $p = Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Uji', 'slug' => 'game-uji-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Miniapp::create(['product_id' => $p->id, 'html_path' => $this->htmlPath, 'pwa_name' => 'Game Uji']);
        Entitlement::create(['user_id' => $user->id, 'product_id' => $p->id]);
        return $p;
    }

    public function test_pemilik_bisa_buka_player(): void
    {
        $user = User::factory()->create();
        $p = $this->miniappMilik($user);

        $response = $this->actingAs($user)->get(route('miniapp.main', $p));

        $response->assertOk();
        $response->assertSee('sandbox="allow-scripts"', false);
        $response->assertSee('rel="manifest"', false);
        $response->assertSee('Pasang di HP', false);
    }

    public function test_bukan_pemilik_ditolak(): void
    {
        $pemilik = User::factory()->create();
        $lain = User::factory()->create();
        $p = $this->miniappMilik($pemilik);

        $this->actingAs($lain)->get(route('miniapp.main', $p))->assertForbidden();
    }

    public function test_manifest_json_benar(): void
    {
        $user = User::factory()->create();
        $p = $this->miniappMilik($user);

        $response = $this->actingAs($user)->get(route('miniapp.manifest', $p));

        $response->assertOk();
        $data = $response->json();
        $this->assertEquals('Game Uji', $data['name']);
        $this->assertEquals('standalone', $data['display']);
        $this->assertStringContainsString('/pwa/', $data['start_url']);
        $this->assertNotEmpty($data['icons']);
    }

    public function test_install_token_unik_per_user_dan_produk(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $p = $this->miniappMilik($a);

        $t1 = InstallToken::untuk($a, $p);
        $t2 = InstallToken::untuk($a, $p); // idempoten
        $t3 = InstallToken::untuk($b, $p);

        $this->assertEquals($t1->id, $t2->id);
        $this->assertNotEquals($t1->token, $t3->token);
    }

    public function test_pwa_launch_sajikan_html_langsung(): void
    {
        $user = User::factory()->create();
        $p = $this->miniappMilik($user);
        $token = InstallToken::untuk($user, $p);

        $response = $this->get(route('pwa.launch', ['token' => $token->token]));

        // Start URL menyajikan HTML game langsung (bukan redirect ke web)
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
        $this->assertNotNull($token->fresh()->dipakai_terakhir_at);
    }

    public function test_pwa_launch_token_dicabut_ditolak(): void
    {
        $user = User::factory()->create();
        $p = $this->miniappMilik($user);
        $token = InstallToken::untuk($user, $p);
        $token->update(['dicabut' => true]);

        $this->get(route('pwa.launch', ['token' => $token->token]))->assertForbidden();
        $this->assertGuest();
    }

    public function test_pwa_launch_token_ngawur_ditolak(): void
    {
        $this->get(route('pwa.launch', ['token' => 'tidakada']))->assertForbidden();
    }

    public function test_file_miniapp_signed_url_berfungsi(): void
    {
        $user = User::factory()->create();
        $p = $this->miniappMilik($user);

        $url = URL::temporarySignedRoute('file.miniapp', now()->addMinutes(30), ['product' => $p->id]);
        $response = $this->actingAs($user)->get($url);

        $response->assertOk();
        $this->assertStringContainsString('text/html', $response->headers->get('Content-Type'));
    }

    public function test_file_miniapp_tanpa_signature_ditolak(): void
    {
        $user = User::factory()->create();
        $p = $this->miniappMilik($user);

        $this->actingAs($user)->get(route('file.miniapp', $p))->assertForbidden();
    }
}
