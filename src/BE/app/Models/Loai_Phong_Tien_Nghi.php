<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loai_Phong_Tien_Nghi extends Model
{
    //
    protected $table = 'Loai_Phong_Tien_Nghi';
    protected $fillable = ['maLoaiPhong', 'maTienNghi'];
}
