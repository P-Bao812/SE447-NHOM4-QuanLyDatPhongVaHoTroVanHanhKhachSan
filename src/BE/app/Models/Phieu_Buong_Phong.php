<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phieu_Buong_Phong extends Model
{
    protected $table = 'Phieu_Buong_Phong';

    protected $fillable = [
        'maPhong',
        'maYeuCauDichVu',
        'viTri',
        'maNhanVien',
        'tieuDe',
        'loaiCongViec',
        'mucDoUuTien',
        'thoiHan',
        'chiTietTienDo',
        'ketQuaKiemTra',
        'ghiChuKiemTra',
        'thoiGianHoanThanh',
        'nguoiKiemTra',
        'ngayTao',
        'trangThai',
        'ghiChu'
    ];

    public function phong()
    {
        return $this->belongsTo(Phong::class, 'maPhong');
    }

    public function nhanVien()
    {
        return $this->belongsTo(Nhan_Vien::class, 'maNhanVien');
    }

    public function chiTiets()
    {
        return $this->hasMany(Chi_Tiet_Buong_Phong::class, 'maPhieu');
    }

    public function yeuCauDichVu()
    {
        return $this->belongsTo(Yeu_Cau_Dich_Vu::class, 'maYeuCauDichVu');
    }
}
