<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cho phép dia_chi_id = NULL để hỗ trợ lưu bài viết nháp chưa có địa chỉ.
     */
    public function up(): void
    {
        Schema::table('bat_dong_sans', function (Blueprint $table) {
            $table->unsignedBigInteger('dia_chi_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bat_dong_sans', function (Blueprint $table) {
            $table->unsignedBigInteger('dia_chi_id')->nullable(false)->change();
        });
    }
};
