<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GiaoDichSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paidAt1 = Carbon::now()->subDays(30);
        $paidAt2 = Carbon::now()->subDays(20);
        $paidAt4 = Carbon::now()->subDays(15);

        DB::table('giao_dichs')->insertOrIgnore([
            [
                'moi_gioi_id' => 1,
                'goi_tin_id' => 1,
                'so_tien' => 50000,
                'phuong_thuc' => 'bank',
                'trang_thai' => 'success',
                'paid_at' => $paidAt1,
                'ma_giao_dich' => 'GD001-SEED',
                'created_at' => $paidAt1,
                'updated_at' => $paidAt1,
            ],
            [
                'moi_gioi_id' => 2,
                'goi_tin_id' => 2,
                'so_tien' => 100000,
                'phuong_thuc' => 'bank',
                'trang_thai' => 'success',
                'paid_at' => $paidAt2,
                'ma_giao_dich' => 'GD002-SEED',
                'created_at' => $paidAt2,
                'updated_at' => $paidAt2,
            ],
            [
                'moi_gioi_id' => 3,
                'goi_tin_id' => 3,
                'so_tien' => 250000,
                'phuong_thuc' => 'bank',
                'trang_thai' => 'pending',
                'paid_at' => null,
                'ma_giao_dich' => 'GD003-SEED',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'moi_gioi_id' => 4,
                'goi_tin_id' => 2,
                'so_tien' => 100000,
                'phuong_thuc' => 'bank',
                'trang_thai' => 'success',
                'paid_at' => $paidAt4,
                'ma_giao_dich' => 'GD004-SEED',
                'created_at' => $paidAt4,
                'updated_at' => $paidAt4,
            ],
            [
                'moi_gioi_id' => 1,
                'goi_tin_id' => 4,
                'so_tien' => 500000,
                'phuong_thuc' => 'bank',
                'trang_thai' => 'failed',
                'paid_at' => null,
                'ma_giao_dich' => 'GD005-SEED',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
