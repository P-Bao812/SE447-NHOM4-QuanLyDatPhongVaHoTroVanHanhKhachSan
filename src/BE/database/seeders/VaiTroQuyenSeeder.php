<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VaiTroQuyenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('vai_tro')->delete();
        $now = Carbon::now();

        DB::table('vai_tro')->insert([
            ['id' => 1, 'tenVaiTro' => 'Quản lý', 'moTa' => 'Quản lý toàn bộ hệ thống khách sạn', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'tenVaiTro' => 'Lễ tân', 'moTa' => 'Tiếp đón khách, làm thủ tục check-in/check-out', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'tenVaiTro' => 'Buồng phòng', 'moTa' => 'Kiểm tra, dọn dẹp và quản lý tài sản phòng', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'tenVaiTro' => 'Khách hàng', 'moTa' => 'Khách hàng sử dụng dịch vụ đặt phòng', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
