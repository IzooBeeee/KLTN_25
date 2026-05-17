<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\QuanHuyen;
use App\Models\PhuongXa;

class GadmLocationSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure all 7 districts exist in quan_huyens
        $districts = [
            'Liên Chiểu', 'Thanh Khê', 'Hải Châu', 'Sơn Trà', 'Ngũ Hành Sơn', 'Cẩm Lệ', 'Hòa Vang'
        ];

        foreach ($districts as $tenQuan) {
            QuanHuyen::firstOrCreate(
                ['ten' => $tenQuan],
                ['tinh_id' => 1] // Assuming Da Nang is tinh_id = 1
            );
        }

        // 2. Read the frontend GeoJSON files to seed phuong_xas
        $subareasDir = base_path('../Frontend/public/geojson/subareas');
        $files = glob($subareasDir . '/*.min.json');

        if (empty($files)) {
            $this->command->error("Không tìm thấy file GeoJSON tại: $subareasDir");
            return;
        }

        $phuongCount = 0;

        foreach ($files as $file) {
            $json = file_get_contents($file);
            $data = json_decode($json, true);

            if (!isset($data['features'])) continue;

            foreach ($data['features'] as $feature) {
                $props = $feature['properties'];
                $rawDistName = $props['district']; // e.g. "LiênChiểu"
                
                // Add spaces before uppercase letters to fix spaceless GADM names
                // e.g. "HòaHiệpBắc" -> "Hòa Hiệp Bắc"
                $rawWardName = $props['name']; 
                $wardName = trim(preg_replace('/([A-ZĐ])/u', ' $1', $rawWardName));
                
                $distNameFix = trim(preg_replace('/([A-ZĐ])/u', ' $1', $rawDistName));
                if ($distNameFix == 'Hòa Vang') $distNameFix = 'Hòa Vang'; // Just making sure
                
                // Find matching quan_huyen_id
                $quan = QuanHuyen::where('ten', $distNameFix)->first();
                if (!$quan) {
                    $this->command->warn("Không tìm thấy Quận/Huyện: $distNameFix cho phường $wardName");
                    continue;
                }

                // Add "Phường " or "Xã " prefix for better display
                $type = $props['type'] ?? 'Phường';
                $fullName = $type . ' ' . $wardName;
                
                // Keep the exact slug from JSON for perfect matching
                $slug = $props['slug'];

                PhuongXa::updateOrCreate(
                    [
                        'slug' => $slug
                    ],
                    [
                        'ten' => $fullName,
                        'loai' => $type,
                        'quan_huyen_id' => $quan->id,
                    ]
                );
                $phuongCount++;
            }
        }

        $this->command->info("Đã đồng bộ thành công $phuongCount phường/xã từ GADM!");
    }
}
