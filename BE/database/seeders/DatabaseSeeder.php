<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Xóa sạch tất cả dữ liệu cũ để seed lại từ đầu
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $tables = [
            'notifications', 'personal_access_tokens',
            'messages', 'conversations',
            'lich_su_dinh_gias', 'a_i_dinh_gias',
            'thong_baos', 'yeu_thichs',
            'lich_su_goi_tins', 'hinh_anh_bat_dong_sans',
            'giao_dichs', 'bat_dong_sans', 'dia_chis',
            'khach_hangs', 'moi_giois', 'admins',
            'goi_tins', 'phan_quyens', 'chuc_nangs', 'chuc_vus',
            'loai_bat_dong_sans', 'trang_thai_bat_dong_sans',
            'unmatched_payments', 'phuong_xas',
            'quan_huyens', 'tinh_thanhs',
            'users',
        ];
        foreach ($tables as $table) {
            \DB::table($table)->truncate();
        }
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Seed dữ liệu cơ bản
        $this->call([
            TinhThanhSeeder::class,
            QuanHuyenSeeder::class,
            PhuongXaSeeder::class,
            ChucVuSeeder::class,
            ChucNangSeeder::class,
            PhanQuyenSeeder::class,
            LoaiBatDongSanSeeder::class,
            TrangThaiBatDongSanSeeder::class,
            GoiTinSeeder::class,
            AdminSeeder::class,
            MoiGioiSeeder::class,
            KhachHangSeeder::class,
            DiaChiSeeder::class,
            BatDongSanSeeder::class,
            GiaoDichSeeder::class,
            HinhAnhBatDongSanSeeder::class,
            LichSuGoiTinSeeder::class,
            YeuThichSeeder::class,
            ThongBaoSeeder::class,
            AIDinhGiaSeeder::class,
            LichSuDinhGiaSeeder::class,
            ChatSeeder::class,
        ]);

        // Seed user test
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
