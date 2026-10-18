<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Log login pengguna untuk dashboard admin (PRD §6.3). */
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('login_at');
            $table->index(['login_at', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
