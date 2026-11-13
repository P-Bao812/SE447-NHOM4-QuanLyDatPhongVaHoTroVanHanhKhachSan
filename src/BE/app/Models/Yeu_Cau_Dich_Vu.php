<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yeu_Cau_Dich_Vu extends Model
{
    //
    protected $table = 'Yeu_Cau_Dich_Vu';
    protected $fillable = ['maLuuTru', 'maDichVu', 'soLuong', 'donGiaApDung', 'ngayYeuCau', 'trangThai', 'ghiChu'];
}
