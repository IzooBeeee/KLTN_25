<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThongBaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moiGioiId = DB::table('moi_giois')->value('id');
        $bdsId = DB::table('bat_dong_sans')->value('id');

        if (!$moiGioiId || !$bdsId) {
            return;
        }

        DB::table('thong_baos')->insert([
            [
                'moi_gioi_id' => $moiGioiId,
                'khach_hang_id' => null,
                'bat_dong_san_id' => $bdsId,
                'tieu_de' => 'Tin đăng được duyệt',
                'noi_dung' => 'Tin đăng của bạn đã được phê duyệt và đang hiển thị.',
                'trang_thai' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
