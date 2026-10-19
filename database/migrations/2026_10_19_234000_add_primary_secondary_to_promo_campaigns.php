<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_campaigns', function (Blueprint $table) {
            $table->foreignId('produk_primary_id')->nullable()->after('id')
                ->constrained('products')->nullOnDelete()
                ->comment('Produk utama iklan');
            $table->string('tampilan_secondary', 10)->default('card')->after('produk_primary_id')
                ->comment('card|list — tampilan produk tambahan');
            $table->json('urutan_konten')->nullable()->after('testimoni_ss')
                ->comment('Urutan section halaman iklan');
            $table->json('section_sembunyi')->nullable()->after('urutan_konten')
                ->comment('Section yang disembunyikan');
        });

        // Migrasi data lama: produk pertama (urutan terkecil) jadi primary, sisanya secondary
        $kampanyes = DB::table('promo_campaigns')->get(['id']);
        foreach ($kampanyes as $k) {
            $pivots = DB::table('promo_campaign_product')
                ->where('promo_campaign_id', $k->id)
                ->orderBy('urutan')
                ->get(['product_id']);
            if ($pivots->isEmpty()) {
                continue;
            }
            $primary = $pivots->shift();
            DB::table('promo_campaigns')->where('id', $k->id)
                ->update(['produk_primary_id' => $primary->product_id]);
            // Hapus primary dari pivot (pivot kini hanya untuk secondary)
            DB::table('promo_campaign_product')
                ->where('promo_campaign_id', $k->id)
                ->where('product_id', $primary->product_id)
                ->delete();
        }
    }

    public function down(): void
    {
        Schema::table('promo_campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('produk_primary_id');
            $table->dropColumn(['tampilan_secondary', 'urutan_konten', 'section_sembunyi']);
        });
    }
};
