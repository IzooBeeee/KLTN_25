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
            // NEW IMAGES FOR 7 NEW PROPERTIES
            // BDS 8
            ['bds_id' => 8, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/209142_69eae8cd14e8c.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 8, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/209142_69eae8cda281a.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 8, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/209142_69eae8cde93c8.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 8, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/209142_69eae8ce53fb4.jpg', 'created_at' => now(), 'updated_at' => now()],
            // BDS 9
            ['bds_id' => 9, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207467_69dd00e53f3cd.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 9, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207467_69dd00e5b6338.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 9, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207467_69dd00e5ec533.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 9, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207467_69dd00e631409.jpg', 'created_at' => now(), 'updated_at' => now()],
            // BDS 10
            ['bds_id' => 10, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207373_69dc52148dc14.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 10, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207373_69dc52151bad9.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 10, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207373_69dc5215983a1.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 10, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207373_69dc5215de877.jpg', 'created_at' => now(), 'updated_at' => now()],
            // BDS 11
            ['bds_id' => 11, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207046_69d861e8be198.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 11, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207046_69d861e91930d.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 11, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207046_69d861e9355c3.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 11, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207046_69d861e95067e.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 11, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207046_69d861e96fd95.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 11, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207046_69d861e98a8df.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 11, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207046_69d861e9a6069.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 11, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207046_69d861e9c0a1f.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 11, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/207046_69d861e9d9111.jpg', 'created_at' => now(), 'updated_at' => now()],
            // BDS 12
            ['bds_id' => 12, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/206909_69d717e53e5a7.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 12, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/206909_69d717e59bfa8.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 12, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/206909_69d717e5ba630.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 12, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/206909_69d717e5d688e.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 12, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/206909_69d717e5eb438.jpg', 'created_at' => now(), 'updated_at' => now()],
            // BDS 13
            ['bds_id' => 13, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/143522_67c27ffaf410b.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 13, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/143522_67c27ffb8d5d0.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 13, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/143522_67c27ffbaf030.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 13, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/143522_67c27ffbd1c58.jpg', 'created_at' => now(), 'updated_at' => now()],
            // BDS 14
            ['bds_id' => 14, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/142947_67bee13f43eca.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['bds_id' => 14, 'url' => 'https://media.batdongsan.vn/crop/1275x717/posts/142947_67bee13faa127.jpg', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
