<?php

namespace Tests\Feature;

use App\Jobs\KirimWaJob;
use App\Mail\KodeRedeemMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\WablasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/** Notifikasi WhatsApp via Wablas: service, antrean, hook order, Pengaturan. */
class WablasTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function produk(): Product
    {
        return Product::create([
            'tipe' => 'ebook', 'judul' => 'Ebook Uji', 'slug' => 'ebook-uji',
            'deskripsi' => 'd', 'harga' => 50000, 'stok' => 10, 'status' => 'tersedia',
        ]);
    }

    private function orderVerifikasi(string $status = Order::ST_VERIFIKASI): Order
    {
        $user = User::factory()->create([
            'email' => 'pembeli@test.id', 'whatsapp' => '6281234567890',
        ]);
        $p = $this->produk();

        return Order::create([
            'kode_order' => 'RCB-'.uniqid(), 'user_id' => $user->id,
            'product_id' => $p->id, 'total_bayar' => 50000,
            'status' => $status, 'bukti_path' => 'bukti/x.jpg',
            'batas_bayar' => now()->addDay(),
        ]);
    }

    private function aktifkanWablas(): void
    {
        Setting::set('wablas_aktif', '1');
        Setting::set('wablas_token', Crypt::encryptString('token-uji'));
        Setting::set('wablas_secret', Crypt::encryptString('secret-uji'));
        Setting::set('wablas_domain', 'console.wablas.com');
        Setting::set('wa_admin', '6280000000001');
    }

    public function test_service_mengirim_ke_endpoint_wablas(): void
    {
        $this->aktifkanWablas();
        Http::fake(['*console.wablas.com/*' => Http::response(['status' => true], 200)]);

        $this->assertTrue(app(WablasService::class)->kirim('081234567890', 'halo'));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'console.wablas.com/api/send-message')
                && $request->header('Authorization')[0] === 'token-uji.secret-uji'
                && $request->data()['phone'] === '6281234567890'
                && $request->data()['message'] === 'halo';
        });
    }

    public function test_service_diam_bila_nonaktif(): void
    {
        Http::fake();

        $this->assertFalse(app(WablasService::class)->kirim('081234567890', 'halo'));
        Http::assertNothingSent();
    }

    public function test_domain_menerima_tempelan_url_lengkap(): void
    {
        $this->aktifkanWablas();
        Setting::set('wablas_domain', 'https://texas.wablas.com/');
        Http::fake(['*texas.wablas.com/*' => Http::response(['status' => true], 200)]);

        $this->assertTrue(app(WablasService::class)->kirim('081234567890', 'halo'));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://texas.wablas.com/api/send-message';
        });
    }

    public function test_service_menyimpan_penyebab_gagal(): void
    {
        $this->aktifkanWablas();
        Http::fake(['*console.wablas.com/*' => Http::response('unauthorized', 401)]);

        $service = app(WablasService::class);
        $this->assertFalse($service->kirim('081234567890', 'halo'));
        $this->assertEquals('HTTP 401', $service->errorTerakhir);
    }

    public function test_order_baru_mengantre_notif_admin(): void
    {
        $this->aktifkanWablas();
        Queue::fake();
        $p = $this->produk();

        $this->get('/p/'.$p->slug.'/pesan')->assertOk();
        $this->post('/p/checkout', [
            'nama' => 'Budi Tamu', 'email' => 'budi@example.com',
            'whatsapp' => '081234567890', 'metode_bayar' => 'qris',
        ])->assertRedirect();

        Queue::assertPushed(KirimWaJob::class, function ($job) {
            return $job->tujuan === '6280000000001'
                && str_contains($job->pesan, 'Order baru');
        });
    }

    public function test_order_baru_tidak_mengantre_bila_wablas_mati(): void
    {
        Queue::fake();
        $p = $this->produk();

        $this->get('/p/'.$p->slug.'/pesan')->assertOk();
        $this->post('/p/checkout', [
            'nama' => 'Budi Tamu', 'email' => 'budi@example.com',
            'whatsapp' => '081234567890', 'metode_bayar' => 'qris',
        ])->assertRedirect();

        Queue::assertNotPushed(KirimWaJob::class);
    }

    public function test_approve_mengantre_wa_redeem_dan_email(): void
    {
        $this->aktifkanWablas();
        Queue::fake();
        Mail::fake();
        $order = $this->orderVerifikasi();

        $this->actingAs($this->admin())
            ->post(route('admin.pembayaran.approve', $order))
            ->assertRedirect(route('admin.pembayaran.show', $order));

        Queue::assertPushed(KirimWaJob::class, function ($job) {
            return $job->tujuan === '6281234567890'
                && str_contains($job->pesan, 'Kode redeem');
        });
        Mail::assertQueued(KodeRedeemMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id && count($mail->kodes) === 1;
        });
    }

    public function test_approve_dari_status_ditolak_bisa_tanpa_order_ulang(): void
    {
        $this->aktifkanWablas();
        Queue::fake();
        $order = $this->orderVerifikasi(Order::ST_DITOLAK);

        $this->actingAs($this->admin())
            ->post(route('admin.pembayaran.approve', $order))
            ->assertRedirect(route('admin.pembayaran.show', $order));

        $this->assertEquals('disetujui', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->redeemCode);
    }

    public function test_reject_mengantre_wa_alasan(): void
    {
        $this->aktifkanWablas();
        Queue::fake();
        $order = $this->orderVerifikasi();

        $this->actingAs($this->admin())
            ->post(route('admin.pembayaran.reject', $order), ['alasan_tolak' => 'Bukti buram'])
            ->assertRedirect(route('admin.pembayaran.show', $order));

        $this->assertEquals('ditolak', $order->fresh()->status);
        Queue::assertPushed(KirimWaJob::class, function ($job) {
            return $job->tujuan === '6281234567890'
                && str_contains($job->pesan, 'Bukti buram')
                && str_contains($job->pesan, 'tidak perlu order ulang');
        });
    }

    public function test_tombol_test_mengirim_ke_wa_admin(): void
    {
        $this->aktifkanWablas();
        Http::fake(['*console.wablas.com/*' => Http::response(['status' => true], 200)]);

        $this->actingAs($this->admin())
            ->post(route('admin.pengaturan.wablas-test'))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        Http::assertSent(function ($request) {
            return $request->data()['phone'] === '6280000000001';
        });
    }

    public function test_tombol_test_gagal_bila_wablas_mati(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.pengaturan.wablas-test'))
            ->assertRedirect()
            ->assertSessionHas('gagal');
    }

    public function test_token_disimpan_terenkripsi(): void
    {
        $this->actingAs($this->admin())->post(route('admin.pengaturan.update'), [
            'wablas_aktif' => '1',
            'wablas_token' => 'token-rahasia',
            'wablas_secret' => 'secret-rahasia',
            'wablas_domain' => 'console.wablas.com',
        ])->assertRedirect();

        $tersimpan = Setting::get('wablas_token');
        $this->assertNotEquals('token-rahasia', $tersimpan);
        $this->assertEquals('token-rahasia', WablasService::token());
        $this->assertNotEquals('secret-rahasia', Setting::get('wablas_secret'));
        $this->assertEquals('secret-rahasia', WablasService::secret());
    }
}
