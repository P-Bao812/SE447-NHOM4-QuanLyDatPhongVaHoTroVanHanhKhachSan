<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hoa_Don extends Model
{
    //
    protected $table = 'Hoa_Don';
    protected $fillable = ['maLuuTru', 'ngayLap', 'tongTien', 'trangThai'];
}
