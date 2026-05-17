<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TinhThanhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tinh_thanhs')->insert([
            [
                'ten' => 'Đà Nẵng',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
