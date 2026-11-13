<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hoan_Tien extends Model
{
    //
    protected $table = 'Hoan_tien';
    protected $fillable = ['maThanhToan', 'soTien', 'lyDo', 'thoiGian', 'trangThai'];
}
