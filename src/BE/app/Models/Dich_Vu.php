<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dich_Vu extends Model
{
    //
    protected $table = 'Dich_Vu';
    protected $fillable = ['tenDichVu', 'moTa', 'donGia', 'trangThai'];
}
