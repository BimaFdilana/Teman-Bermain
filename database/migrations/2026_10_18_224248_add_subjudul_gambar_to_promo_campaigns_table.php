<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sub-judul & gambar banner untuk halaman iklan ala sales page.
     */
    public function up(): void
    {
        Schema::table('promo_campaigns', function (Blueprint $table) {
            $table->string('subjudul', 200)->nullable()->after('judul');
            $table->string('gambar_path')->nullable()->after('deskripsi');
        });
    }

    public function down(): void
    {
        Schema::table('promo_campaigns', function (Blueprint $table) {
            $table->dropColumn(['subjudul', 'gambar_path']);
        });
    }
};
