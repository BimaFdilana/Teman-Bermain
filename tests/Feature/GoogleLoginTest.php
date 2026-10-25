<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Tests\TestCase;

/**
 * Login Google: callback harus gagal secara anggun saat state OAuth tidak
 * cocok (regresi laporan 2026-10-25: 500 InvalidStateException di Android
 * saat ketuk Bergabung), bukan meledak 500.
 */
class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_callback_state_tidak_cocok_kembali_ke_login_bukan_500(): void
    {
        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('google');
        $this->assertGuest();
    }

    public function test_pesan_error_tampil_di_halaman_login(): void
    {
        // Alur nyata: callback gagal -> redirect ke login -> pesan tampil
        $response = $this->followingRedirects()->get(route('google.callback'));

        $response->assertOk();
        $response->assertSee('Sesi login Google kedaluwarsa');
    }

    public function test_callback_sukses_tetap_login_normal(): void
    {
        Socialite::fake('google', GoogleUser::fake([
            'id' => 'g-123', 'email' => 'baru@test.id', 'name' => 'Pengguna Baru',
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'baru@test.id', 'google_id' => 'g-123']);
    }

    public function test_callback_admin_tetap_ditolak(): void
    {
        User::factory()->create(['email' => 'admin@test.id', 'role' => 'admin']);
        Socialite::fake('google', GoogleUser::fake([
            'id' => 'g-999', 'email' => 'admin@test.id', 'name' => 'Admin',
        ]));

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}
