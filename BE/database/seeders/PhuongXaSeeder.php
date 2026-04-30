<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhuongXaSeeder extends Seeder
{
    /**
     * Seed toàn bộ phường/xã của Đà Nẵng.
     * ID quận huyện theo thứ tự trong QuanHuyenSeeder:
     *   1 = Liên Chiểu
     *   2 = Thanh Khê
     *   3 = Hải Châu
     *   4 = Sơn Trà
     *   5 = Ngũ Hành Sơn
     *   6 = Cẩm Lệ
     *   7 = Hòa Vang
     */
    public function run(): void
    {
        $now = now();

        $data = [
            // ===== LIÊN CHIỂU (quan_huyen_id = 1) =====
            ['ten' => 'Phường Hòa Hiệp Bắc',  'slug' => 'phuong-hoa-hiep-bac',  'loai' => 'phuong', 'quan_huyen_id' => 1],
            ['ten' => 'Phường Hòa Hiệp Nam',   'slug' => 'phuong-hoa-hiep-nam',  'loai' => 'phuong', 'quan_huyen_id' => 1],
            ['ten' => 'Phường Hòa Khánh Bắc',  'slug' => 'phuong-hoa-khanh-bac', 'loai' => 'phuong', 'quan_huyen_id' => 1],
            ['ten' => 'Phường Hòa Khánh Nam',   'slug' => 'phuong-hoa-khanh-nam', 'loai' => 'phuong', 'quan_huyen_id' => 1],
            ['ten' => 'Phường Hòa Minh',         'slug' => 'phuong-hoa-minh',      'loai' => 'phuong', 'quan_huyen_id' => 1],

            // ===== THANH KHÊ (quan_huyen_id = 2) =====
            ['ten' => 'Phường Tam Thuận',        'slug' => 'phuong-tam-thuan',     'loai' => 'phuong', 'quan_huyen_id' => 2],
            ['ten' => 'Phường Thanh Khê Tây',    'slug' => 'phuong-thanh-khe-tay', 'loai' => 'phuong', 'quan_huyen_id' => 2],
            ['ten' => 'Phường Thanh Khê Đông',   'slug' => 'phuong-thanh-khe-dong','loai' => 'phuong', 'quan_huyen_id' => 2],
            ['ten' => 'Phường Xuân Hà',           'slug' => 'phuong-xuan-ha',       'loai' => 'phuong', 'quan_huyen_id' => 2],
            ['ten' => 'Phường Tân Chính',         'slug' => 'phuong-tan-chinh',     'loai' => 'phuong', 'quan_huyen_id' => 2],
            ['ten' => 'Phường Chính Gián',        'slug' => 'phuong-chinh-gian',    'loai' => 'phuong', 'quan_huyen_id' => 2],
            ['ten' => 'Phường Vĩnh Trung',        'slug' => 'phuong-vinh-trung',    'loai' => 'phuong', 'quan_huyen_id' => 2],
            ['ten' => 'Phường Thạc Gián',         'slug' => 'phuong-thac-gian',     'loai' => 'phuong', 'quan_huyen_id' => 2],
            ['ten' => 'Phường An Khê',             'slug' => 'phuong-an-khe',        'loai' => 'phuong', 'quan_huyen_id' => 2],
            ['ten' => 'Phường Hòa Khê',            'slug' => 'phuong-hoa-khe',       'loai' => 'phuong', 'quan_huyen_id' => 2],

            // ===== HẢI CHÂU (quan_huyen_id = 3) =====
            ['ten' => 'Phường Thanh Bình',        'slug' => 'phuong-thanh-binh',    'loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Thuận Phước',       'slug' => 'phuong-thuan-phuoc',   'loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Thạch Thang',       'slug' => 'phuong-thach-thang',   'loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Hải Châu I',         'slug' => 'phuong-hai-chau-i',    'loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Hải Châu II',        'slug' => 'phuong-hai-chau-ii',   'loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Phước Ninh',         'slug' => 'phuong-phuoc-ninh',    'loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Hòa Thuận Tây',     'slug' => 'phuong-hoa-thuan-tay', 'loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Hòa Thuận Đông',    'slug' => 'phuong-hoa-thuan-dong','loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Nam Dương',          'slug' => 'phuong-nam-duong',     'loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Bình Hiên',          'slug' => 'phuong-binh-hien',     'loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Bình Thuận',         'slug' => 'phuong-binh-thuan',    'loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Hòa Cường Bắc',     'slug' => 'phuong-hoa-cuong-bac', 'loai' => 'phuong', 'quan_huyen_id' => 3],
            ['ten' => 'Phường Hòa Cường Nam',      'slug' => 'phuong-hoa-cuong-nam', 'loai' => 'phuong', 'quan_huyen_id' => 3],

            // ===== SƠN TRÀ (quan_huyen_id = 4) =====
            ['ten' => 'Phường Thọ Quang',          'slug' => 'phuong-tho-quang',     'loai' => 'phuong', 'quan_huyen_id' => 4],
            ['ten' => 'Phường Mân Thái',            'slug' => 'phuong-man-thai',      'loai' => 'phuong', 'quan_huyen_id' => 4],
            ['ten' => 'Phường An Hải Bắc',          'slug' => 'phuong-an-hai-bac',    'loai' => 'phuong', 'quan_huyen_id' => 4],
            ['ten' => 'Phường Phước Mỹ',            'slug' => 'phuong-phuoc-my',      'loai' => 'phuong', 'quan_huyen_id' => 4],
            ['ten' => 'Phường An Hải Tây',          'slug' => 'phuong-an-hai-tay',    'loai' => 'phuong', 'quan_huyen_id' => 4],
            ['ten' => 'Phường An Hải Đông',         'slug' => 'phuong-an-hai-dong',   'loai' => 'phuong', 'quan_huyen_id' => 4],
            ['ten' => 'Phường Nại Hiên Đông',       'slug' => 'phuong-nai-hien-dong', 'loai' => 'phuong', 'quan_huyen_id' => 4],

            // ===== NGŨ HÀNH SƠN (quan_huyen_id = 5) =====
            ['ten' => 'Phường Mỹ An',               'slug' => 'phuong-my-an',         'loai' => 'phuong', 'quan_huyen_id' => 5],
            ['ten' => 'Phường Khuê Mỹ',             'slug' => 'phuong-khue-my',       'loai' => 'phuong', 'quan_huyen_id' => 5],
            ['ten' => 'Phường Hòa Hải',              'slug' => 'phuong-hoa-hai',       'loai' => 'phuong', 'quan_huyen_id' => 5],
            ['ten' => 'Phường Hòa Quý',              'slug' => 'phuong-hoa-quy',       'loai' => 'phuong', 'quan_huyen_id' => 5],

            // ===== CẨM LỆ (quan_huyen_id = 6) =====
            ['ten' => 'Phường Khuê Trung',           'slug' => 'phuong-khue-trung',    'loai' => 'phuong', 'quan_huyen_id' => 6],
            ['ten' => 'Phường Hòa Phát',             'slug' => 'phuong-hoa-phat',      'loai' => 'phuong', 'quan_huyen_id' => 6],
            ['ten' => 'Phường Hòa An',               'slug' => 'phuong-hoa-an',        'loai' => 'phuong', 'quan_huyen_id' => 6],
            ['ten' => 'Phường Hòa Thọ Tây',         'slug' => 'phuong-hoa-tho-tay',   'loai' => 'phuong', 'quan_huyen_id' => 6],
            ['ten' => 'Phường Hòa Thọ Đông',        'slug' => 'phuong-hoa-tho-dong',  'loai' => 'phuong', 'quan_huyen_id' => 6],
            ['ten' => 'Phường Hòa Xuân',             'slug' => 'phuong-hoa-xuan',      'loai' => 'phuong', 'quan_huyen_id' => 6],

            // ===== HÒA VANG (quan_huyen_id = 7) =====
            ['ten' => 'Xã Hòa Bắc',                  'slug' => 'xa-hoa-bac',           'loai' => 'xa', 'quan_huyen_id' => 7],
            ['ten' => 'Xã Hòa Liên',                  'slug' => 'xa-hoa-lien',          'loai' => 'xa', 'quan_huyen_id' => 7],
            ['ten' => 'Xã Hòa Ninh',                  'slug' => 'xa-hoa-ninh',          'loai' => 'xa', 'quan_huyen_id' => 7],
            ['ten' => 'Xã Hòa Sơn',                   'slug' => 'xa-hoa-son',           'loai' => 'xa', 'quan_huyen_id' => 7],
            ['ten' => 'Xã Hòa Nhơn',                  'slug' => 'xa-hoa-nhon',          'loai' => 'xa', 'quan_huyen_id' => 7],
            ['ten' => 'Xã Hòa Phú',                   'slug' => 'xa-hoa-phu',           'loai' => 'xa', 'quan_huyen_id' => 7],
            ['ten' => 'Xã Hòa Phong',                 'slug' => 'xa-hoa-phong',         'loai' => 'xa', 'quan_huyen_id' => 7],
            ['ten' => 'Xã Hòa Khương',                'slug' => 'xa-hoa-khuong',        'loai' => 'xa', 'quan_huyen_id' => 7],
            ['ten' => 'Xã Hòa Tiến',                  'slug' => 'xa-hoa-tien',          'loai' => 'xa', 'quan_huyen_id' => 7],
            ['ten' => 'Xã Hòa Phước',                 'slug' => 'xa-hoa-phuoc',         'loai' => 'xa', 'quan_huyen_id' => 7],
            ['ten' => 'Xã Hòa Châu',                  'slug' => 'xa-hoa-chau',          'loai' => 'xa', 'quan_huyen_id' => 7],
            ['ten' => 'Xã Hòa Xuân',                  'slug' => 'xa-hoa-xuan-hoa-vang', 'loai' => 'xa', 'quan_huyen_id' => 7],
        ];

        // Thêm timestamps
        foreach ($data as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('phuong_xas')->insert($data);
    }
}
