<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoaiPhongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('loai_phong')->delete();
        $now = Carbon::now();

        DB::table('loai_phong')->insert([
            ['id' => 1, 'tenLoai' => 'Single Room', 'moTa' => 'Phòng đơn tiêu chuẩn cho 1 người', 'giaTieuChuan' => 500000, 'sucChuaToiDa' => 1, 'trangThai' => 'Kinh doanh', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'tenLoai' => 'Double Room', 'moTa' => 'Phòng đôi cho 2 người', 'giaTieuChuan' => 800000, 'sucChuaToiDa' => 2, 'trangThai' => 'Kinh doanh', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'tenLoai' => 'VIP Suite', 'moTa' => 'Phòng cao cấp đầy đủ tiện nghi sang trọng', 'giaTieuChuan' => 2000000, 'sucChuaToiDa' => 4, 'trangThai' => 'Kinh doanh', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
