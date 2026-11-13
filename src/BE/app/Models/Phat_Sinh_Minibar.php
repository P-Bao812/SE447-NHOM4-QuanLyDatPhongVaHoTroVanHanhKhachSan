<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phat_Sinh_Minibar extends Model
{
    //
    protected $table = 'Phat_Sinh_Minibar';
    protected $fillable = ['maLuuTru', 'maSanPham', 'soLuong', 'donGiaApDung', 'thoiGian'];
}
