<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Minibar_Phong extends Model
{
    protected $table = 'Minibar_Phong';
    protected $fillable = [
        'maPhong',
        'maLuuTru',
        'maSanPham',
        'soLuongTieuChuan',
        'soLuongBanDau',
        'soLuongBoSung',
        'soLuongThucTe',
        'soLuongTieuThu',
        'soLuongChenhLech',
        'lyDoChenhLech',
        'donGiaApDung',
        'thanhTien',
        'trangThai',
        'ghiChu'
    ];

    public function sanPham()
    {
        return $this->belongsTo(San_Pham_Minibar::class, 'maSanPham');
    }

    public function phong()
    {
        return $this->belongsTo(Phong::class, 'maPhong');
    }

    public function luuTru()
    {
        return $this->belongsTo(Luu_Tru::class, 'maLuuTru');
    }
}
