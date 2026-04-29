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
            'ten' => 'Phường Bến Nghé',
            'slug' => 'phuong-ben-nghe',
            'loai' => 'phuong',
            'quan_huyen_id' => 1,
        ]);

        PhuongXa::create([
            'ten' => 'Xã Bình Hưng',
            'slug' => 'xa-binh-hung',
            'loai' => 'xa',
            'quan_huyen_id' => 2,
        ]);
    }
}
