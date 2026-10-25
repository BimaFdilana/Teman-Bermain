<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Slogan (subjudul) iklan boleh panjang (hingga 500 karakter).
     * SQLite tidak menegakkan panjang VARCHAR — hanya MySQL yang diubah.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE promo_campaigns MODIFY subjudul TEXT NULL');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE promo_campaigns MODIFY subjudul VARCHAR(200) NULL');
        }
    }
};
