<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * UAT Fase 6: alur lengkap pembeli bundle dari daftar sampai main.
 */
class UatAlurLengkapTest extends TestCase
{
    use RefreshDatabase;

    public function test_alur_lengkap_bundle(): void
    {
        Storage::fake('local');

        // 1. Setup: ebook + miniapp + bundle (dengan file)
        Storage::disk('local')->put('ebooks/uat.pdf', '%PDF-1.4');
        Storage::disk('local')->put('miniapps/uat.html', '<html><body>game</body></html>');

        $ebook = Product::create(['tipe' => 'ebook', 'judul' => 'Ebook UAT', 'slug' => 'ebook-uat',
            'deskripsi' => 'd', 'harga' => 20000, 'status' => 'tersedia']);
        $ebook->ebook()->create(['pdf_path' => 'ebooks/uat.pdf']);
        $miniapp = Product::create(['tipe' => 'miniapp', 'judul' => 'Game UAT', 'slug' => 'game-uat',
            'deskripsi' => 'd', 'harga' => 30000, 'status' => 'tersedia']);
        $miniapp->miniapp()->create(['html_path' => 'miniapps/uat.html', 'pwa_name' => 'Game UAT']);
        $bundle = Product::create(['tipe' => 'bundle', 'judul' => 'Bundle UAT', 'slug' => 'bundle-uat',
            'deskripsi' => 'd', 'harga' => 40000, 'status' => 'tersedia']);
        $bundle->bundle()->create();
        $bundle->bundle->items()->sync([$ebook->id, $miniapp->id]);

        $admin = User::factory()->create(['role' => 'admin']);

        // 2. Pengguna daftar via Google (register form dihapus 2026-10-19)
        $user = User::factory()->create([
            'name' => 'Orang Tua', 'email' => 'ortu@uat.test',
            'google_id' => 'google-uat-123', 'password' => null,
        ]);

        // 3. Lihat katalog & detail bundle
        $this->actingAs($user)->get('/produk')->assertOk()->assertSee('Bundle UAT', false);
        $this->actingAs($user)->get('/produk/bundle-uat')->assertOk()->assertSee('Ebook UAT', false);

        // 4. Checkout bundle
        $this->actingAs($user)->post(route('checkout.store', 'bundle-uat'))->assertRedirect();
        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('pembayaran', $order->status);
        $this->assertEquals(40000, $order->total_bayar);

        // 5. Halaman bayar + upload bukti
        $this->actingAs($user)->get(route('checkout.bayar', $order->kode_order))->assertOk();
        $this->actingAs($user)->post(route('checkout.bukti', $order->kode_order), [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect();
        $this->assertEquals('verifikasi', $order->fresh()->status);

        // 6. Admin setujui -> kode redeem terbit
        $this->actingAs($admin)->post(route('admin.pembayaran.approve', $order))
            ->assertRedirect();
        $order->refresh();
        $this->assertEquals('disetujui', $order->status);
        $kode = $order->redeemCode;
        $this->assertNotNull($kode);

        // 7. Pengguna redeem -> dapat bundle + isinya
        $this->actingAs($user)->post(route('redeem.store'), ['code' => $kode->kode])
            ->assertRedirect();
        $this->assertTrue($user->hasProduct($bundle->id));
        $this->assertTrue($user->hasProduct($ebook->id));
        $this->assertTrue($user->hasProduct($miniapp->id));
        $this->assertEquals('terpakai', $kode->fresh()->status);

        // 8. Baca ebook dari bundle
        $this->actingAs($user)->get(route('ebook.baca', $ebook))->assertOk();

        // 9. Mainkan miniapp dari bundle + manifest PWA
        $this->actingAs($user)->get(route('miniapp.main', $miniapp))->assertOk()
            ->assertSee('rel="manifest"', false);
        $manifest = $this->actingAs($user)->get(route('miniapp.manifest', $miniapp));
        $manifest->assertOk();
        $startUrl = $manifest->json('start_url');
        $this->assertStringContainsString('/pwa/', $startUrl);

        // 10. Simulasi buka dari HP terpasang: token -> HTML game langsung
        $this->post('/logout');
        $token = basename($startUrl);
        $this->get($startUrl)->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=utf-8');

        // 11. Riwayat pesanan tercatat
        $this->actingAs($user)->get(route('checkout.riwayat'))->assertOk()
            ->assertSee($order->kode_order, false);
    }
}
