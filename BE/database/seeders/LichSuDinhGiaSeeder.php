<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LichSuDinhGiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aiId = DB::table('a_i_dinh_gias')->value('id');

        if (!$aiId) {
            return;
        }

        DB::table('lich_su_dinh_gias')->insert([
            [
                'a_i_dinh_gia_id' => $aiId,
                'ket_qua' => 'Seeded result',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
