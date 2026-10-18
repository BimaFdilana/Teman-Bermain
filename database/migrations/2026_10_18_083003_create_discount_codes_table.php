<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Kode diskon (PRD §6.6). */
    public function up(): void
    {
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->enum('tipe', ['persen', 'nominal']);
            $table->unsignedInteger('nilai'); // persen 1-100 atau rupiah
            $table->unsignedInteger('kuota')->nullable(); // null = tanpa batas
            $table->unsignedInteger('terpakai')->default(0);
            $table->timestamp('mulai')->nullable();
            $table->timestamp('berakhir')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
