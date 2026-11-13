<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KhachHangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('khach_hang')->delete();
        $now = Carbon::now();

        DB::table('khach_hang')->insert([
            [
                'id' => 1,
                'maTaiKhoan' => 3,
                'hoTen' => 'Nguyễn Văn Minh',
                'cccd' => '001203001234',
                'email' => 'minhnguyen@gmail.com',
                'soDienThoai' => '0912345678',
                'diaChi' => '123 Lê Lợi, Quận 1, TP.HCM',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
