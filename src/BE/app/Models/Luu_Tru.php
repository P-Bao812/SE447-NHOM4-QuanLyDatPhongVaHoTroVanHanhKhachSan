<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Luu_Tru extends Model
{
    //
    protected $table = 'Luu_Tru';
    protected $fillable = ['maChiTiet', 'thoiGianNhan', 'thoiGianTra', 'trangThai'];
}
