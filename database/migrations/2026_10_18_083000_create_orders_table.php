<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Order pembelian (PRD §4.8/§5.4). */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('kode_order')->unique(); // RCB-YYYYMMDD-XXXX
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('total_bayar'); // setelah diskon
            $table->foreignId('discount_code_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pembayaran', 'verifikasi', 'ditolak', 'disetujui', 'kadaluarsa'])
                ->default('pembayaran');
            $table->string('bukti_path')->nullable();
            $table->unsignedTinyInteger('upload_ke')->default(0); // hitung upload bukti
            $table->text('alasan_tolak')->nullable();
            $table->timestamp('batas_bayar')->nullable(); // +24 jam dari order
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
