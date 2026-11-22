<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phong extends Model
{
    protected $table = 'Phong';

    protected $fillable = [
        'maLoaiPhong',
        'tenPhong',
        'tang',
        'trangThai',
        'ghiChuDonDep',
        'thoiGianCheckOut',
        'thoiGianDonGanNhat'
    ];

    public function loaiPhong()
    {
        return $this->belongsTo(Loai_Phong::class, 'maLoaiPhong');
    }

    public function phieuBuongPhongs()
    {
        return $this->hasMany(Phieu_Buong_Phong::class, 'maPhong');
    }

    public function taiSans()
    {
        return $this->hasMany(Tai_San::class, 'maPhong');
    }
}
