<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HinhAnhBatDongSanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('hinh_anh_bat_dong_sans')->insertOrIgnore([
            // BDS 1 - Căn hộ cao cấp Quận 1
            [
                'bds_id' => 1,
                'url' => 'https://images.pexels.com/photos/34973980/pexels-photo-34973980.jpeg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bds_id' => 1,
                'url' => 'https://file4.batdongsan.com.vn/crop/562x284/2026/03/23/20260323155317-63ba_wm.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // BDS 2 - Nhà phố mặt tiền Quận 7
            [
                'bds_id' => 2,
                'url' => 'https://cms.luatvietnam.vn/uploaded/Images/Original/2018/09/29/nha-dang-the-chap_2909092944.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bds_id' => 2,
                'url' => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // BDS 3 - Đất nền Bình Dương
            [
                'bds_id' => 3,
                'url' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // BDS 4 - Chung cư cao cấp Bình Thạnh
            [
                'bds_id' => 4,
                'url' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bds_id' => 4,
                'url' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // BDS 5 - Villa biển Andromeda Quận 2
            [
                'bds_id' => 5,
                'url' => 'https://images.unsplash.com/photo-1505228395891-9a51e7e86e81?w=800',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bds_id' => 5,
                'url' => 'https://images.unsplash.com/photo-1522499881294-f32e3153c5e9?w=800',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // BDS 6 - Vườn biệt thự Thuận An (chờ duyệt)
            [
                'bds_id' => 6,
                'url' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // BDS 7 - Kho xưởng Tân Bình
            [
                'bds_id' => 7,
                'url' => 'https://images.unsplash.com/photo-1581244277943-fe4a9c777189?w=800',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
