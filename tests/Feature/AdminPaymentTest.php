<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\RedeemCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function orderVerifikasi(): Order
    {
        $user = User::factory()->create(['whatsapp' => '6281234567890']);
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

    public function test_bukan_admin_ditolak_403(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/pembayaran')->assertForbidden();
    }

    public function test_tamu_diarahkan_login(): void
    {
        $this->get('/admin/pembayaran')->assertRedirect('/login');
    }

    public function test_admin_bisa_lihat_daftar_verifikasi(): void
    {
        $this->orderVerifikasi();
        $this->actingAs($this->admin())->get('/admin/pembayaran')->assertOk();
    }

    public function test_admin_approve_membuat_kode_redeem(): void
    {
        $order = $this->orderVerifikasi();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.pembayaran.approve', $order));

        $order->refresh();
        $this->assertEquals('disetujui', $order->status);
        $this->assertNotNull($order->redeemCode);
        $this->assertMatchesRegularExpression('/^RC-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $order->redeemCode->kode);
        $this->assertEquals(9, $order->product->fresh()->stok); // stok berkurang
        $response->assertRedirect(route('admin.pembayaran.show', $order));
    }

    public function test_halaman_detail_menampilkan_link_wa(): void
    {
        $order = $this->orderVerifikasi();
        $this->actingAs($this->admin())->post(route('admin.pembayaran.approve', $order));

        $response = $this->actingAs($this->admin())->get(route('admin.pembayaran.show', $order));

        $response->assertOk();
        $response->assertSee('wa.me', false);
        $response->assertSee($order->redeemCode->fresh()->kode);
    }

    public function test_admin_reject_dengan_alasan(): void
    {
        $order = $this->orderVerifikasi();

        $response = $this->actingAs($this->admin())->post(
            route('admin.pembayaran.reject', $order),
            ['alasan_tolak' => 'Bukti tidak jelas']
        );

        $order->refresh();
        $this->assertEquals('ditolak', $order->status);
        $this->assertEquals('Bukti tidak jelas', $order->alasan_tolak);
        $this->assertNull(RedeemCode::first());
        $response->assertRedirect(route('admin.pembayaran.show', $order));
    }

    public function test_order_kadaluarsa_oleh_command(): void
    {
        $user = User::factory()->create();
        $p = Product::create([
            'tipe' => 'ebook', 'judul' => 'E', 'slug' => 'e', 'deskripsi' => 'd',
            'harga' => 10000, 'status' => 'tersedia',
        ]);
        $order = Order::create([
            'kode_order' => 'RCB-OLD', 'user_id' => $user->id, 'product_id' => $p->id,
            'total_bayar' => 10000, 'status' => 'pembayaran',
            'batas_bayar' => now()->subHour(),
        ]);

        $this->artisan('order:kadaluarsa')->assertSuccessful();

        $this->assertEquals('kadaluarsa', $order->fresh()->status);
    }
}
