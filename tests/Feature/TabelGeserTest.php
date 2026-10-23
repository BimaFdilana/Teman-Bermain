<?php

namespace Tests\Feature;

use App\Models\Miniapp;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi: tabel admin harus bisa digeser horizontal di HP.
 * Kelas .tabel-scroll menang atas overflow:hidden milik .kidza-card —
 * tanpa kelas ini tabel terpotong dan tidak bisa digeser (laporan user 2026-10-23).
 */
class TabelGeserTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_bertabel_memakai_tabel_scroll(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        // Tabel MiniApps hanya dirender bila ada data (selain itu empty-state)
        $p = Product::create([
            'tipe' => 'miniapp', 'judul' => 'Game Tabel', 'slug' => 'game-tabel-'.uniqid(),
            'deskripsi' => 'd', 'harga' => 10000, 'status' => 'tersedia',
        ]);
        Miniapp::create(['product_id' => $p->id, 'html_path' => 'miniapps/tabel.html']);

        foreach ([route('admin.miniapp.index'), route('admin.pembayaran.index'), route('admin.diskon.index'), route('admin.iklan.index')] as $url) {
            $this->actingAs($admin)->get($url)->assertOk()->assertSee('tabel-scroll', false);
        }
    }

    public function test_css_tabel_scroll_terdefinisi(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('.tabel-scroll', $css);
        $this->assertStringContainsString('overflow-x: auto !important', $css);
    }
}
