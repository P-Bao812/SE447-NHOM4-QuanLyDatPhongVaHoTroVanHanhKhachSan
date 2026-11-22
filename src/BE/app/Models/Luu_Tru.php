<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Luu_Tru extends Model
{
    protected $table = 'Luu_Tru';

    protected $fillable = [
        'maChiTiet',
        'thoiGianNhan',
        'thoiGianTra',
        'trangThai'
    ];

    public function chiTietDatPhong()
    {
        return $this->belongsTo(Chi_Tiet_Dat_Phong::class, 'maChiTiet');
    }

    public function yeuCauDichVus()
    {
        return $this->hasMany(Yeu_Cau_Dich_Vu::class, 'maLuuTru');
    }

    public function hoaDons()
    {
        return $this->hasMany(Hoa_Don::class, 'maLuuTru');
    }
}
