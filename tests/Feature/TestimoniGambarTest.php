<?php

namespace Tests\Feature;

use App\Models\PromoCampaign;
use App\Models\TestimonialImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Galeri Gambar Testimoni: upload terpusat, dipilih di form iklan.
 */
class TestimoniGambarTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_bisa_upload_gambar_ke_galeri(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())->post(route('admin.testimoni-gambar.store'), [
            'gambar' => [
                UploadedFile::fake()->image('satu.jpg'),
                UploadedFile::fake()->image('dua.jpg'),
            ],
        ]);

        $response->assertRedirect();
        $this->assertSame(2, TestimonialImage::count());
        foreach (TestimonialImage::all() as $g) {
            Storage::disk('public')->assertExists($g->path);
        }
    }

    public function test_non_admin_ditolak(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.testimoni-gambar.index'))->assertForbidden();
    }

    public function test_hapus_gambar_yang_tidak_dipakai(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('bebas.jpg')->store('testimoni', 'public');
        $g = TestimonialImage::create(['path' => $path]);

        $response = $this->actingAs($this->admin())->delete(route('admin.testimoni-gambar.destroy', $g));

        $response->assertRedirect();
        $this->assertSame(0, TestimonialImage::count());
        Storage::disk('public')->assertMissing($path);
    }

    public function test_hapus_gambar_yang_sedang_dipakai_iklan_diblokir(): void
    {
        $g = TestimonialImage::create(['path' => 'testimoni/dipakai.jpg']);
        PromoCampaign::create([
            'judul' => 'Promo', 'slug' => 'promo-'.uniqid(),
            'berakhir_at' => now()->addDays(2), 'status' => 'aktif',
            'testimoni_ss' => [$g->id],
        ]);

        $response = $this->actingAs($this->admin())->delete(route('admin.testimoni-gambar.destroy', $g));

        $response->assertSessionHasErrors('gambar');
        $this->assertSame(1, TestimonialImage::count());
    }
}
