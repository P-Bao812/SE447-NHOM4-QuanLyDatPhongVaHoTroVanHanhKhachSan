<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class San_Pham_Minibar extends Model
{
    //
    protected $table = 'San_Pham_Minibar';
    protected $fillable = ['tenSanPham', 'donGia', 'donViTinh', 'trangThai'];
}
