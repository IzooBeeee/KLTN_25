<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('moi_giois', function (Blueprint $table) {
            $table->string('hash_reset')->nullable()->after('password');
            $table->timestamp('hash_reset_expires_at')->nullable()->after('hash_reset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moi_giois', function (Blueprint $table) {
            $table->dropColumn(['hash_reset', 'hash_reset_expires_at']);
        });
    }
};
