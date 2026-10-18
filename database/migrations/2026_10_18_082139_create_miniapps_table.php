<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Detail produk bertipe miniapp: 1 file HTML mandiri (PRD §8). */
    public function up(): void
    {
        Schema::create('miniapps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('html_path')->nullable();
            $table->string('pwa_name')->nullable();
            $table->string('icon_path')->nullable();
            $table->string('versi')->default('1.0.0');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miniapps');
    }
};
