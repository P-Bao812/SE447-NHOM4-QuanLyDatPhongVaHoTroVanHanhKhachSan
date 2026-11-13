<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ghi_Nhan_Tai_San extends Model
{
    //
    protected $table = 'Ghi_Nhan_Tai_San';
    protected $fillable = ['maLuuTru', 'maTaiSan', 'loaiPhatSinh', 'thoiGian', 'soTienBoiThuong', 'ghiChu'];
}
