<?php

namespace Tests\Feature;

use App\Models\Ebook;
use App\Models\Entitlement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EbookReaderTest extends TestCase
{
    use RefreshDatabase;

    private string $pdfPath = 'ebooks/uji.pdf';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::disk('local')->put($this->pdfPath, '%PDF-1.4 uji');
    }

    protected function tearDown(): void
    {
        Storage::disk('local')->delete($this->pdfPath);
        parent::tearDown();
    }

    private function ebookMilik(User $user): Product
    {
        $p = Product::create([
            'tipe' => 'ebook', 'judul' => 'Ebook Uji', 'slug' => 'ebook-uji-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Ebook::create(['product_id' => $p->id, 'pdf_path' => $this->pdfPath, 'jumlah_halaman' => 10]);
        Entitlement::create(['user_id' => $user->id, 'product_id' => $p->id]);
        return $p;
    }

    public function test_pemilik_bisa_buka_reader(): void
    {
        $user = User::factory()->create();
        $p = $this->ebookMilik($user);

        $response = $this->actingAs($user)->get(route('ebook.baca', $p));

        $response->assertOk();
        $response->assertSee('iframe', false);
        $response->assertSee('Unduh PDF', false);
    }

    public function test_bukan_pemilik_ditolak(): void
    {
        $pemilik = User::factory()->create();
        $lain = User::factory()->create();
        $p = $this->ebookMilik($pemilik);

        $this->actingAs($lain)->get(route('ebook.baca', $p))->assertForbidden();
    }

    public function test_tamu_diarahkan_login(): void
    {
        $user = User::factory()->create();
        $p = $this->ebookMilik($user);

        $this->get(route('ebook.baca', $p))->assertRedirect('/login');
    }

    public function test_file_signed_url_berfungsi_untuk_pemilik(): void
    {
        $user = User::factory()->create();
        $p = $this->ebookMilik($user);

        $url = URL::temporarySignedRoute('file.ebook', now()->addMinutes(30), ['product' => $p->id]);
        $response = $this->actingAs($user)->get($url);

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_file_tanpa_signature_ditolak(): void
    {
        $user = User::factory()->create();
        $p = $this->ebookMilik($user);

        $this->actingAs($user)->get(route('file.ebook', $p))->assertForbidden();
    }

    public function test_file_bukan_pemilik_ditolak(): void
    {
        $pemilik = User::factory()->create();
        $lain = User::factory()->create();
        $p = $this->ebookMilik($pemilik);

        $url = URL::temporarySignedRoute('file.ebook', now()->addMinutes(30), ['product' => $p->id]);
        $this->actingAs($lain)->get($url)->assertForbidden();
    }

    public function test_unduh_pdf_attachment(): void
    {
        $user = User::factory()->create();
        $p = $this->ebookMilik($user);

        $url = URL::temporarySignedRoute('file.ebook', now()->addMinutes(30), ['product' => $p->id, 'download' => 1]);
        $response = $this->actingAs($user)->get($url);

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }
}
