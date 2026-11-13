<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chi_Tiet_Dat_Phong extends Model
{
    //
    protected $table = 'Chi_Tiet_Dat_Phong';
    protected $fillable = ['maDatPhong', 'maPhong', 'giaPhongApDung', 'soLuongKhach'];
}
