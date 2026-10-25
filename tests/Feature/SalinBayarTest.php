<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tombol salin di halaman bayar: nomor VA + nominal (angka mentah, tanpa format).
 */
class SalinBayarTest extends TestCase
{
    use RefreshDatabase;

    private function order(string $metode, ?string $bank = null): Order
    {
        $produk = Product::create([
            'tipe' => 'ebook', 'judul' => 'Ebook Salin', 'slug' => 'ebook-salin-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 75000, 'status' => 'tersedia',
        ]);

        $order = Order::create([
            'kode_order' => 'RC-SALIN-'.uniqid(),
            'product_id' => $produk->id,
            'total_bayar' => 75000,
            'status' => Order::ST_PEMBAYARAN,
            'batas_bayar' => now()->addDay(),
            'guest_nama' => 'Tamu', 'guest_email' => 'tamu@test.id', 'guest_wa' => '6281234567890',
            'metode_bayar' => $metode, 'bank_va' => $bank,
        ]);
        $order->items()->create(['product_id' => $produk->id, 'harga_saat_beli' => 75000]);

        return $order;
    }

    public function test_halaman_bayar_va_punya_tombol_salin_nomor_va_dan_nominal(): void
    {
        Setting::set('va_numbers', json_encode(['BRI' => '8801 2345 6789']));
        $order = $this->order('va', 'BRI');

        $response = $this->withSession(['order_terakhir' => $order->id])
            ->get(route('checkout.bayar', $order->kode_order));

        $response->assertOk();
        $response->assertSee('Salin Nomor Rekening');
        // Nomor yang disalin = digit saja, tanpa spasi
        $response->assertSee('data-copy="880123456789"', false);
        $response->assertSee('Salin Nominal');
        $response->assertSee('data-copy="75000"', false);
    }

    public function test_halaman_bayar_qris_punya_tombol_salin_nominal_saja(): void
    {
        $order = $this->order('qris');

        $response = $this->withSession(['order_terakhir' => $order->id])
            ->get(route('checkout.bayar', $order->kode_order));

        $response->assertOk();
        $response->assertSee('Salin Nominal');
        $response->assertDontSee('Salin Nomor Rekening');
    }
}
