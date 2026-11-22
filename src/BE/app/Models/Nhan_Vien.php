<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nhan_Vien extends Model
{
    protected $table = 'Nhan_Vien';

    protected $fillable = [
        'maNhanVien',
        'maTaiKhoan',
        'hoTen',
        'email',
        'ngaySinh',
        'cccd',
        'soDienThoai',
        'chucVu',
        'boPhan',
        'caLam',
        'ngayGiaNhap',
        'nguoiQuanLy',
        'ghiChu',
        'trangThai'
    ];

    public function phieuBuongPhongs()
    {
        return $this->hasMany(Phieu_Buong_Phong::class, 'maNhanVien');
    }

    public function taiKhoan()
    {
        return $this->belongsTo(Tai_Khoan::class, 'maTaiKhoan', 'id');
    }
}
