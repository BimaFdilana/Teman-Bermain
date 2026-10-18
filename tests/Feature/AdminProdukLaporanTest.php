<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\SalesTarget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProdukLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function produk(string $tipe = 'ebook'): Product
    {
        return Product::create([
            'tipe' => $tipe, 'judul' => 'P-'.uniqid(), 'slug' => 'p-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
    }

    public function test_update_harga_stok_status(): void
    {
        $admin = $this->admin();
        $p = $this->produk();

        $response = $this->actingAs($admin)->put(route('admin.produk.harga', $p), [
            'harga' => 25000, 'harga_coret' => 30000, 'stok' => 50, 'status' => 'tersedia',
        ]);

        $response->assertRedirect();
        $p->refresh();
        $this->assertEquals(25000, $p->harga);
        $this->assertEquals(30000, $p->harga_coret);
        $this->assertEquals(50, $p->stok);
    }

    public function test_harga_coret_harus_lebih_besar_dari_harga(): void
    {
        $p = $this->produk();
        $response = $this->actingAs($this->admin())->put(route('admin.produk.harga', $p), [
            'harga' => 25000, 'harga_coret' => 20000, 'status' => 'tersedia',
        ]);
        $response->assertSessionHasErrors('harga_coret');
    }

    public function test_buat_bundle_minimal_2_produk(): void
    {
        $admin = $this->admin();
        $a = $this->produk(); $b = $this->produk('miniapp');

        $response = $this->actingAs($admin)->post(route('admin.produk.bundle.store'), [
            'judul' => 'Bundle Hebat', 'harga' => 40000, 'status' => 'tersedia',
            'items' => [$a->id, $b->id],
        ]);

        $response->assertRedirect(route('admin.produk.index', ['tab' => 'bundle']));
        $bundle = Product::where('judul', 'Bundle Hebat')->first();
        $this->assertEquals(2, $bundle->bundle->items->count());
    }

    public function test_bundle_ditolak_bila_kurang_dari_2_produk(): void
    {
        $a = $this->produk();
        $response = $this->actingAs($this->admin())->post(route('admin.produk.bundle.store'), [
            'judul' => 'Bundle Kurang', 'harga' => 40000, 'status' => 'draft',
            'items' => [$a->id],
        ]);
        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('products', ['judul' => 'Bundle Kurang']);
    }

    public function test_laporan_tampil_dan_target_tersimpan(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $p = $this->produk();
        Order::create([
            'kode_order' => 'RCB-1', 'user_id' => $user->id, 'product_id' => $p->id,
            'total_bayar' => 50000, 'status' => 'disetujui',
        ]);

        $this->actingAs($admin)->get(route('admin.laporan.index'))->assertOk()->assertSee('50.000', false);

        $key = now()->format('Y-m');
        $this->actingAs($admin)->post(route('admin.laporan.target'), [
            'tipe' => 'bulan', 'periode' => $key, 'target' => 1000000,
        ])->assertRedirect();

        $this->assertDatabaseHas('sales_targets', ['tipe' => 'bulan', 'periode' => $key, 'target' => 1000000]);
        $this->actingAs($admin)->get(route('admin.laporan.index'))->assertSee('1.000.000', false);
    }

    public function test_export_excel_dan_pdf_berhasil(): void
    {
        $admin = $this->admin();

        $excel = $this->actingAs($admin)->get(route('admin.laporan.excel'));
        $excel->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml', $excel->headers->get('Content-Type')
        );

        $pdf = $this->actingAs($admin)->get(route('admin.laporan.pdf'));
        $pdf->assertOk();
        $this->assertEquals('application/pdf', $pdf->headers->get('Content-Type'));
    }

    public function test_landing_teks_dan_testimoni(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.landing.teks'), [
            'hero_headline' => 'Headline Baru',
            'wa_admin' => '6281234567890',
        ])->assertRedirect();
        $this->assertEquals('Headline Baru', \App\Models\Setting::get('hero_headline'));

        $this->actingAs($admin)->post(route('admin.landing.testimoni.store'), [
            'nama' => 'Bunda Rina', 'isi' => 'Anak saya suka!', 'aktif' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('testimonials', ['nama' => 'Bunda Rina', 'aktif' => true]);
    }

    public function test_landing_paket_maks_3(): void
    {
        $bundles = collect([1, 2, 3, 4])->map(fn () => Product::create([
            'tipe' => 'bundle', 'judul' => 'B-'.uniqid(), 'slug' => 'b-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]));

        $response = $this->actingAs($this->admin())->post(route('admin.landing.paket'), [
            'paket' => $bundles->pluck('id')->toArray(),
        ]);
        $response->assertSessionHasErrors('paket');
    }
}
