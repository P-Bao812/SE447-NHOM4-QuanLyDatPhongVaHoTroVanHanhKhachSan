<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Khuyen_Mai extends Model
{
    //
    protected $table = 'Khuyen_Mai';
    protected $fillable = ['tenKhuyenMai', 'loaiKhuyenMai', 'giaTriGiam', 'ngayBatDau', 'ngayKetThuc', 'dieuKienApDung', 'trangThai'];
}
