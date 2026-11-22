<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dat_Phong extends Model
{
    protected $table = 'Dat_Phong';

    protected $fillable = [
        'maKhachHang',
        'maNhanVien',
        'ngayDat',
        'ngayNhanPhong',
        'ngayTraPhong',
        'soLuongKhach',
        'tienCoc',
        'trangThai'
    ];

    public function khachHang()
    {
        return $this->belongsTo(Khach_Hang::class, 'maKhachHang');
    }

    public function chiTietDatPhongs()
    {
        return $this->hasMany(Chi_Tiet_Dat_Phong::class, 'maDatPhong');
    }
}
