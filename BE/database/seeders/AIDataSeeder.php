<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BatDongSan;
use App\Models\DiaChi;
use App\Models\TinhThanh;
use App\Models\QuanHuyen;
use App\Models\MoiGioi;
use Faker\Factory as Faker;

class AIDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('vi_VN');
        
        // Find or create Da Nang
        $tinh = TinhThanh::where('ten', 'like', '%Đà Nẵng%')->first();
        if (!$tinh) {
            $tinh = TinhThanh::first();
        }
        
        if (!$tinh) {
            echo "No TinhThanh found\n";
            return;
        }

        $quanLienChieu = QuanHuyen::where('tinh_id', $tinh->id)->where('ten', 'like', '%Liên Chiểu%')->first();
        $quanHaiChau = QuanHuyen::where('tinh_id', $tinh->id)->where('ten', 'like', '%Hải Châu%')->first();
        
        $quans = array_filter([$quanLienChieu, $quanHaiChau]);
        if (empty($quans)) {
            $quans = QuanHuyen::where('tinh_id', $tinh->id)->limit(5)->get()->all();
        }
        
        if (empty($quans)) {
            $quans = QuanHuyen::limit(5)->get()->all();
        }

        $moiGioi = MoiGioi::first();
        if (!$moiGioi) {
            echo "No Moi Gioi found. Please create one.\n";
            return;
        }

        echo "Seeding 100 properties for AI training...\n";

        for ($i = 0; $i < 100; $i++) {
            $quan = $faker->randomElement($quans);
            
            $diaChi = DiaChi::create([
                'tinh_id' => $tinh->id,
                'quan_id' => $quan ? $quan->id : null,
                'dia_chi_chi_tiet' => $faker->streetAddress,
                'latitude' => 16.0 + (rand(-50, 50) / 1000),
                'longitude' => 108.2 + (rand(-50, 50) / 1000),
            ]);

            // Randomize loai_id (1: Căn hộ, 2: Nhà phố, 3: Biệt thự, 4: Đất nền)
            $loai_id = $faker->randomElement([1, 2, 3, 4]);
            
            // Randomize price per sqm based on loai_id
            $basePrices = [
                1 => rand(30, 50) * 1000000, // Căn hộ: 30-50tr/m2
                2 => rand(80, 120) * 1000000, // Nhà phố: 80-120tr/m2
                3 => rand(130, 180) * 1000000, // Biệt thự: 130-180tr/m2
                4 => rand(40, 70) * 1000000, // Đất nền: 40-70tr/m2
            ];
            
            $dien_tich = rand(50, 400);
            $gia = $basePrices[$loai_id] * $dien_tich;
            
            // Random variance
            $gia = $gia * (rand(90, 110) / 100);

            BatDongSan::create([
                'tieu_de' => $faker->sentence(6),
                'mo_ta' => $faker->paragraph,
                'gia' => $gia,
                'dien_tich' => $dien_tich,
                'loai_id' => $loai_id,
                'moi_gioi_id' => $moiGioi->id,
                'dia_chi_id' => $diaChi->id,
                'so_phong_ngu' => rand(1, 5),
                'so_phong_tam' => rand(1, 4),
                'is_duyet' => 1,
                'trang_thai_id' => 2, // Đang bán
            ]);
        }
        
        echo "Done seeding AI properties!\n";
    }
}
