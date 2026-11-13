<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tai_San extends Model
{
    //
    protected $table = 'Tai_San';
    protected $fillable = ['maPhong', 'tenTaiSan', 'tinhTrang', 'giaTri'];
}
