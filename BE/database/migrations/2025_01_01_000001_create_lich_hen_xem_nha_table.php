<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lich_hen_xem_nha', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bat_dong_san_id')->constrained('bat_dong_sans')->onDelete('cascade');
            $table->foreignId('khach_hang_id')->constrained('khach_hangs')->onDelete('cascade');
            $table->foreignId('moi_gioi_id')->constrained('moi_giois')->onDelete('cascade');
            $table->date('ngay_hen');
            $table->time('gio_hen');
            $table->text('ghi_chu')->nullable();
            $table->enum('trang_thai', ['cho_xac_nhan', 'da_xac_nhan', 'hoan_thanh', 'huy'])->default('cho_xac_nhan');
            $table->text('ly_do_huy')->nullable();
            $table->timestamp('xac_nhan_at')->nullable();
            $table->timestamps();

            $table->index(['moi_gioi_id', 'trang_thai']);
            $table->index(['khach_hang_id', 'trang_thai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_hen_xem_nha');
    }
};
