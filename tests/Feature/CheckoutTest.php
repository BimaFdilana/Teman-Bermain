<?php

namespace Tests\Feature;

use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function produk(array $over = []): Product
    {
        return Product::create(array_merge([
            'tipe' => 'ebook', 'judul' => 'Ebook Uji', 'slug' => 'ebook-uji',
            'deskripsi' => 'Deskripsi', 'harga' => 50000, 'status' => 'tersedia',
        ], $over));
    }

    public function test_tamu_diarahkan_login_saat_checkout(): void
    {
        $p = $this->produk();
        $this->post("/checkout/{$p->slug}")->assertRedirect('/login');
    }

    public function test_user_bisa_buat_order(): void
    {
        $user = User::factory()->create();
        $p = $this->produk();

        $response = $this->actingAs($user)->post("/checkout/{$p->slug}");

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(50000, $order->total_bayar);
        $this->assertEquals('pembayaran', $order->status);
        $this->assertStringStartsWith('RCB-', $order->kode_order);
        $response->assertRedirect(route('checkout.bayar', $order->kode_order));
    }

    public function test_diskon_persen_diterapkan(): void
    {
        $user = User::factory()->create();
        $p = $this->produk();
        DiscountCode::create(['kode' => 'HEMAT20', 'tipe' => 'persen', 'nilai' => 20, 'aktif' => true]);

        $this->actingAs($user)->post("/checkout/{$p->slug}", ['kode_diskon' => 'hemat20']);

        $this->assertEquals(40000, Order::first()->total_bayar);
    }

    public function test_diskon_tidak_valid_ditolak(): void
    {
        $user = User::factory()->create();
        $p = $this->produk();

        $response = $this->actingAs($user)->post("/checkout/{$p->slug}", ['kode_diskon' => 'ngawur']);

        $response->assertRedirect(route('produk.show', $p->slug));
        $this->assertEquals(0, Order::count());
    }

    public function test_stok_habis_tidak_bisa_checkout(): void
    {
        $user = User::factory()->create();
        $p = $this->produk(['stok' => 0]);

        $this->actingAs($user)->post("/checkout/{$p->slug}");

        $this->assertEquals(0, Order::count());
    }

    public function test_upload_bukti_mengubah_status_verifikasi(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $p = $this->produk();
        $order = Order::create([
            'kode_order' => 'RCB-20260101-TST1', 'user_id' => $user->id,
            'product_id' => $p->id, 'total_bayar' => 50000,
            'batas_bayar' => now()->addDay(),
        ]);

        $response = $this->actingAs($user)->post(
            route('checkout.bukti', $order->kode_order),
            ['bukti' => UploadedFile::fake()->image('bukti.jpg')]
        );

        $order->refresh();
        $this->assertEquals('verifikasi', $order->status);
        $this->assertEquals(1, $order->upload_ke);
        Storage::disk('public')->assertExists($order->bukti_path);
        $response->assertRedirect(route('checkout.riwayat'));
    }

    public function test_upload_bukti_ditolak_bila_file_terlalu_besar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $p = $this->produk();
        $order = Order::create([
            'kode_order' => 'RCB-20260101-TST2', 'user_id' => $user->id,
            'product_id' => $p->id, 'total_bayar' => 50000,
            'batas_bayar' => now()->addDay(),
        ]);

        $besar = UploadedFile::fake()->image('besar.jpg')->size(3000); // 3MB

        $this->actingAs($user)->post(route('checkout.bukti', $order->kode_order), ['bukti' => $besar])
            ->assertSessionHasErrors('bukti');

        $this->assertEquals('pembayaran', $order->fresh()->status);
    }

    public function test_user_tidak_bisa_lihat_order_orang_lain(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $p = $this->produk();
        $order = Order::create([
            'kode_order' => 'RCB-20260101-TST3', 'user_id' => $a->id,
            'product_id' => $p->id, 'total_bayar' => 50000,
            'batas_bayar' => now()->addDay(),
        ]);

        $this->actingAs($b)->get(route('checkout.bayar', $order->kode_order))->assertNotFound();
    }
}
