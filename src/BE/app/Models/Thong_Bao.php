<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thong_Bao extends Model
{
    protected $table = 'Thong_Bao';

    protected $fillable = [
        'loai',
        'tieuDe',
        'noiDung',
        'maYeuCauDichVu',
        'maPhieuBuongPhong',
        'vaiTroNhan',
        'maNhanVienNhan',
        'maKhachHangNhan',
        'trangThai'
    ];

    public function yeuCauDichVu()
    {
        return $this->belongsTo(Yeu_Cau_Dich_Vu::class, 'maYeuCauDichVu');
    }

    public function phieuBuongPhong()
    {
        return $this->belongsTo(Phieu_Buong_Phong::class, 'maPhieuBuongPhong');
    }

    public function nhanVien()
    {
        return $this->belongsTo(Nhan_Vien::class, 'maNhanVienNhan');
    }

    public function khachHang()
    {
        return $this->belongsTo(Khach_Hang::class, 'maKhachHangNhan');
    }
}
