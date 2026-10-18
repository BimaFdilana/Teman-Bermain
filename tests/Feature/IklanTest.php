<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PromoCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Manage Iklan: halaman promo multi-produk (1-4) + hitungan mundur.
 */
class IklanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function buatProduk(string $slug, string $judul): Product
    {
        return Product::create([
            'tipe' => 'ebook', 'judul' => $judul, 'slug' => $slug,
            'deskripsi' => 'x', 'harga' => 25000, 'jenjang' => 'PAUD', 'status' => 'tersedia',
        ]);
    }

    private function buatKampanye(array $over = []): PromoCampaign
    {
        return PromoCampaign::create(array_merge([
            'judul' => 'Promo Test', 'slug' => 'promo-test',
            'deskripsi' => 'Diskon spesial',
            'mulai_at' => now()->subDay(), 'berakhir_at' => now()->addDays(3),
            'status' => 'aktif',
        ], $over));
    }

    public function test_admin_bisa_buat_iklan_dengan_2_produk(): void
    {
        $p1 = $this->buatProduk('a', 'A');
        $p2 = $this->buatProduk('b', 'B');

        $response = $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Promo Merdeka',
            'deskripsi' => 'Merdeka belajar',
            'produk_ids' => [$p1->id, $p2->id],
            'berakhir_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.iklan.index'));
        $k = PromoCampaign::where('slug', 'promo-merdeka')->first();
        $this->assertNotNull($k);
        $this->assertCount(2, $k->products);
    }

    public function test_admin_bisa_simpan_konten_sales_page(): void
    {
        $p1 = $this->buatProduk('a', 'A');

        $response = $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Promo Konten',
            'produk_ids' => [$p1->id],
            'berakhir_at' => now()->addDays(5)->format('Y-m-d\\TH:i'),
            'status' => 'aktif',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'ajakan' => 'Jangan sampai kehabisan!',
            'materi' => ['Materi Satu', '', 'Materi Dua'],
        ]);

        $response->assertRedirect(route('admin.iklan.index'));
        $k = PromoCampaign::where('slug', 'promo-konten')->first();
        $this->assertNotNull($k);
        $this->assertEquals('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $k->video_url);
        $this->assertEquals('Jangan sampai kehabisan!', $k->ajakan);
        $this->assertEquals(['Materi Satu', 'Materi Dua'], $k->materi); // yang kosong dibuang
    }

    public function test_halaman_iklan_tampilkan_materi_video_tanpa_label_deskripsi(): void
    {
        $p = $this->buatProduk('a', 'Produk A');
        $kampanye = $this->buatKampanye([
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'ajakan' => 'Amankan sekarang!',
            'materi' => ['Materi Satu', 'Materi Dua'],
        ]);
        $kampanye->products()->sync([$p->id => ['urutan' => 0]]);

        $response = $this->get(route('iklan.show', $kampanye->slug));
        $response->assertOk();
        $response->assertSee('Materi Satu');
        $response->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ');
        $response->assertSee('Amankan sekarang!');
        $response->assertDontSee('Deskripsi Produk:');
        $response->assertSee('HARGA SPESIAL');
    }

    public function test_iklan_tolak_lebih_dari_4_produk(): void
    {
        $ids = [];
        foreach (range(1, 5) as $i) {
            $ids[] = $this->buatProduk("p$i", "P$i")->id;
        }

        $response = $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Kebanyakan',
            'produk_ids' => $ids,
            'berakhir_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('produk_ids');
        $this->assertDatabaseMissing('promo_campaigns', ['judul' => 'Kebanyakan']);
    }

    public function test_iklan_wajib_minimal_1_produk(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Kosong',
            'produk_ids' => [],
            'berakhir_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('produk_ids');
    }

    public function test_halaman_iklan_tampil_untuk_kampanye_live(): void
    {
        $k = $this->buatKampanye();
        $p = $this->buatProduk('a', 'Produk A');
        $k->products()->sync([$p->id => ['urutan' => 0]]);

        $response = $this->get('/iklan/promo-test');

        $response->assertOk();
        $response->assertSee('Promo Test');
        $response->assertSee('Produk A');
        $response->assertSee('Pesan Sekarang');
        $response->assertSee('cd-text', false); // elemen countdown
    }

    public function test_halaman_iklan_menampilkan_subjudul_dan_banner(): void
    {
        $kampanye = $this->buatKampanye(['subjudul' => 'Parents, bingung cari tontonan?', 'gambar_path' => 'promo/banner.jpg']);
        $response = $this->get(route('iklan.show', $kampanye->slug));
        $response->assertOk();
        $response->assertSee('Parents, bingung cari tontonan?');
        $response->assertSee('storage/promo/banner.jpg');
    }

    public function test_halaman_iklan_404_untuk_draft(): void
    {
        $this->buatKampanye(['status' => 'draft', 'slug' => 'draft-x']);

        $this->get('/iklan/draft-x')->assertNotFound();
    }

    public function test_halaman_iklan_tampil_status_berakhir(): void
    {
        $k = $this->buatKampanye(['berakhir_at' => now()->subHour(), 'slug' => 'lewat']);
        $p = $this->buatProduk('a', 'Produk A');
        $k->products()->sync([$p->id => ['urutan' => 0]]);

        $response = $this->get('/iklan/lewat');

        $response->assertOk();
        $response->assertSee('Promo telah berakhir');
        $response->assertDontSee('Pesan Sekarang');
    }

    public function test_non_admin_ditolak(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.iklan.index'))->assertForbidden();
    }
}
