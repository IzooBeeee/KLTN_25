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

                $records[] = [
            'moi_gioi_id' => 22,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 6,
            'ngay_bat_dau' => Carbon::create(2026, 5, 1, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 1, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 1, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 1, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 50,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 7,
            'ngay_bat_dau' => Carbon::create(2026, 5, 9, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 9, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 26,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 8,
            'ngay_bat_dau' => Carbon::create(2026, 5, 6, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 6, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 6, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 6, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 37,
            'goi_tin_id'  => 4,
            'giao_dich_id' => 9,
            'ngay_bat_dau' => Carbon::create(2026, 5, 14, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 14, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 45,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 10,
            'ngay_bat_dau' => Carbon::create(2026, 5, 13, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 13, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 13, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 13, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 39,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 11,
            'ngay_bat_dau' => Carbon::create(2026, 5, 15, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 15, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 19,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 12,
            'ngay_bat_dau' => Carbon::create(2026, 5, 9, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 9, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 30,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 13,
            'ngay_bat_dau' => Carbon::create(2026, 5, 14, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 14, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 11,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 14,
            'ngay_bat_dau' => Carbon::create(2026, 5, 9, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 9, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 45,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 15,
            'ngay_bat_dau' => Carbon::create(2026, 5, 7, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 7, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 7, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 7, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 12,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 16,
            'ngay_bat_dau' => Carbon::create(2026, 5, 11, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 11, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 11, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 11, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 34,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 17,
            'ngay_bat_dau' => Carbon::create(2026, 5, 3, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 3, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 3, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 3, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 9,
            'goi_tin_id'  => 4,
            'giao_dich_id' => 18,
            'ngay_bat_dau' => Carbon::create(2026, 5, 13, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 13, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 13, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 13, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 12,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 19,
            'ngay_bat_dau' => Carbon::create(2026, 5, 4, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 4, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 28,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 20,
            'ngay_bat_dau' => Carbon::create(2026, 5, 8, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 8, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 8, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 8, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 33,
            'goi_tin_id'  => 4,
            'giao_dich_id' => 21,
            'ngay_bat_dau' => Carbon::create(2026, 5, 4, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 4, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 40,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 22,
            'ngay_bat_dau' => Carbon::create(2026, 5, 15, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 15, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 15,
            'goi_tin_id'  => 4,
            'giao_dich_id' => 23,
            'ngay_bat_dau' => Carbon::create(2026, 5, 11, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 11, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 11, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 11, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 29,
            'goi_tin_id'  => 4,
            'giao_dich_id' => 24,
            'ngay_bat_dau' => Carbon::create(2026, 5, 9, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 9, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 16,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 25,
            'ngay_bat_dau' => Carbon::create(2026, 5, 4, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 4, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 33,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 26,
            'ngay_bat_dau' => Carbon::create(2026, 5, 3, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 3, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 3, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 3, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 49,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 27,
            'ngay_bat_dau' => Carbon::create(2026, 5, 4, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 4, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 15,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 28,
            'ngay_bat_dau' => Carbon::create(2026, 5, 4, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 4, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 20,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 29,
            'ngay_bat_dau' => Carbon::create(2026, 5, 10, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 10, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 10, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 10, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 18,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 30,
            'ngay_bat_dau' => Carbon::create(2026, 5, 1, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 1, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 1, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 1, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 21,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 31,
            'ngay_bat_dau' => Carbon::create(2026, 5, 12, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 12, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 12, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 12, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 25,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 32,
            'ngay_bat_dau' => Carbon::create(2026, 5, 3, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 3, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 3, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 3, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 8,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 33,
            'ngay_bat_dau' => Carbon::create(2026, 5, 10, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 10, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 10, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 10, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 30,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 34,
            'ngay_bat_dau' => Carbon::create(2026, 5, 9, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 9, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 44,
            'goi_tin_id'  => 4,
            'giao_dich_id' => 35,
            'ngay_bat_dau' => Carbon::create(2026, 5, 15, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 15, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 47,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 36,
            'ngay_bat_dau' => Carbon::create(2026, 5, 8, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 8, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 8, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 8, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 36,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 37,
            'ngay_bat_dau' => Carbon::create(2026, 5, 1, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 1, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 1, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 1, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 48,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 38,
            'ngay_bat_dau' => Carbon::create(2026, 5, 15, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 15, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 7,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 39,
            'ngay_bat_dau' => Carbon::create(2026, 5, 3, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 3, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 3, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 3, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 40,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 40,
            'ngay_bat_dau' => Carbon::create(2026, 5, 3, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 3, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 3, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 3, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 21,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 41,
            'ngay_bat_dau' => Carbon::create(2026, 5, 8, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 8, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 8, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 8, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 10,
            'goi_tin_id'  => 4,
            'giao_dich_id' => 42,
            'ngay_bat_dau' => Carbon::create(2026, 5, 2, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 2, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 2, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 2, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 10,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 43,
            'ngay_bat_dau' => Carbon::create(2026, 5, 9, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 9, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 37,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 44,
            'ngay_bat_dau' => Carbon::create(2026, 5, 15, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 15, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 33,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 45,
            'ngay_bat_dau' => Carbon::create(2026, 5, 14, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 14, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 40,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 46,
            'ngay_bat_dau' => Carbon::create(2026, 5, 2, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 2, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 2, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 2, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 35,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 47,
            'ngay_bat_dau' => Carbon::create(2026, 5, 8, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 8, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 8, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 8, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 47,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 48,
            'ngay_bat_dau' => Carbon::create(2026, 5, 15, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 15, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 12,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 49,
            'ngay_bat_dau' => Carbon::create(2026, 5, 9, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 9, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 48,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 50,
            'ngay_bat_dau' => Carbon::create(2026, 5, 2, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 2, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 2, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 2, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 28,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 51,
            'ngay_bat_dau' => Carbon::create(2026, 5, 2, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 2, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 2, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 2, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 34,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 52,
            'ngay_bat_dau' => Carbon::create(2026, 5, 15, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 15, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 15, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 16,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 53,
            'ngay_bat_dau' => Carbon::create(2026, 5, 10, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 10, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 10, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 10, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 30,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 54,
            'ngay_bat_dau' => Carbon::create(2026, 5, 14, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 14, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 13,
            'goi_tin_id'  => 4,
            'giao_dich_id' => 55,
            'ngay_bat_dau' => Carbon::create(2026, 5, 14, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 14, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 28,
            'goi_tin_id'  => 1,
            'giao_dich_id' => 56,
            'ngay_bat_dau' => Carbon::create(2026, 5, 13, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 13, 10, 0, 0)->copy()->addDays(7)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 13, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 13, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 48,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 57,
            'ngay_bat_dau' => Carbon::create(2026, 5, 8, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 8, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 8, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 8, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 8,
            'goi_tin_id'  => 4,
            'giao_dich_id' => 58,
            'ngay_bat_dau' => Carbon::create(2026, 5, 14, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 14, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 19,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 59,
            'ngay_bat_dau' => Carbon::create(2026, 5, 14, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 14, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 14, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 30,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 60,
            'ngay_bat_dau' => Carbon::create(2026, 5, 4, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 4, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 4, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 45,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 61,
            'ngay_bat_dau' => Carbon::create(2026, 5, 5, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 5, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 5, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 5, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 18,
            'goi_tin_id'  => 3,
            'giao_dich_id' => 62,
            'ngay_bat_dau' => Carbon::create(2026, 5, 9, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 9, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 9, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 11,
            'goi_tin_id'  => 2,
            'giao_dich_id' => 63,
            'ngay_bat_dau' => Carbon::create(2026, 5, 6, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 6, 10, 0, 0)->copy()->addDays(15)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 6, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 6, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 25,
            'goi_tin_id'  => 4,
            'giao_dich_id' => 64,
            'ngay_bat_dau' => Carbon::create(2026, 5, 5, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 5, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 5, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 5, 10, 0, 0),
        ];
        $records[] = [
            'moi_gioi_id' => 7,
            'goi_tin_id'  => 4,
            'giao_dich_id' => 65,
            'ngay_bat_dau' => Carbon::create(2026, 5, 7, 10, 0, 0)->toDateString(),
            'ngay_ket_thuc' => Carbon::create(2026, 5, 7, 10, 0, 0)->copy()->addDays(30)->toDateString(),
            'trang_thai' => 'active',
            'created_at' => Carbon::create(2026, 5, 7, 10, 0, 0),
            'updated_at' => Carbon::create(2026, 5, 7, 10, 0, 0),
        ];

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
