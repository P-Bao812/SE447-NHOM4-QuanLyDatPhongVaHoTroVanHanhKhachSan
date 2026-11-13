<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phieu_Buong_Phong extends Model
{
    //
    protected $table = 'Phieu_Buong_Phong';
    protected $fillable = ['maPhong', 'maNhanVien', 'ngayTao', 'trangThai', 'ghiChu'];
}
