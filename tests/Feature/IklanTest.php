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

    /** Admin boleh pratinjau iklan Draft lewat tombol Lihat (bukan 404). */
    public function test_admin_bisa_pratinjau_iklan_draft(): void
    {
        $k = $this->buatKampanye(['status' => 'draft', 'slug' => 'draft-pratinjau']);

        $this->actingAs($this->admin())->get(route('iklan.show', $k->slug))
            ->assertOk()
            ->assertSee('Pratinjau admin');
        auth()->logout();
        $this->get(route('iklan.show', $k->slug))->assertNotFound();
    }

    /** Admin boleh pratinjau iklan terjadwal (mulai di masa depan). */
    public function test_admin_bisa_pratinjau_iklan_terjadwal(): void
    {
        $k = $this->buatKampanye([
            'status' => 'aktif', 'slug' => 'terjadwal-pratinjau',
            'mulai_at' => now()->addDays(3), 'berakhir_at' => now()->addDays(10),
        ]);

        $this->actingAs($this->admin())->get(route('iklan.show', $k->slug))
            ->assertOk()
            ->assertSee('Pratinjau admin');
        auth()->logout();
        $this->get(route('iklan.show', $k->slug))->assertNotFound();
    }

    /** Iklan tayang tidak menampilkan banner pratinjau meski dibuka admin. */
    public function test_iklan_tayang_tanpa_banner_pratinjau(): void
    {
        $k = $this->buatKampanye(['slug' => 'tayang-tanpa-banner']);

        $this->actingAs($this->admin())->get(route('iklan.show', $k->slug))
            ->assertOk()
            ->assertDontSee('Pratinjau admin');
    }

    /** Regresi 2026-10-25: hilangkan SEMUA centang 🙈 lalu simpan HARUS
     *  membuka semua section (checkbox yang tak dicentang tidak dikirim
     *  browser — daftar sembunyi lama tidak boleh bertahan). */
    public function test_update_tanpa_section_sembunyi_membuka_semua_section(): void
    {
        $p1 = $this->buatProduk('a', 'A');
        $k = $this->buatKampanye([
            'judul' => 'Promo Sembunyi', 'slug' => 'promo-sembunyi',
            'section_sembunyi' => array_keys(PromoCampaign::SECTIONS),
            'produk_primary_id' => $p1->id,
        ]);

        // Pratinjau admin sebelum perbaikan: tidak ada section yang tampil
        $this->actingAs($this->admin())->get(route('iklan.show', $k->slug))
            ->assertOk()
            ->assertDontSee('Berakhir dalam');

        // Edit tanpa mengirim section_sembunyi sama sekali (semua 🙈 dibuka)
        $this->actingAs($this->admin())->put(route('admin.iklan.update', $k), [
            'judul' => 'Promo Sembunyi',
            'produk_primary_id' => $p1->id,
            'tampilan_secondary' => 'card',
            'testimoni_ids' => $this->gambarTestimoni(),
            'berakhir_at' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'aktif',
        ])->assertRedirect(route('admin.iklan.index'));

        $this->assertSame([], $k->fresh()->section_sembunyi);
        $this->actingAs($this->admin())->get(route('iklan.show', $k->slug))
            ->assertOk()
            ->assertSee('Berakhir dalam');
    }

    /** Hitungan mundur tampil dua kali: di section-nya + di bawah judul HARGA SPESIAL (sebelum tombol). */
    public function test_countdown_juga_tampil_di_bawah_harga_spesial(): void
    {
        $p1 = $this->buatProduk('utama', 'Produk Utama');
        $k = $this->buatKampanye(['judul' => 'Promo CD', 'slug' => 'promo-cd', 'produk_primary_id' => $p1->id]);

        $html = $this->get(route('iklan.show', $k->slug))->assertOk()->getContent();
        $this->assertSame(2, substr_count($html, 'Berakhir dalam'));
        // Duplikatnya terletak sesudah judul HARGA SPESIAL dan sebelum tombol Pesan
        $posHarga = strpos($html, 'HARGA SPESIAL');
        $posPesan = strpos($html, 'Pesan Sekarang', $posHarga);
        $posCd2 = strpos($html, 'Berakhir dalam', $posHarga);
        $this->assertTrue($posCd2 !== false && $posCd2 < $posPesan);

        // Kalau section countdown disembunyikan, duplikatnya ikut hilang
        $k->update(['section_sembunyi' => ['countdown']]);
        $html = $this->get(route('iklan.show', $k->slug))->assertOk()->getContent();
        $this->assertSame(0, substr_count($html, 'Berakhir dalam'));
    }

    /** Foto testimoni terpusat vertikal di dalam kartunya (bukan menempel di atas). */
    public function test_gambar_testimoni_terpusat_di_kartu(): void
    {
        $k = $this->buatKampanye([
            'judul' => 'Promo Tengah2', 'slug' => 'promo-tengah2',
            'testimoni_ss' => $this->gambarTestimoni(),
        ]);

        $html = $this->get(route('iklan.show', $k->slug))->assertOk()->getContent();
        $this->assertStringContainsString('flex-direction:column;justify-content:center', $html);
    }

    /** Slider testimoni terpusat: kartu pertama/terakhir berhenti di tengah. */
    public function test_slider_testimoni_terpusat(): void
    {
        $k = $this->buatKampanye([
            'judul' => 'Promo Tengah', 'slug' => 'promo-tengah',
            'testimoni_ss' => $this->gambarTestimoni(),
        ]);

        $html = $this->get(route('iklan.show', $k->slug))->assertOk()->getContent();
        $this->assertStringContainsString('calc(50% - 120px)', $html);
    }

    /** Warna latar kartu testimoni tersimpan & tampil sebagai frame di halaman publik. */
    public function test_testimoni_bg_tampil_sebagai_frame_kartu(): void
    {
        $k = $this->buatKampanye([
            'judul' => 'Promo BG', 'slug' => 'promo-bg',
            'testimoni_ss' => $this->gambarTestimoni(),
            'testimoni_bg' => '#167941',
        ]);

        $html = $this->get(route('iklan.show', $k->slug))->assertOk()->getContent();
        $this->assertStringContainsString('background:#167941;padding:10px', $html);
    }

    /** Tanpa warna latar, kartu testimoni default putih. */
    public function test_testimoni_bg_default_putih(): void
    {
        $k = $this->buatKampanye([
            'judul' => 'Promo BG2', 'slug' => 'promo-bg2',
            'testimoni_ss' => $this->gambarTestimoni(),
        ]);

        $html = $this->get(route('iklan.show', $k->slug))->assertOk()->getContent();
        $this->assertStringContainsString('background:#ffffff;padding:10px', $html);
    }

    /** Admin bisa menyimpan warna latar testimoni lewat form iklan. */
    public function test_admin_bisa_simpan_warna_latar_testimoni(): void
    {
        $p1 = $this->buatProduk('a', 'A');
        $response = $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Promo Warna',
            'produk_primary_id' => $p1->id,
            'tampilan_secondary' => 'card',
            'testimoni_ids' => $this->gambarTestimoni(),
            'testimoni_bg' => '#F5B301',
            'berakhir_at' => now()->addDays(5)->format('Y-m-d\\TH:i'),
            'status' => 'aktif',
        ]);

        $response->assertRedirect(route('admin.iklan.index'));
        $this->assertSame('#F5B301', PromoCampaign::where('slug', 'promo-warna')->first()->testimoni_bg);
    }

    /** HARGA SPESIAL hanya menampilkan produk utama (bukan semua produk). */
    public function test_harga_spesial_hanya_menampilkan_produk_utama(): void
    {
        $p1 = $this->buatProduk('utama', 'Produk Utama');
        $p1->update(['harga' => 79000, 'harga_coret' => 118000]);
        $p2 = $this->buatProduk('tambahan', 'Produk Tambahan');
        $p2->update(['harga' => 49000]);
        $k = $this->buatKampanye(['judul' => 'Promo Harga', 'slug' => 'promo-harga', 'produk_primary_id' => $p1->id]);
        $k->produkSecondary()->sync([$p2->id]);

        $html = $this->get(route('iklan.show', $k->slug))->assertOk()->getContent();
        $awal = strpos($html, 'HARGA SPESIAL');
        $akhir = strpos($html, 'Pesan Sekarang', $awal);
        $blok = substr($html, $awal, $akhir - $awal);

        $this->assertStringContainsString('Rp 79.000', $blok);
        $this->assertStringContainsString('Rp 118.000', $blok);
        $this->assertStringNotContainsString('Rp 49.000', $blok);
    }

    /** Tabel iklan menampilkan status mentah (aktif/draft) di bawah badge tayang. */
    public function test_tabel_iklan_menampilkan_status_mentah(): void
    {
        $this->buatKampanye(['judul' => 'Promo Aktif X', 'slug' => 'promo-aktif-x', 'status' => 'aktif']);
        $this->buatKampanye(['judul' => 'Promo Draft Y', 'slug' => 'promo-draft-y', 'status' => 'draft']);

        $response = $this->actingAs($this->admin())->get(route('admin.iklan.index'));

        $response->assertOk();
        $response->assertSee('status: aktif');
        $response->assertSee('status: draft');
    }

    /** Regresi laporan admin 2026-10-25: iklan tersimpan HARUS muncul di tabel index. */
    public function test_iklan_tersimpan_muncul_di_tabel_index(): void
    {
        $p1 = $this->buatProduk('a', 'A');
        $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Promo Muncul Tabel',
            'produk_primary_id' => $p1->id,
            'tampilan_secondary' => 'card',
            'testimoni_ids' => $this->gambarTestimoni(),
            'berakhir_at' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'aktif',
        ])->assertRedirect(route('admin.iklan.index'));

        $this->get(route('admin.iklan.index'))
            ->assertOk()
            ->assertSee('Promo Muncul Tabel');
    }

    /** Slogan boleh panjang: 400 karakter tersimpan utuh & tampil di halaman publik. */
    public function test_subjudul_panjang_400_karakter_tersimpan_utuh(): void
    {
        $p1 = $this->buatProduk('a', 'A');
        $slogan = substr(str_repeat('Slogan panjang untuk orang tua. ', 13), 0, 400);

        $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Promo Slogan Panjang',
            'subjudul' => $slogan,
            'produk_primary_id' => $p1->id,
            'tampilan_secondary' => 'card',
            'testimoni_ids' => $this->gambarTestimoni(),
            'berakhir_at' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'aktif',
        ])->assertRedirect(route('admin.iklan.index'));

        $k = PromoCampaign::where('slug', 'promo-slogan-panjang')->first();
        $this->assertNotNull($k);
        $this->assertSame(mb_strlen($slogan), mb_strlen($k->subjudul));
        $this->assertSame($slogan, $k->subjudul);
        $this->get(route('iklan.show', $k->slug))->assertOk()->assertSee($slogan);
    }

    /** Simpan sebagai Draft harus jelas di flash — bukan dikira hilang. */
    public function test_flash_draft_menyebut_status_draft(): void
    {
        $p1 = $this->buatProduk('a', 'A');
        $this->actingAs($this->admin())->post(route('admin.iklan.store'), [
            'judul' => 'Promo Draft Saja',
            'produk_primary_id' => $p1->id,
            'tampilan_secondary' => 'card',
            'testimoni_ids' => $this->gambarTestimoni(),
            'berakhir_at' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'draft',
        ])->assertSessionHas('sukses', 'Iklan promo dibuat sebagai Draft — ubah status ke Aktif agar tayang di halaman publik.');
    }

    /** Preview halaman create memuat SEMUA section + blok HARGA SPESIAL + countdown live. */
    public function test_preview_create_memuat_semua_section(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.iklan.create'));

        $response->assertOk();
        foreach (array_keys(PromoCampaign::SECTIONS) as $key) {
            $response->assertSee('data-pv-section="'.$key.'"', false);
        }
        $response->assertSee('HARGA SPESIAL');
        $response->assertSee('pv-countdown-text');
    }

    /** Setelah berakhir: iklan TETAP di tabel admin (badge Berakhir), halaman publik
     *  menampilkan "Promo telah berakhir", dan harga produk TIDAK berubah otomatis. */
    public function test_iklan_berakhir_tetap_di_tabel_dan_harga_tidak_berubah(): void
    {
        $p = Product::create([
            'tipe' => 'ebook', 'judul' => 'Ebook Promo', 'slug' => 'ebook-promo',
            'deskripsi' => 'x', 'harga' => 79000, 'harga_coret' => 118000,
            'jenjang' => 'PAUD', 'status' => 'tersedia',
        ]);
        $k = $this->buatKampanye([
            'judul' => 'Promo Sampai 8 Nov', 'slug' => 'promo-8-nov',
            'berakhir_at' => now()->subDay(), 'produk_primary_id' => $p->id,
        ]);

        $this->actingAs($this->admin())->get(route('admin.iklan.index'))
            ->assertOk()
            ->assertSee('Promo Sampai 8 Nov')
            ->assertSee('Berakhir');
        $this->get(route('iklan.show', $k->slug))
            ->assertOk()
            ->assertSee('Promo telah berakhir');

        $this->assertSame(79000, (int) $p->fresh()->harga);
        $this->assertSame(118000, (int) $p->fresh()->harga_coret);
    }
}
