<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loai_Phong extends Model
{
    //
    protected $table = 'Loai_Phong';
    protected $fillable = ['tenLoai', 'moTa', 'giaTieuChuan', 'sucChuaToiDa', 'trangThai'];
}
