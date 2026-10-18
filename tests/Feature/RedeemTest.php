<?php

namespace Tests\Feature;

use App\Models\Bundle;
use App\Models\Order;
use App\Models\Product;
use App\Models\RedeemCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedeemTest extends TestCase
{
    use RefreshDatabase;

    private function produk(array $over = []): Product
    {
        return Product::create(array_merge([
            'tipe' => 'ebook', 'judul' => 'Ebook '.uniqid(), 'slug' => 'ebook-'.uniqid(),
            'deskripsi' => 'Deskripsi', 'harga' => 50000, 'status' => 'tersedia',
        ], $over));
    }

    private function orderDisetujui(User $user, Product $p): Order
    {
        $order = Order::create([
            'kode_order' => 'RCB-'.uniqid(), 'user_id' => $user->id,
            'product_id' => $p->id, 'total_bayar' => $p->harga,
            'status' => Order::ST_DISETUJUI, 'batas_bayar' => now()->addDay(),
        ]);
        RedeemCode::create([
            'kode' => RedeemCode::generate(), 'order_id' => $order->id,
            'product_id' => $p->id,
        ]);
        return $order;
    }

    public function test_redeem_kode_valid_memberi_entitlement(): void
    {
        $user = User::factory()->create();
        $p = $this->produk();
        $order = $this->orderDisetujui($user, $p);
        $kode = $order->redeemCode->kode;

        $response = $this->actingAs($user)->post(route('redeem.store'), ['code' => strtolower($kode)]);

        $response->assertRedirect(route('home'));
        $this->assertTrue($user->hasProduct($p->id));
        $this->assertEquals('terpakai', $order->redeemCode->fresh()->status);
    }

    public function test_redeem_kode_salah_gagal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('redeem.store'), ['code' => 'RC-XXXX-XXXX']);

        $response->assertSessionHas('gagal');
        $this->assertEquals(0, $user->entitlements()->count());
    }

    public function test_redeem_kode_terpakai_gagal(): void
    {
        $user = User::factory()->create();
        $p = $this->produk();
        $order = $this->orderDisetujui($user, $p);
        $order->redeemCode->update(['status' => 'terpakai']);

        $response = $this->actingAs($user)->post(route('redeem.store'), ['code' => $order->redeemCode->kode]);

        $response->assertSessionHas('gagal');
    }

    public function test_redeem_bundle_memberi_semua_isi(): void
    {
        $user = User::factory()->create();
        $ebook = $this->produk(['tipe' => 'ebook']);
        $miniapp = $this->produk(['tipe' => 'miniapp']);
        $bundle = $this->produk(['tipe' => 'bundle', 'judul' => 'Paket Uji', 'slug' => 'paket-uji']);
        $b = Bundle::create(['product_id' => $bundle->id]);
        $b->items()->attach([$ebook->id, $miniapp->id]);

        $order = $this->orderDisetujui($user, $bundle);

        $this->actingAs($user)->post(route('redeem.store'), ['code' => $order->redeemCode->kode]);

        $this->assertTrue($user->hasProduct($bundle->id));
        $this->assertTrue($user->hasProduct($ebook->id));
        $this->assertTrue($user->hasProduct($miniapp->id));
    }
}
