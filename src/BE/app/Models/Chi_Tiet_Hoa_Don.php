<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chi_Tiet_Hoa_Don extends Model
{
    //
    protected $table = 'Chi_Tiet_Hoa_Don';
    protected $fillable = ['maHoaDon', 'noiDung', 'soLuong', 'donGia', 'thanhTien'];
}
