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
            ['id' => 1, 'tenDangNhap' => 'an@example.com', 'password' => bcrypt('123456'), 'hash_password' => null, 'trangThai' => 'Đang hoạt động', 'created_at' => $now->copy()->subDays(30), 'updated_at' => $now->copy()->subHours(6)],
            ['id' => 2, 'tenDangNhap' => 'linh@example.com', 'password' => bcrypt('123456'), 'hash_password' => null, 'trangThai' => 'Đang hoạt động', 'created_at' => $now->copy()->subDays(20), 'updated_at' => $now->copy()->subMinutes(30)],
            ['id' => 3, 'tenDangNhap' => 'huong@example.com', 'password' => bcrypt('123456'), 'hash_password' => null, 'trangThai' => 'Đang hoạt động', 'created_at' => $now->copy()->subDays(15), 'updated_at' => $now->copy()->subMinutes(45)],
            ['id' => 4, 'tenDangNhap' => 'hoa@example.com', 'password' => bcrypt('123456'), 'hash_password' => null, 'trangThai' => 'Đang hoạt động', 'created_at' => $now->copy()->subDays(10), 'updated_at' => $now->copy()->subMinutes(50)],
            ['id' => 5, 'tenDangNhap' => 'ngoc@example.com', 'password' => bcrypt('123456'), 'hash_password' => null, 'trangThai' => 'Đang hoạt động', 'created_at' => $now->copy()->subDays(12), 'updated_at' => $now->copy()->subHours(2)],
            ['id' => 6, 'tenDangNhap' => 'binh@example.com', 'password' => bcrypt('123456'), 'hash_password' => null, 'trangThai' => 'Đã khóa', 'created_at' => $now->copy()->subDays(40), 'updated_at' => $now->copy()->subDays(7)],
            ['id' => 7, 'tenDangNhap' => 'admin', 'password' => bcrypt('123456'), 'hash_password' => null, 'trangThai' => 'Đang hoạt động', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'tenDangNhap' => 'letan01', 'password' => bcrypt('123456'), 'hash_password' => null, 'trangThai' => 'Đang hoạt động', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
