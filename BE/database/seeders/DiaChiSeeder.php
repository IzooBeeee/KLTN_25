<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiaChiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('dia_chis')->insert([
            [
                'tinh_id' => 1,
                'quan_id' => 3, // Hải Châu
                'dia_chi_chi_tiet' => '123 Bạch Đằng, Phường Thạch Thang',
                'latitude' => 16.0748,
                'longitude' => 108.2240,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tinh_id' => 1,
                'quan_id' => 4, // Sơn Trà
                'dia_chi_chi_tiet' => '456 Phạm Văn Đồng, Phường An Hải Bắc',
                'latitude' => 16.0717,
                'longitude' => 108.2435,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tinh_id' => 1,
                'quan_id' => 2, // Thanh Khê
                'dia_chi_chi_tiet' => '789 Nguyễn Văn Linh, Phường Thạc Gián',
                'latitude' => 16.0612,
                'longitude' => 108.2045,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tinh_id' => 1,
                'quan_id' => 1, // Liên Chiểu
                'dia_chi_chi_tiet' => '321 Tôn Đức Thắng, Phường Hòa Minh',
                'latitude' => 16.0590,
                'longitude' => 108.1585,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tinh_id' => 1,
                'quan_id' => 5, // Ngũ Hành Sơn
                'dia_chi_chi_tiet' => '654 Ngũ Hành Sơn, Phường Mỹ An',
                'latitude' => 16.0365,
                'longitude' => 108.2461,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tinh_id' => 1,
                'quan_id' => 6, // Cẩm Lệ
                'dia_chi_chi_tiet' => '987 Cách Mạng Tháng 8, Phường Khuê Trung',
                'latitude' => 16.0152,
                'longitude' => 108.2078,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
