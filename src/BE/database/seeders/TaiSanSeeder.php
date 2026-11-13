<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaiSanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('tai_san')->delete();
        $now = Carbon::now();

        DB::table('tai_san')->insert([
            ['id' => 1, 'maPhong' => 1, 'tenTaiSan' => 'Tivi Samsung 43 inch', 'tinhTrang' => 'Tốt', 'giaTri' => 7000000, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'maPhong' => 1, 'tenTaiSan' => 'Điều hòa Daikin', 'tinhTrang' => 'Tốt', 'giaTri' => 9000000, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
