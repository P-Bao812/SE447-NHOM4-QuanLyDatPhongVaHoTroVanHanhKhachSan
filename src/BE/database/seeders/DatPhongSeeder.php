<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatPhongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('dat_phong')->delete();
        $now = Carbon::now();

        DB::table('dat_phong')->insert([
            [
                'id' => 1,
                'maKhachHang' => 1,
                'maNhanVien' => 1,
                'ngayDat' => $now,
                'ngayNhanPhong' => Carbon::tomorrow(),
                'ngayTraPhong' => Carbon::tomorrow()->addDays(3),
                'soLuongKhach' => 2,
                'tienCoc' => 500000,
                'trangThai' => 'Đã cọc',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
