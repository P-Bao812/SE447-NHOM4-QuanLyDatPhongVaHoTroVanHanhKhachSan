<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaiKhoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('tai_khoan')->delete();
        $now = Carbon::now();

        DB::table('tai_khoan')->insert([
            ['id' => 1, 'tenDangNhap' => 'admin', 'password' => bcrypt('123456'), 'hash_password' => null, 'trangThai' => 'Hoạt động', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'tenDangNhap' => 'letan01', 'password' => bcrypt('123456'), 'hash_password' => null, 'trangThai' => 'Hoạt động', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'tenDangNhap' => 'nguyenvanminh', 'password' => bcrypt('123456'), 'hash_password' => null, 'trangThai' => 'Hoạt động', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
