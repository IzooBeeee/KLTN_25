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
        Schema::table('dia_chis', function (Blueprint $table) {
            $table->foreignId('phuong_xa_id')->nullable()->constrained('phuong_xas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dia_chis', function (Blueprint $table) {
            $table->dropForeign(['phuong_xa_id']);
            $table->dropColumn('phuong_xa_id');
        });
    }
};
