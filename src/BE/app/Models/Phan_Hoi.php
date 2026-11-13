<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phan_Hoi extends Model
{
    //
    protected $table = 'Phan_Hoi';
    protected $fillable = ['maDanhGia', 'maNhanVien', 'noiDung', 'ngayPhanHoi'];
}
