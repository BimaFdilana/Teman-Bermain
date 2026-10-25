<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PromoCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Meta Pixel: event browser ViewContent / AddToCart / InitiateCheckout / Lead. */
class MetaEventsTest extends TestCase
{
    use RefreshDatabase;

    private function produk(array $over = []): Product
    {
        return Product::create(array_merge([
            'tipe' => 'ebook', 'judul' => 'Produk Event', 'slug' => 'produk-event',
            'deskripsi' => 'x', 'harga' => 75000, 'jenjang' => 'PAUD', 'status' => 'tersedia',
        ], $over));
    }

    public function test_viewcontent_di_detail_produk_dan_halaman_promo(): void
    {
        $p = $this->produk();

        $this->get(route('produk.show', $p->slug))->assertOk()
            ->assertSee("fbq('track', 'ViewContent'", false)
            ->assertSee((string) $p->id, false);

        $this->get(route('promo.show', $p->slug))->assertOk()
            ->assertSee("fbq('track', 'ViewContent'", false);
    }

    public function test_addtocart_muncul_setelah_tambah_keranjang(): void
    {
        $p = $this->produk();

        $this->from('/')->followingRedirects()
            ->post(route('cart.tambah', $p->slug))->assertOk()
            ->assertSee("fbq('track', 'AddToCart'", false)
            ->assertSee((string) $p->id, false);
    }

    public function test_initiatecheckout_di_halaman_checkout(): void
    {
        $p = $this->produk();

        $this->withSession(['cart' => [$p->id]])
            ->get(route('cart.checkout'))->assertOk()
            ->assertSee("fbq('track', 'InitiateCheckout'", false)
            ->assertSee((string) $p->id, false);
    }

    public function test_lead_di_tombol_pesan_halaman_iklan(): void
    {
        $p = $this->produk();
        $k = PromoCampaign::create([
            'judul' => 'Promo Event', 'slug' => 'promo-event', 'deskripsi' => 'x',
            'produk_primary_id' => $p->id,
            'mulai_at' => now()->subDay(), 'berakhir_at' => now()->addDays(3),
            'status' => 'aktif',
        ]);

        $this->get(route('iklan.show', $k->slug))->assertOk()
            ->assertSee('js-meta-lead', false)
            ->assertSee("fbq('track', 'Lead'", false);
    }
}
