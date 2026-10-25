<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Conversions API: Purchase server saat admin menyetujui order. */
class MetaConversionsApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function orderVerifikasi(): Order
    {
        $user = User::factory()->create([
            'email' => 'pembeli@test.id', 'whatsapp' => '6281234567890',
        ]);
        $p = Product::create([
            'tipe' => 'ebook', 'judul' => 'Ebook Uji', 'slug' => 'ebook-uji',
            'deskripsi' => 'd', 'harga' => 50000, 'stok' => 10, 'status' => 'tersedia',
        ]);

        return Order::create([
            'kode_order' => 'RCB-'.uniqid(), 'user_id' => $user->id,
            'product_id' => $p->id, 'total_bayar' => 50000,
            'status' => Order::ST_VERIFIKASI, 'bukti_path' => 'bukti/x.jpg',
            'batas_bayar' => now()->addDay(),
        ]);
    }

    public function test_approve_mengirim_purchase_ke_meta(): void
    {
        config(['meta.capi_token' => 'token-uji']);
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1], 200)]);
        $order = $this->orderVerifikasi();

        $this->actingAs($this->admin())
            ->post(route('admin.pembayaran.approve', $order))
            ->assertRedirect(route('admin.pembayaran.show', $order));

        $this->assertEquals('disetujui', $order->fresh()->status);
        Http::assertSent(function ($request) use ($order) {
            $data = $request->data()['data'][0] ?? [];

            return str_contains($request->url(), 'graph.facebook.com')
                && ($data['event_name'] ?? null) === 'Purchase'
                && ($data['event_id'] ?? null) === 'purchase-'.$order->kode_order
                && ($data['custom_data']['value'] ?? null) == 50000
                && ($data['custom_data']['currency'] ?? null) === 'IDR'
                && ($data['user_data']['em'] ?? null) === hash('sha256', 'pembeli@test.id');
        });
    }

    public function test_tanpa_token_tidak_ada_pengiriman(): void
    {
        config(['meta.capi_token' => null]);
        Http::fake();
        $order = $this->orderVerifikasi();

        $this->actingAs($this->admin())
            ->post(route('admin.pembayaran.approve', $order))->assertRedirect();

        $this->assertEquals('disetujui', $order->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_meta_error_tidak_menggagalkan_approval(): void
    {
        config(['meta.capi_token' => 'token-uji']);
        Http::fake(['graph.facebook.com/*' => Http::response('error', 500)]);
        $order = $this->orderVerifikasi();

        $this->actingAs($this->admin())
            ->post(route('admin.pembayaran.approve', $order))
            ->assertRedirect(route('admin.pembayaran.show', $order));

        $this->assertEquals('disetujui', $order->fresh()->status);
    }
}
