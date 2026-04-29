<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PhuongXa;

class PhuongXaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PhuongXa::create([
            'ten' => 'Phường Thạch Thang',
            'slug' => 'phuong-thach-thang',
            'loai' => 'phuong',
            'quan_huyen_id' => 3, // Hải Châu
        ]);

        PhuongXa::create([
            'ten' => 'Phường An Hải Bắc',
            'slug' => 'phuong-an-hai-bac',
            'loai' => 'phuong',
            'quan_huyen_id' => 4, // Sơn Trà
        ]);
    }
}
