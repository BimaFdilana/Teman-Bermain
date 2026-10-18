<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kampanye iklan/promo multi-produk (1-4 produk) dengan batas waktu.
     */
    public function up(): void
    {
        Schema::create('promo_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->dateTime('mulai_at')->nullable();
            $table->dateTime('berakhir_at');
            $table->string('status')->default('draft'); // draft|aktif
            $table->timestamps();
        });

        Schema::create('promo_campaign_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->unique(['promo_campaign_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_campaign_product');
        Schema::dropIfExists('promo_campaigns');
    }
};
