<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Galeri gambar testimoni terpusat: admin upload sekali di menu
     * Gambar Testimoni, lalu di form iklan tinggal memilih.
     * promo_campaigns.testimoni_ss berubah makna: dari array path file
     * menjadi array ID testimonial_images (urutan = urutan tampil).
     */
    public function up(): void
    {
        Schema::create('testimonial_images', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique()->comment('Path file di disk public');
            $table->timestamps();
        });

        // Konversi data lama: path -> ID galeri (file tidak dipindah).
        foreach (DB::table('promo_campaigns')->select('id', 'testimoni_ss')->get() as $row) {
            $lama = json_decode($row->testimoni_ss ?? '[]', true);
            if (! is_array($lama) || $lama === []) {
                continue;
            }
            $ids = [];
            foreach ($lama as $item) {
                if (! is_string($item)) {
                    continue; // sudah berupa ID / tidak dikenal — lewati
                }
                $id = DB::table('testimonial_images')->where('path', $item)->value('id');
                if (! $id) {
                    $id = DB::table('testimonial_images')->insertGetId([
                        'path' => $item,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $ids[] = (int) $id;
            }
            DB::table('promo_campaigns')->where('id', $row->id)
                ->update(['testimoni_ss' => json_encode($ids)]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonial_images');
    }
};
