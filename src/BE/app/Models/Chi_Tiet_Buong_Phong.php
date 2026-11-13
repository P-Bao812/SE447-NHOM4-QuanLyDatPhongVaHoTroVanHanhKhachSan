<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chi_Tiet_Buong_Phong extends Model
{
    //
    protected $table = 'Chi_Tiet_Buong_Phong';
    protected $fillable = ['maPhieu', 'noiDungCongViec', 'trangThai', 'ghiChu'];
}
