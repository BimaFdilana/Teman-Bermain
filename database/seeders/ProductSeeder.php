<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\Ebook;
use App\Models\Miniapp;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'tipe' => 'ebook', 'judul' => 'Belajar Membaca: Petualangan Huruf A-Z',
                'deskripsi' => 'Ebook interaktif 48 halaman full color yang mengajak anak PAUD berkenalan dengan huruf A sampai Z lewat cerita pendek, gambar besar, dan tombol suara di setiap halaman.',
                'fitur' => ['48 halaman full color', 'Tombol suara baca huruf & kata', 'Latihan menebalkan huruf', 'Untuk usia 4-6 tahun'],
                'harga' => 25000, 'harga_coret' => 35000, 'jenjang' => 'PAUD',
                'cover' => 'belajar-membaca.svg',
                'detail' => ['jumlah_halaman' => 48],
            ],
            [
                'tipe' => 'ebook', 'judul' => 'Berhitung Seru dengan Hewan Ternak',
                'deskripsi' => 'Belajar angka 1-20 sambil berkenalan dengan sapi, kambing, dan ayam di peternakan. Setiap halaman ada kuis hitung gambar yang bisa disentuh.',
                'fitur' => ['36 halaman full color', 'Kuis sentuh interaktif', 'Lagu angka 1-20', 'Untuk usia 5-7 tahun'],
                'harga' => 20000, 'harga_coret' => null, 'jenjang' => 'TK',
                'cover' => 'berhitung-seru.svg',
                'detail' => ['jumlah_halaman' => 36],
            ],
            [
                'tipe' => 'ebook', 'judul' => 'Cerita Teladan: Kisah 25 Nabi untuk Anak',
                'deskripsi' => 'Kumpulan kisah 25 nabi yang ditulis dengan bahasa sederhana untuk anak SD, lengkap dengan ilustrasi hangat dan pesan moral di setiap akhir cerita.',
                'fitur' => ['64 halaman full color', 'Bahasa sederhana untuk anak', 'Pesan moral tiap kisah', 'Untuk usia 7-10 tahun'],
                'harga' => 30000, 'harga_coret' => 45000, 'jenjang' => 'SD 1-3',
                'cover' => 'cerita-teladan.svg',
                'detail' => ['jumlah_halaman' => 64],
            ],
            [
                'tipe' => 'miniapp', 'judul' => 'Teman Main: Garden School',
                'deskripsi' => 'Game mencocokkan kata dan gambar di kebun sekolah: seret kartu kata ke gambar yang tepat, kumpulkan bintang, dan dengarkan pelafalannya.',
                'fitur' => ['Game HTML5 tanpa install awal', 'Bisa dipasang sebagai aplikasi di HP', 'Main offline setelah dipasang', 'Untuk usia 4-7 tahun'],
                'harga' => 35000, 'harga_coret' => null, 'jenjang' => 'PAUD',
                'cover' => 'garden-school.svg',
                'detail' => ['pwa_name' => 'Garden School', 'versi' => '1.0.0'],
            ],
            [
                'tipe' => 'miniapp', 'judul' => 'Tebak Angka Ajaib',
                'deskripsi' => 'Tantangan berhitung cepat: jawab soal tambah-kurang sebelum waktu habis, naik level, dan kumpulkan medali angka.',
                'fitur' => ['Game HTML5 tanpa install awal', 'Bisa dipasang sebagai aplikasi di HP', '3 level kesulitan', 'Untuk usia 5-8 tahun'],
                'harga' => 25000, 'harga_coret' => null, 'jenjang' => 'TK',
                'cover' => 'tebak-angka.svg',
                'detail' => ['pwa_name' => 'Tebak Angka Ajaib', 'versi' => '1.0.0'],
            ],
        ];

        $created = [];
        foreach ($products as $p) {
            $product = Product::create([
                'tipe' => $p['tipe'],
                'judul' => $p['judul'],
                'slug' => Str::slug($p['judul']),
                'deskripsi' => $p['deskripsi'],
                'fitur' => $p['fitur'],
                'cover_path' => 'images/covers/'.$p['cover'],
                'harga' => $p['harga'],
                'harga_coret' => $p['harga_coret'],
                'jenjang' => $p['jenjang'],
                'status' => 'tersedia',
            ]);
            if ($p['tipe'] === 'ebook') {
                Ebook::create(['product_id' => $product->id] + $p['detail']);
            } else {
                Miniapp::create(['product_id' => $product->id] + $p['detail']);
            }
            $created[$p['judul']] = $product;
        }

        // Bundles
        $bundles = [
            [
                'judul' => 'Paket PAUD Ceria',
                'deskripsi' => 'Paket hemat untuk anak PAUD: 1 Ebook belajar membaca + 1 MiniApp Garden School. Lebih murah daripada beli satuan.',
                'fitur' => ['1 Ebook + 1 MiniApp', 'Hemat Rp 11.000', '1x redeem untuk semua isi paket'],
                'harga' => 49000, 'harga_coret' => 60000, 'jenjang' => 'PAUD',
                'cover' => 'paket-paud.svg',
                'isi' => ['Belajar Membaca: Petualangan Huruf A-Z', 'Teman Main: Garden School'],
            ],
            [
                'judul' => 'Paket TK Pintar',
                'deskripsi' => 'Paket hemat untuk anak TK: Ebook berhitung + MiniApp Tebak Angka Ajaib. Belajar angka jadi permainan seru.',
                'fitur' => ['1 Ebook + 1 MiniApp', 'Hemat Rp 6.000', '1x redeem untuk semua isi paket'],
                'harga' => 39000, 'harga_coret' => 45000, 'jenjang' => 'TK',
                'cover' => 'paket-tk.svg',
                'isi' => ['Berhitung Seru dengan Hewan Ternak', 'Tebak Angka Ajaib'],
            ],
        ];

        foreach ($bundles as $b) {
            $product = Product::create([
                'tipe' => 'bundle',
                'judul' => $b['judul'],
                'slug' => Str::slug($b['judul']),
                'deskripsi' => $b['deskripsi'],
                'fitur' => $b['fitur'],
                'cover_path' => 'images/covers/'.$b['cover'],
                'harga' => $b['harga'],
                'harga_coret' => $b['harga_coret'],
                'jenjang' => $b['jenjang'],
                'status' => 'tersedia',
            ]);
            $bundle = Bundle::create(['product_id' => $product->id]);
            foreach ($b['isi'] as $judulItem) {
                $bundle->items()->attach($created[$judulItem]->id);
            }
        }

        // Testimonials
        $testis = [
            ['nama' => 'Bunda Ratna', 'peran' => 'Ibu dari Nara (5 th), Bekasi', 'isi' => 'Nara yang tadinya susah duduk diam, sekarang nagih minta "main huruf" tiap sore. Ebooknya bersuara jadi dia bisa belajar sendiri.', 'urutan' => 1],
            ['nama' => 'Pak Dedi', 'peran' => 'Ayah dari Kirana (7 th), Bandung', 'isi' => 'Beli hari Senin pagi, siang sudah dapat kode via WA. Anak saya langsung main Tebak Angka Ajaib sampai lupa waktu — dalam arti yang baik.', 'urutan' => 2],
            ['nama' => 'Bunda Sari', 'peran' => 'Ibu dari dua anak, Surabaya', 'isi' => 'Paket PAUD Ceria worth it banget. Satu kode untuk ebook dan gamenya, anak saya yang 4 tahun dan 6 tahun bisa pakai bareng.', 'urutan' => 3],
        ];
        foreach ($testis as $t) {
            Testimonial::create($t + ['aktif' => true]);
        }

        // Settings
        $settings = [
            'hero_headline' => 'Belajar Jadi Petualangan Seru!',
            'hero_subheadline' => 'Ebook interaktif & MiniApp game edukasi untuk anak PAUD dan SD. Beli sekali, mainkan selamanya.',
            'wa_admin' => '6281234567890',
            'jam_operasional' => 'Senin–Sabtu, 08.00–20.00 WIB',
            'alamat' => 'Bekasi, Jawa Barat',
        ];
        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
