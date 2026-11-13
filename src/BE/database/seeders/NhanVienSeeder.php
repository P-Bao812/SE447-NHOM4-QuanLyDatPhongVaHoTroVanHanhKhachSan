<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NhanVienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('nhan_vien')->delete();
        $now = Carbon::now();

        DB::table('nhan_vien')->insert([
            [
                'id' => 1,
                'maTaiKhoan' => 1,
                'hoTen' => 'Trần Văn Quản Lý',
                'ngaySinh' => '1990-05-15',
                'cccd' => '001190005555',
                'soDienThoai' => '0988777666',
                'chucVu' => 'Quản lý',
                'trangThai' => 'Đang làm',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
