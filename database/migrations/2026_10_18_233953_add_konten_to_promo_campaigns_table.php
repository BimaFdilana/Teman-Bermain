<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Konten sales page: video opsional, kalimat ajakan, daftar materi. */
    public function up(): void
    {
        Schema::table('promo_campaigns', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('gambar_path');
            $table->text('ajakan')->nullable()->after('video_url');
            $table->json('materi')->nullable()->after('ajakan');
        });
    }

    public function down(): void
    {
        Schema::table('promo_campaigns', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'ajakan', 'materi']);
        });
    }
};
