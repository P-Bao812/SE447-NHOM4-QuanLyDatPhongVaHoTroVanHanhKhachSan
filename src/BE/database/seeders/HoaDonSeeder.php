<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HoaDonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('hoa_don')->delete();
        $now = Carbon::now();

        DB::table('hoa_don')->insert([
            [
                'id' => 1,
                'maLuuTru' => 1,
                'ngayLap' => $now,
                'tongTien' => 2400000,
                'trangThai' => 'Chờ thanh toán',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
