<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nhan_Vien extends Model
{
    //
    protected $table = 'Nhan_Vien';
    protected $fillable = ['maTaiKhoan', 'hoTen', 'ngaySinh', 'cccd', 'soDienThoai', 'chucVu', 'trangThai'];
}
