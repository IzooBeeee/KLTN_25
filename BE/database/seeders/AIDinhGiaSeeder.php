<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AIDinhGiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moiGioiId = DB::table('moi_giois')->value('id');

        if (!$moiGioiId) {
            return;
        }

        DB::table('a_i_dinh_gias')->insert([
            [
                'moi_gioi_id' => $moiGioiId,
                'dia_chi' => '123 Đường Nguyễn Huệ, TP.HCM',
                'gia_ao' => 3200000000,
                'gia_thap' => 3000000000,
                'gia_cao' => 3500000000,
                'trang_thai' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
