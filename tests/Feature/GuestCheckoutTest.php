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
 * Alur guest checkout via Iklan (Laporan Final 19 Okt 2026).
 */
class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function produk(string $slug = 'ebook-1', int $harga = 50000): Product
    {
        return Product::create([
            'judul' => 'Ebook '.$slug, 'slug' => $slug, 'tipe' => 'ebook',
            'deskripsi' => 'x', 'harga' => $harga, 'status' => 'tersedia',
        ]);
    }

    public function test_tamu_bisa_checkout_tanpa_login(): void
    {
        $p = $this->produk();

        $this->get('/p/'.$p->slug.'/pesan')->assertOk();

        $response = $this->post('/p/checkout', [
            'nama' => 'Budi Tamu',
            'email' => 'budi@example.com',
            'whatsapp' => '081234567890',
            'metode_bayar' => 'qris',
        ]);

        $this->assertDatabaseHas('orders', [
            'guest_nama' => 'Budi Tamu',
            'guest_email' => 'budi@example.com',
            'guest_wa' => '6281234567890',
            'metode_bayar' => 'qris',
            'user_id' => null,
        ]);
        $order = Order::where('guest_email', 'budi@example.com')->first();
        $this->assertCount(1, $order->items);
        $this->assertEquals($p->id, $order->items->first()->product_id);
        $response->assertRedirect(route('checkout.bayar', $order->kode_order));
    }

    public function test_cart_bisa_multi_produk(): void
    {
        $p1 = $this->produk('ebook-1', 50000);
        $p2 = $this->produk('ebook-2', 75000);

        $this->get('/p/'.$p1->slug.'/pesan');
        $this->post('/p/cart/tambah/'.$p2->slug);

        $response = $this->post('/p/checkout', [
            'nama' => 'Budi', 'email' => 'budi@example.com',
            'whatsapp' => '081234567890', 'metode_bayar' => 'qris',
        ]);

        $order = Order::where('guest_email', 'budi@example.com')->first();
        $this->assertCount(2, $order->items);
        $this->assertEquals(125000, $order->total_bayar);
        $response->assertRedirect(route('checkout.bayar', $order->kode_order));
    }

    public function test_tamu_bisa_buka_halaman_bayar_order_sendiri(): void
    {
        $p = $this->produk();
        $this->get('/p/'.$p->slug.'/pesan');
        $this->post('/p/checkout', [
            'nama' => 'Budi', 'email' => 'budi@example.com',
            'whatsapp' => '081234567890', 'metode_bayar' => 'qris',
        ]);
        $order = Order::where('guest_email', 'budi@example.com')->first();

        $this->get('/bayar/'.$order->kode_order)
            ->assertOk()
            ->assertSee('Verifikasi Pembayaran');
    }

    public function test_tamu_tidak_bisa_buka_bayar_order_orang_lain(): void
    {
        $user = User::factory()->create();
        $p = $this->produk();
        $order = Order::create([
            'kode_order' => 'RCB-20260101-TST9', 'user_id' => $user->id,
            'product_id' => $p->id, 'total_bayar' => 50000,
            'batas_bayar' => now()->addDay(),
        ]);

        $this->get('/bayar/'.$order->kode_order)->assertNotFound();
    }

    public function test_upload_bukti_tamu_ke_halaman_terima_kasih(): void
    {
        Storage::fake('public');
        $p = $this->produk();
        $this->get('/p/'.$p->slug.'/pesan');
        $this->post('/p/checkout', [
            'nama' => 'Budi', 'email' => 'budi@example.com',
            'whatsapp' => '081234567890', 'metode_bayar' => 'qris',
        ]);
        $order = Order::where('guest_email', 'budi@example.com')->first();

        $response = $this->post(route('checkout.bukti', $order->kode_order), [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertRedirect(route('checkout.terima-kasih', $order->kode_order));
        $this->get(route('checkout.terima-kasih', $order->kode_order))
            ->assertOk()
            ->assertSee('Terima kasih sudah membeli');
    }

    public function test_approve_multi_item_buat_kode_per_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $p1 = $this->produk('ebook-1', 50000);
        $p2 = $this->produk('ebook-2', 75000);

        $order = Order::create([
            'kode_order' => 'RCB-20260101-MULT', 'guest_nama' => 'Budi',
            'guest_email' => 'budi@example.com', 'guest_wa' => '6281234567890',
            'total_bayar' => 125000, 'status' => 'verifikasi',
            'batas_bayar' => now()->addDay(),
        ]);
        $order->items()->create(['product_id' => $p1->id, 'harga_saat_beli' => 50000, 'qty' => 1]);
        $order->items()->create(['product_id' => $p2->id, 'harga_saat_beli' => 75000, 'qty' => 1]);

        $this->actingAs($admin)->post(route('admin.pembayaran.approve', $order));

        $order->refresh();
        $this->assertEquals('disetujui', $order->status);
        $this->assertCount(2, $order->redeemCodes);
        $this->assertEquals(
            [$p1->id, $p2->id],
            $order->redeemCodes->pluck('product_id')->sort()->values()->all()
        );
    }

    public function test_order_tamu_terhubung_saat_login_google_email_cocok(): void
    {
        $order = Order::create([
            'kode_order' => 'RCB-20260101-LINK', 'guest_nama' => 'Budi',
            'guest_email' => 'budi@example.com', 'guest_wa' => '6281234567890',
            'total_bayar' => 50000, 'batas_bayar' => now()->addDay(),
        ]);
        $user = User::factory()->create(['email' => 'budi@example.com']);

        // Simulasi logika GoogleController: hubungkan order tamu via email
        Order::whereNull('user_id')->where('guest_email', $user->email)->update(['user_id' => $user->id]);

        $this->assertEquals($user->id, $order->fresh()->user_id);
    }

    public function test_checkout_va_menyimpan_bank(): void
    {
        \App\Models\Setting::set('va_numbers', json_encode(['BCA' => '8801234567890']));
        $p = $this->produk();
        $this->get('/p/'.$p->slug.'/pesan');

        $this->post('/p/checkout', [
            'nama' => 'Budi', 'email' => 'budi@example.com',
            'whatsapp' => '081234567890', 'metode_bayar' => 'va', 'bank_va' => 'BCA',
        ]);

        $order = Order::where('guest_email', 'budi@example.com')->first();
        $this->assertEquals('va', $order->metode_bayar);
        $this->assertEquals('BCA', $order->bank_va);

        $this->get('/bayar/'.$order->kode_order)
            ->assertOk()
            ->assertSee('8801234567890');
    }
}
