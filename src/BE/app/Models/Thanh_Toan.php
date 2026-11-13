<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thanh_Toan extends Model
{
    //
    protected $table = 'Thanh_Toan';
    protected $fillable = ['maHoaDon', 'soTien', 'phuongThuc', 'maGiaoDich', 'thoiGian', 'trangThai'];
}
