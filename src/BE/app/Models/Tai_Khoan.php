<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tai_Khoan extends Model
{
    //
    protected $table = 'Tai_Khoan';
    protected $fillable = ['tenDangNhap', 'password', 'hash_password', 'trangThai'];
}
