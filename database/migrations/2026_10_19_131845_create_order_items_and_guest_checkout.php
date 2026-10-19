<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guest checkout + multi-produk: orders dapat guest fields & user_id nullable;
     * tabel order_items baru (backfill dari order lama).
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildOrdersSqlite();
        } else {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('guest_nama', 100)->nullable()->after('user_id');
                $table->string('guest_email', 150)->nullable()->after('guest_nama');
                $table->string('guest_wa', 20)->nullable()->after('guest_email');
                $table->string('metode_bayar', 10)->default('qris')->after('guest_wa');
                $table->string('bank_va', 20)->nullable()->after('metode_bayar');
            });
            DB::statement('ALTER TABLE orders MODIFY user_id BIGINT UNSIGNED NULL');
        }

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('harga_saat_beli');
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
        });

        // Backfill: order lama (1 produk) -> 1 item
        DB::table('orders')->whereNotNull('product_id')->orderBy('id')->chunkById(200, function ($orders) {
            foreach ($orders as $o) {
                DB::table('order_items')->insert([
                    'order_id' => $o->id,
                    'product_id' => $o->product_id,
                    'harga_saat_beli' => $o->total_bayar,
                    'qty' => 1,
                    'created_at' => $o->created_at,
                    'updated_at' => $o->updated_at,
                ]);
            }
        });
    }

    /**
     * SQLite tanpa doctrine/dbal tidak bisa MODIFY COLUMN: bangun ulang tabel.
     */
    private function rebuildOrdersSqlite(): void
    {
        Schema::create('orders_new', function (Blueprint $table) {
            $table->id();
            $table->string('kode_order')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('total_bayar');
            $table->foreignId('discount_code_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pembayaran', 'verifikasi', 'ditolak', 'disetujui', 'kadaluarsa'])
                ->default('pembayaran');
            $table->string('bukti_path')->nullable();
            $table->unsignedTinyInteger('upload_ke')->default(0);
            $table->text('alasan_tolak')->nullable();
            $table->timestamp('batas_bayar')->nullable();
            $table->string('guest_nama', 100)->nullable();
            $table->string('guest_email', 150)->nullable();
            $table->string('guest_wa', 20)->nullable();
            $table->string('metode_bayar', 10)->default('qris');
            $table->string('bank_va', 20)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });

        DB::statement('INSERT INTO orders_new
            (id, kode_order, user_id, product_id, total_bayar, discount_code_id, status,
             bukti_path, upload_ke, alasan_tolak, batas_bayar, created_at, updated_at)
            SELECT id, kode_order, user_id, product_id, total_bayar, discount_code_id, status,
             bukti_path, upload_ke, alasan_tolak, batas_bayar, created_at, updated_at FROM orders');

        Schema::drop('orders');
        Schema::rename('orders_new', 'orders');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['guest_nama', 'guest_email', 'guest_wa', 'metode_bayar', 'bank_va']);
        });
    }
};
