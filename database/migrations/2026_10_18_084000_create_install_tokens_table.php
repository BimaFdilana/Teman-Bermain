<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Token install PWA per (pengguna, MiniApp) — PRD BR-RC-19/20. */
    public function up(): void
    {
        Schema::create('install_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete(); // miniapp
            $table->string('token', 64)->unique();
            $table->timestamp('dipakai_terakhir_at')->nullable();
            $table->boolean('dicabut')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('install_tokens');
    }
};
