<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_chuc_vu')->nullable();

            $table->string('ten')->index();
            $table->string('email')->unique();
            $table->string('so_dien_thoai')->nullable()->index();
            $table->text('mo_ta')->nullable();
            $table->string('password');

            $table->boolean('is_super')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();

            $table->string('hash_reset')->nullable();
            $table->timestamp('hash_reset_expires_at')->nullable();

            $table->timestamps();

            $table->foreign('id_chuc_vu')
                ->references('id')
                ->on('chuc_vus')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
