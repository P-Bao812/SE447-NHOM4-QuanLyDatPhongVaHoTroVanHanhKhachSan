<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Khach_Hang extends Model
{
    //
    protected $table = 'Khach_Hang';
    protected $fillable = [
        'maTaiKhoan',
        'hoTen',
        'cccd',
        'email',
        'soDienThoai',
        'diaChi'
    ];
}
