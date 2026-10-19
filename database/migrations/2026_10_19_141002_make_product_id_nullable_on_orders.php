<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * product_id nullable: order multi-item menyimpan produk di order_items.
     * (Perbaikan: migrasi guest checkout lupa me-nullable-kan kolom ini di MySQL.)
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE orders MODIFY product_id BIGINT UNSIGNED NULL');
        }
        // SQLite: sudah nullable dari rebuild di migrasi guest checkout.
    }

    public function down(): void
    {
        // Tidak dikembalikan: data multi-item butuh null.
    }
};
