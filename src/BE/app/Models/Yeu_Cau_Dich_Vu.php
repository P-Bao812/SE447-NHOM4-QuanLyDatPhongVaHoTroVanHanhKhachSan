<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yeu_Cau_Dich_Vu extends Model
{
    protected $table = 'Yeu_Cau_Dich_Vu';

    protected $fillable = [
        'maYeuCauCode',
        'maLuuTru',
        'maDichVu',
        'soLuong',
        'donGiaApDung',
        'ngayYeuCau',
        'thoiGianMongMuon',
        'trangThai',
        'boPhanPhuTrach',
        'maNhanVien',
        'mucDoUuTien',
        'thoiGianHen',
        'lyDoTuChoi',
        'baoSuCo',
        'maPhieuBuongPhong',
        'daGhiHoaDon',
        'thoiGianTiepNhan',
        'thoiGianNhanViec',
        'thoiGianBatDau',
        'thoiGianHoanThanh',
        'ghiChu'
    ];

    public function dichVu()
    {
        return $this->belongsTo(Dich_Vu::class, 'maDichVu');
    }

    public function luuTru()
    {
        return $this->belongsTo(Luu_Tru::class, 'maLuuTru');
    }

    public function nhanVien()
    {
        return $this->belongsTo(Nhan_Vien::class, 'maNhanVien');
    }

    public function phieuBuongPhong()
    {
        return $this->belongsTo(Phieu_Buong_Phong::class, 'maPhieuBuongPhong');
    }
}
