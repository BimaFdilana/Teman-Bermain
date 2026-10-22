<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Logo Slider landing: dikelola dari Pengaturan (logo usaha + pendukung).
 */
class LogoSliderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_upload_logo_slider_dan_tampil_di_landing(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())->post(route('admin.pengaturan.logo-slider.store'), [
            'logo' => [
                UploadedFile::fake()->image('usaha.png'),
                UploadedFile::fake()->image('mitra.png'),
            ],
        ]);

        $response->assertRedirect();
        $daftar = json_decode(Setting::get('logo_slider', '[]'), true);
        $this->assertCount(2, $daftar);
        foreach ($daftar as $path) {
            Storage::disk('public')->assertExists($path);
        }

        // Landing menampilkan logo (bukan marquee teks)
        $landing = $this->get(route('landing'));
        $landing->assertOk();
        $landing->assertSee('kidza-marquee-logo', false);
        $landing->assertSee('storage/'.$daftar[0], false);
        $landing->assertDontSee('Ebook Anak 📚');
    }

    public function test_landing_tanpa_logo_tetap_marquee_teks(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Ebook Anak 📚')
            ->assertDontSee('kidza-marquee-logo', false);
    }

    public function test_admin_hapus_satu_logo_slider(): void
    {
        Storage::fake('public');
        $p1 = UploadedFile::fake()->image('a.png')->store('logo-slider', 'public');
        $p2 = UploadedFile::fake()->image('b.png')->store('logo-slider', 'public');
        Setting::set('logo_slider', json_encode([$p1, $p2]));

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.pengaturan.logo-slider.destroy', 0));

        $response->assertRedirect();
        $this->assertSame([$p2], json_decode(Setting::get('logo_slider', '[]'), true));
        Storage::disk('public')->assertMissing($p1);
        Storage::disk('public')->assertExists($p2);
    }

    public function test_non_admin_ditolak_upload_logo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.pengaturan.logo-slider.store'), [
            'logo' => [UploadedFile::fake()->image('x.png')],
        ])->assertForbidden();
    }
}
