<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phieu_Kiem_Ke extends Model
{
    protected $table = 'Phieu_Kiem_Ke';
    protected $fillable = [
        'maPhieu',
        'maPhong',
        'maLuuTru',
        'maNhanVien',
        'tenNhanVien',
        'loaiKiemKe',
        'thoiGian',
        'trangThai',
        'tongTienPhatSinh',
        'ghiChu',
        'chiTiet'
    ];

    protected $casts = [
        'chiTiet' => 'array',
        'thoiGian' => 'datetime'
    ];

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
