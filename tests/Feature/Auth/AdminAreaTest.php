<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dialihkan_dari_home_pengguna(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/home')->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_dialihkan_dari_halaman_pengguna_lain(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/profile')->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_yang_sudah_login_buka_login_diarahkan_ke_dashboard_admin(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/login')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get('/admin/login')->assertRedirect(route('admin.dashboard'));
    }

    public function test_pengguna_tetap_bisa_akses_home(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PENGGUNA]);

        $this->actingAs($user)->get('/home')->assertOk();
    }

    public function test_pengguna_buka_login_diarahkan_ke_home(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PENGGUNA]);

        $this->actingAs($user)->get('/login')->assertRedirect('/home');
    }
}
