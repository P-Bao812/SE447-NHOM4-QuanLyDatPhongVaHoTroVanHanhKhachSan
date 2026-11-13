<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dat_Phong extends Model
{
    //
    protected $table = 'Dat_Phong';
    protected $fillable = ['maKhachHang', 'maNhanVien', 'ngayDat', 'ngayNhanPhong', 'ngayTraPhong', 'soLuongKhach', 'tienCoc', 'trangThai'];
}
