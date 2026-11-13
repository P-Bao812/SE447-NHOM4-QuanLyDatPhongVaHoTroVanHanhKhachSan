<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phieu_Nhan_Tra_Phong extends Model
{
    //
    protected $table = 'Phieu_Nhan_Tra_Phong';
    protected $fillable = ['maLuuTru', 'maNhanVien', 'loaiPhieu', 'thoiGian', 'ghiChu'];
}
