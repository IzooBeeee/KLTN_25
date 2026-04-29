<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LichSuGoiTinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy các giao dịch thành công theo mã seed
        $gd1 = DB::table('giao_dichs')->where('ma_giao_dich', 'GD001-SEED')->first();
        $gd2 = DB::table('giao_dichs')->where('ma_giao_dich', 'GD002-SEED')->first();
        $gd4 = DB::table('giao_dichs')->where('ma_giao_dich', 'GD004-SEED')->first();

        $records = [];

        if ($gd1) {
            $goiTin = DB::table('goi_tins')->find($gd1->goi_tin_id);
            $batDau = Carbon::parse($gd1->paid_at);
            $ketThuc = $batDau->copy()->addDays($goiTin->so_ngay ?? 7);
            $records[] = [
                'moi_gioi_id' => $gd1->moi_gioi_id,
                'goi_tin_id'  => $gd1->goi_tin_id,
                'giao_dich_id' => $gd1->id,
                'ngay_bat_dau' => $batDau->toDateString(),
                'ngay_ket_thuc' => $ketThuc->toDateString(),
                'trang_thai' => $ketThuc->isPast() ? 'expired' : 'active',
                'created_at' => $batDau,
                'updated_at' => $batDau,
            ];
        }

        if ($gd2) {
            $goiTin = DB::table('goi_tins')->find($gd2->goi_tin_id);
            $batDau = Carbon::parse($gd2->paid_at);
            $ketThuc = $batDau->copy()->addDays($goiTin->so_ngay ?? 15);
            $records[] = [
                'moi_gioi_id' => $gd2->moi_gioi_id,
                'goi_tin_id'  => $gd2->goi_tin_id,
                'giao_dich_id' => $gd2->id,
                'ngay_bat_dau' => $batDau->toDateString(),
                'ngay_ket_thuc' => $ketThuc->toDateString(),
                'trang_thai' => $ketThuc->isPast() ? 'expired' : 'active',
                'created_at' => $batDau,
                'updated_at' => $batDau,
            ];
        }

        if ($gd4) {
            $goiTin = DB::table('goi_tins')->find($gd4->goi_tin_id);
            $batDau = Carbon::parse($gd4->paid_at);
            $ketThuc = $batDau->copy()->addDays($goiTin->so_ngay ?? 15);
            $records[] = [
                'moi_gioi_id' => $gd4->moi_gioi_id,
                'goi_tin_id'  => $gd4->goi_tin_id,
                'giao_dich_id' => $gd4->id,
                'ngay_bat_dau' => $batDau->toDateString(),
                'ngay_ket_thuc' => $ketThuc->toDateString(),
                'trang_thai' => $ketThuc->isPast() ? 'expired' : 'active',
                'created_at' => $batDau,
                'updated_at' => $batDau,
            ];
        }

        if (!empty($records)) {
            DB::table('lich_su_goi_tins')->insertOrIgnore($records);
        }

        // Cập nhật thông tin gói tin hiện tại cho từng môi giới (dựa trên lịch sử mua)
        foreach ([$gd1, $gd2, $gd4] as $gd) {
            if (!$gd) continue;
            $goiTin = DB::table('goi_tins')->find($gd->goi_tin_id);
            $batDau = Carbon::parse($gd->paid_at);
            $ketThuc = $batDau->copy()->addDays($goiTin->so_ngay ?? 0);

            DB::table('moi_giois')->where('id', $gd->moi_gioi_id)->update([
                'goi_tin_id'       => $gd->goi_tin_id,
                'so_tin_con_lai'   => $goiTin->so_luong_tin ?? 0,
                'ngay_het_han_goi' => $ketThuc,
                'updated_at'       => now(),
            ]);
        }
    }
}
