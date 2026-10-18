<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Induk semua produk jualan (PRD §4.8/§8). */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['ebook', 'miniapp', 'bundle']);
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('deskripsi');
            $table->json('fitur')->nullable();
            $table->string('cover_path')->nullable();
            $table->unsignedInteger('harga');
            $table->unsignedInteger('harga_coret')->nullable();
            $table->unsignedInteger('stok')->nullable();
            $table->string('jenjang')->nullable();
            $table->enum('status', ['draft', 'tersedia', 'arsip'])->default('draft');
            $table->timestamps();
            $table->index(['tipe', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
