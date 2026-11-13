<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PhongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('phong')->delete();
        $now = Carbon::now();

        DB::table('phong')->insert([
            ['id' => 1, 'maLoaiPhong' => 1, 'tenPhong' => 'P101', 'tang' => 1, 'trangThai' => 'Sẵn sàng', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'maLoaiPhong' => 2, 'tenPhong' => 'P201', 'tang' => 2, 'trangThai' => 'Sẵn sàng', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'maLoaiPhong' => 3, 'tenPhong' => 'P301', 'tang' => 3, 'trangThai' => 'Sẵn sàng', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
