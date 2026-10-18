<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminKontenTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function htmlFile(string $isi): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'miniapp').'.html';
        file_put_contents($path, $isi);
        return new UploadedFile($path, 'game.html', 'text/html', null, true);
    }

    public function test_admin_bisa_tambah_ebook_dengan_pdf(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->admin())->post(route('admin.ebook.store'), [
            'judul' => 'Ebook Baru',
            'deskripsi' => 'Deskripsi',
            'status' => 'draft',
            'pdf' => UploadedFile::fake()->create('buku.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('admin.ebook.index'));
        $this->assertDatabaseHas('products', ['judul' => 'Ebook Baru', 'tipe' => 'ebook']);
        $p = Product::where('judul', 'Ebook Baru')->first();
        Storage::disk('local')->assertExists($p->ebook->pdf_path);
    }

    public function test_ebook_tolak_file_bukan_pdf(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.ebook.store'), [
            'judul' => 'Ebook Salah',
            'status' => 'draft',
            'pdf' => UploadedFile::fake()->create('buku.txt', 100, 'text/plain'),
        ]);

        $response->assertSessionHasErrors('pdf');
    }

    public function test_admin_bisa_tambah_miniapp_html_valid(): void
    {
        Storage::fake('local');

        $html = '<html><head><style>body{color:red}</style></head><body><script>console.log(1)</script><img src="data:image/png;base64,AAA"></body></html>';

        $response = $this->actingAs($this->admin())->post(route('admin.miniapp.store'), [
            'judul' => 'Game Baru',
            'pwa_name' => 'Game Baru',
            'status' => 'draft',
            'html' => $this->htmlFile($html),
        ]);

        $response->assertRedirect(route('admin.miniapp.index'));
        $this->assertDatabaseHas('products', ['judul' => 'Game Baru', 'tipe' => 'miniapp']);
    }

    public function test_miniapp_tolak_script_src_eksternal(): void
    {
        $html = '<html><body><script src="https://evil.com/x.js"></script></body></html>';

        $response = $this->actingAs($this->admin())->post(route('admin.miniapp.store'), [
            'judul' => 'Game Jahat',
            'pwa_name' => 'Game Jahat',
            'status' => 'draft',
            'html' => $this->htmlFile($html),
        ]);

        $response->assertSessionHasErrors('html');
        $this->assertDatabaseMissing('products', ['judul' => 'Game Jahat']);
    }

    public function test_miniapp_tolak_gambar_lokal(): void
    {
        $html = '<html><body><img src="gambar/bg.png"></body></html>';

        $response = $this->actingAs($this->admin())->post(route('admin.miniapp.store'), [
            'judul' => 'Game Lokal',
            'pwa_name' => 'Game Lokal',
            'status' => 'draft',
            'html' => $this->htmlFile($html),
        ]);

        $response->assertSessionHasErrors('html');
    }

    public function test_miniapp_tolak_file_lebih_dari_5mb(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.miniapp.store'), [
            'judul' => 'Game Besar',
            'pwa_name' => 'Game Besar',
            'status' => 'draft',
            'html' => UploadedFile::fake()->create('besar.html', 6000, 'text/html'),
        ]);

        $response->assertSessionHasErrors('html');
    }

    public function test_non_admin_ditolak_crud_konten(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.ebook.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.miniapp.index'))->assertForbidden();
    }
}
