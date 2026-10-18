<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Entitlement;
use App\Models\InstallToken;
use App\Models\Miniapp;
use App\Models\Order;
use App\Models\Product;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrdTambahanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_sitemap_tersedia(): void
    {
        $res = $this->get('/sitemap.xml');

        $res->assertOk();
        $this->assertStringContainsString('xml', $res->headers->get('Content-Type'));
        $this->assertStringContainsString(route('landing'), $res->getContent());
    }

    public function test_admin_idle_lebih_2_jam_dilogout(): void
    {
        $admin = $this->admin();

        $res = $this->actingAs($admin)
            ->withSession(['admin_last_activity' => now()->subMinutes(121)])
            ->get(route('admin.dashboard'));

        $res->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_admin_aktif_tidak_dilogout(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->withSession(['admin_last_activity' => now()->subMinutes(30)])
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_verifikasi_order_mencatat_activity_log(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $product = Product::create(['tipe' => 'ebook', 'judul' => 'E', 'slug' => 'e-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia']);
        $order = Order::create(['user_id' => $user->id, 'product_id' => $product->id,
            'kode_order' => 'RC-TEST1', 'total_bayar' => 10000, 'status' => Order::ST_VERIFIKASI]);

        $this->actingAs($admin)->post(route('admin.pembayaran.approve', $order))->assertRedirect();

        $this->assertDatabaseHas('activity_logs', ['aksi' => 'verifikasi_order', 'admin_id' => $admin->id]);
    }

    public function test_halaman_aktivitas_bisa_diakses_admin(): void
    {
        $this->actingAs($this->admin())->get(route('admin.aktivitas.index'))->assertOk();
    }

    public function test_testimoni_soft_delete_dan_restore(): void
    {
        $admin = $this->admin();
        $t = Testimonial::create(['nama' => 'Ibu', 'isi' => 'Bagus', 'urutan' => 1, 'aktif' => true]);

        // Hapus = soft delete
        $this->actingAs($admin)->delete(route('admin.landing.testimoni.destroy', $t))->assertRedirect();
        $this->assertSoftDeleted('testimonials', ['id' => $t->id]);
        $this->assertEquals(0, Testimonial::count()); // tidak tampil di daftar normal

        // Restore
        $this->actingAs($admin)->post(route('admin.landing.testimoni.restore', $t->id))->assertRedirect();
        $this->assertEquals(1, Testimonial::count());
    }

    public function test_backup_manual_membuat_file(): void
    {
        // Backup butuh file sqlite nyata (bukan :memory:)
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        $disk = Storage::build(['driver' => 'local', 'root' => storage_path('app/backups')]);

        $this->actingAs($this->admin())->post(route('admin.backup.store'))->assertRedirect();

        $files = collect($disk->files());
        $this->assertTrue($files->contains(fn ($f) => str_ends_with($f, '.sql') || str_ends_with($f, '.sql.gz')));
        $this->assertTrue($files->contains(fn ($f) => str_ends_with($f, '.zip')));
        $this->assertDatabaseHas('activity_logs', ['aksi' => 'backup']);

        // Bersihkan
        foreach ($files as $f) {
            $disk->delete($f);
        }
    }

    public function test_cabut_install_token(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $p = Product::create(['tipe' => 'miniapp', 'judul' => 'G', 'slug' => 'g-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia']);
        Miniapp::create(['product_id' => $p->id, 'html_path' => 'x.html', 'pwa_name' => 'G']);
        Entitlement::create(['user_id' => $user->id, 'product_id' => $p->id]);
        $token = InstallToken::untuk($user, $p);

        $this->actingAs($admin)->get(route('admin.miniapp.perangkat', $p))->assertOk();
        $this->actingAs($admin)->post(route('admin.miniapp.token.cabut', $token))->assertRedirect();

        $this->assertTrue($token->fresh()->dicabut);
        $this->assertFalse($token->fresh()->berlaku());
        $this->assertDatabaseHas('activity_logs', ['aksi' => 'cabut_token']);
    }
}
