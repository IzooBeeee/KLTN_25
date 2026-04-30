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
            // NEW ADDRESSES FOR 7 NEW PROPERTIES
            [
                'tinh_id' => 1,
                'quan_id' => 3, // Hải Châu
                'dia_chi_chi_tiet' => 'Lô 52, KDC Nguyễn Hữu Thọ, Phường Hòa Cường Bắc',
                'latitude' => 16.0401,
                'longitude' => 108.2140,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tinh_id' => 1,
                'quan_id' => 4, // Sơn Trà
                'dia_chi_chi_tiet' => 'Đường Trần Hưng Đạo, Phường An Hải Tây',
                'latitude' => 16.0681,
                'longitude' => 108.2325,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tinh_id' => 1,
                'quan_id' => 5, // Ngũ Hành Sơn
                'dia_chi_chi_tiet' => 'Võ Nguyên Giáp, Phường Mỹ An',
                'latitude' => 16.0435,
                'longitude' => 108.2511,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tinh_id' => 1,
                'quan_id' => 1, // Liên Chiểu
                'dia_chi_chi_tiet' => 'KĐT Eco Charm, Phường Hòa Hiệp Nam',
                'latitude' => 16.0825,
                'longitude' => 108.1385,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tinh_id' => 1,
                'quan_id' => 2, // Thanh Khê
                'dia_chi_chi_tiet' => 'Nguyễn Tất Thành, Phường Thanh Khê Tây',
                'latitude' => 16.0712,
                'longitude' => 108.1845,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tinh_id' => 1,
                'quan_id' => 7, // Hòa Vang
                'dia_chi_chi_tiet' => 'QL14B, Xã Hòa Khương',
                'latitude' => 15.9652,
                'longitude' => 108.1178,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tinh_id' => 1,
                'quan_id' => 3, // Hải Châu
                'dia_chi_chi_tiet' => 'Nguyễn Văn Linh, Phường Nam Dương',
                'latitude' => 16.0618,
                'longitude' => 108.2120,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
