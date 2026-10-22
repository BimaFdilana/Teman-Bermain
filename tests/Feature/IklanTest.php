<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TestimonialImage;
use App\Models\PromoCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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


    /** Buat gambar testimoni di galeri; kembalikan array ID-nya. */
    private function gambarTestimoni(int $jumlah = 3): array
    {
        $ids = [];
        for ($i = 1; $i <= $jumlah; $i++) {
            $ids[] = TestimonialImage::create(['path' => 'testimoni/uji-'.uniqid()."-{$i}.jpg"])->id;
        }
        return $ids;
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
            'produk_primary_id' => $p1->id,
            'produk_secondary_ids' => [$p2->id],
            'tampilan_secondary' => 'card',
            'testimoni_ids' => $this->gambarTestimoni(),
            'berakhir_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.iklan.index'));
        $k = PromoCampaign::where('slug', 'promo-merdeka')->first();
        $this->assertNotNull($k);
        $this->assertEquals($p1->id, $k->produk_primary_id);
        $this->assertCount(1, $k->produkSecondary);
        $this->assertCount(3, $k->testimoni_ss);
    }

    public function test_admin_bisa_simpan_konten_sales_page(): void
    {
        $p1 = $this->buatProduk('a', 'A');

        $response = $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Promo Konten',
            'produk_primary_id' => $p1->id,
            'tampilan_secondary' => 'list',
            'testimoni_ids' => $this->gambarTestimoni(),
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
        $kampanye->update(['produk_primary_id' => $p->id]);

        $response = $this->get(route('iklan.show', $kampanye->slug));
        $response->assertOk();
        $response->assertSee('Materi Satu');
        $response->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ');
        $response->assertSee('Amankan sekarang!');
        $response->assertDontSee('Deskripsi Produk:');
        $response->assertSee('HARGA SPESIAL');
    }

    public function test_iklan_tolak_lebih_dari_3_secondary(): void
    {
        $primary = $this->buatProduk('pp', 'PP');
        $ids = [];
        foreach (range(1, 4) as $i) {
            $ids[] = $this->buatProduk("p$i", "P$i")->id;
        }

        $response = $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Kebanyakan',
            'produk_primary_id' => $primary->id,
            'produk_secondary_ids' => $ids,
            'tampilan_secondary' => 'card',
            'berakhir_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('produk_secondary_ids');
        $this->assertDatabaseMissing('promo_campaigns', ['judul' => 'Kebanyakan']);
    }

    public function test_iklan_wajib_produk_utama(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Kosong',
            'tampilan_secondary' => 'card',
            'berakhir_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('produk_primary_id');
    }

    public function test_halaman_iklan_tampil_untuk_kampanye_live(): void
    {
        $k = $this->buatKampanye();
        $p = $this->buatProduk('a', 'Produk A');
        $k->update(['produk_primary_id' => $p->id]);

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
        $k->update(['produk_primary_id' => $p->id]);

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

    public function test_urutan_konten_dan_section_sembunyi(): void
    {
        $p = $this->buatProduk('a', 'Produk A');
        $k = $this->buatKampanye();
        $k->update(['produk_primary_id' => $p->id]);

        $response = $this->actingAs($this->admin())->put(route('admin.iklan.update', $k), [
            'judul' => 'Promo Test',
            'produk_primary_id' => $p->id,
            'tampilan_secondary' => 'list',
            'testimoni_ids' => $this->gambarTestimoni(),
            'urutan_konten' => ['testimoni', 'produk_utama', 'banner'],
            'section_sembunyi' => ['countdown'],
            'berakhir_at' => now()->addDays(5)->format('Y-m-d\\TH:i'),
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.iklan.index'));
        $k->refresh();
        $this->assertEquals(['testimoni', 'produk_utama', 'banner'], array_slice($k->urutan_konten, 0, 3));
        $this->assertEquals(['countdown'], $k->section_sembunyi);
        $this->assertFalse($k->sectionTampil('countdown'));
        $this->assertTrue($k->sectionTampil('banner'));
        // Urutan efektif tetap mencakup semua section
        $this->assertCount(count(PromoCampaign::SECTIONS), $k->urutanKontenEfektif());
    }

    public function test_halaman_iklan_tampilkan_primary_dan_secondary_list(): void
    {
        $utama = $this->buatProduk('utama', 'Produk Utama');
        $tambahan = $this->buatProduk('tambahan', 'Produk Tambahan');
        $k = $this->buatKampanye(['tampilan_secondary' => 'list']);
        $k->update(['produk_primary_id' => $utama->id]);
        $k->produkSecondary()->sync([$tambahan->id => ['urutan' => 0]]);

        $response = $this->get(route('iklan.show', $k->slug));

        $response->assertOk();
        $response->assertSee('PRODUK UTAMA');
        $response->assertSee('Produk Tambahan');
        $response->assertSee('+ Lihat');
    }
}
