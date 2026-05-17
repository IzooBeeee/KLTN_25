<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admins')->insert([
            [
                'ten' => 'Admin Super',
                'email' => 'admin@bds.com',
                'password' => bcrypt('123456'),
                'id_chuc_vu' => 2,
                'is_super' => true,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Admin Duyệt Tin',
                'email' => 'duyet@bds.com',
                'password' => bcrypt('123456789'),
                'id_chuc_vu' => 1,
                'is_super' => false,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ten' => 'Admin Quản Lý',
                'email' => 'quanly@bds.com',
                'password' => bcrypt('123456789'),
                'id_chuc_vu' => 2,
                'is_super' => false,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
