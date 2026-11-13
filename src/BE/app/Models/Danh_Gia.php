<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Danh_Gia extends Model
{
    //
    protected $table = 'Danh_Gia';
    protected $fillable = ['maKhachHang', 'maLuuTru', 'diemDanhGia', 'noiDung', 'ngayDanhGia'];
}
