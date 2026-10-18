<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Target penjualan input manual per periode (PRD §6.8, keputusan 18 Okt 2026). */
    public function up(): void
    {
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['minggu', 'bulan', 'tahun']);
            $table->string('periode'); // mis. 2026-W42, 2026-10, 2026
            $table->unsignedBigInteger('target'); // rupiah
            $table->timestamps();

            $table->unique(['tipe', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_targets');
    }
};
