<?php

namespace Tests\Feature;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'password' => bcrypt('rahasia123')]);
    }

    public function test_admin_bisa_login_lewat_halaman_admin(): void
    {
        $admin = $this->admin();

        $response = $this->post(route('admin.login.store'), [
            'email' => $admin->email, 'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_pengguna_biasa_ditolak_di_login_admin(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

        $response = $this->post(route('admin.login.store'), [
            'email' => $user->email, 'password' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_admin_throttle_5x_per_menit(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post(route('admin.login.store'), [
                'email' => 'salah@x.test', 'password' => 'salah',
            ]);
        }
        $response->assertStatus(429);
    }

    public function test_tamu_akses_admin_diarahkan_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect('/login');
    }

    public function test_login_mencatat_login_log(): void
    {
        $admin = $this->admin();

        $this->post(route('admin.login.store'), [
            'email' => $admin->email, 'password' => 'rahasia123',
        ]);

        $this->assertDatabaseHas('login_logs', ['user_id' => $admin->id]);
        $this->assertEquals(1, LoginLog::count());
    }

    public function test_dashboard_hanya_untuk_admin(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertOk();
    }
}
