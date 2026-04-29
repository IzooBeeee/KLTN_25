<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuanHuyenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('quan_huyens')->insert([
            // Đà Nẵng (tinh_id = 1)
            [
                'ten' => 'Liên Chiểu',
                'tinh_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Thanh Khê',
                'tinh_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Hải Châu',
                'tinh_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Sơn Trà',
                'tinh_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Ngũ Hành Sơn',
                'tinh_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Cẩm Lệ',
                'tinh_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Hòa Vang',
                'tinh_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
