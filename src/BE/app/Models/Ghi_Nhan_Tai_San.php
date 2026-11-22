<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ghi_Nhan_Tai_San extends Model
{
    protected $table = 'Ghi_Nhan_Tai_San';
    protected $fillable = [
        'maPhong',
        'maLuuTru',
        'maTaiSan',
        'soLuong',
        'maNhanVien',
        'loaiPhatSinh',
        'thoiGian',
        'soTienBoiThuong',
        'ghiChu',
        'trangThaiXuLy'
    ];

    public function taiSan()
    {
        return $this->belongsTo(Tai_San::class, 'maTaiSan');
    }

    public function phong()
    {
        return $this->belongsTo(Phong::class, 'maPhong');
    }

    public function luuTru()
    {
        return $this->belongsTo(Luu_Tru::class, 'maLuuTru');
    }

    public function nhanVien()
    {
        return $this->belongsTo(Nhan_Vien::class, 'maNhanVien');
    }
}
