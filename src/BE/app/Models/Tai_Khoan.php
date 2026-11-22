<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Tai_Khoan extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Tên bảng tương ứng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'tai_khoan';

    /**
     * Các trường được phép gán hàng loạt (Mass Assignment).
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenDangNhap',
        'password',
        'hash_password',
        'trangThai',
    ];

    /**
     * Các trường bị ẩn khi chuyển đổi Model sang Array/JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'hash_password',
        'remember_token',
    ];

    /**
     * Khai báo kiểu dữ liệu cho các trường.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Liên kết 1-1 với hồ sơ Nhân viên (nếu tài khoản thuộc nhân viên).
     */
    public function nhanVien(): HasOne
    {
        return $this->hasOne(Nhan_Vien::class, 'maTaiKhoan', 'id');
    }

    /**
     * Liên kết 1-1 với hồ sơ Khách hàng (nếu tài khoản thuộc khách hàng).
     */
    public function khachHang(): HasOne
    {
        return $this->hasOne(Khach_Hang::class, 'maTaiKhoan', 'id');
    }

    /**
     * Accessor xác định vai trò của tài khoản (Quản lý, Lễ tân, Buồng phòng, Khách hàng).
     */
    public function getVaiTroAttribute(): string
    {
        // 1. Kiểm tra nếu có hồ sơ nhân viên
        $nhanVien = $this->relationLoaded('nhanVien') ? $this->nhanVien : $this->nhanVien()->first();
        if ($nhanVien && !empty($nhanVien->chucVu)) {
            return $nhanVien->chucVu;
        }

        // 2. Kiểm tra nếu có hồ sơ khách hàng
        $khachHang = $this->relationLoaded('khachHang') ? $this->khachHang : $this->khachHang()->first();
        if ($khachHang) {
            return 'Khách hàng';
        }

        // 3. Fallback dự phòng theo tiền tố tên đăng nhập
        $username = strtolower($this->tenDangNhap ?? '');
        if (str_contains($username, 'admin') || str_contains($username, 'quanly')) {
            return 'Quản lý';
        }
        if (str_contains($username, 'letan')) {
            return 'Lễ tân';
        }
        if (str_contains($username, 'buongphong')) {
            return 'Buồng phòng';
        }

        return 'Khách hàng';
    }

    /**
     * Accessor lấy họ tên người dùng từ hồ sơ liên quan.
     */
    public function getHoTenAttribute(): ?string
    {
        $nhanVien = $this->relationLoaded('nhanVien') ? $this->nhanVien : $this->nhanVien()->first();
        if ($nhanVien && !empty($nhanVien->hoTen)) {
            return $nhanVien->hoTen;
        }

        $khachHang = $this->relationLoaded('khachHang') ? $this->khachHang : $this->khachHang()->first();
        if ($khachHang && !empty($khachHang->hoTen)) {
            return $khachHang->hoTen;
        }

        return $this->tenDangNhap;
    }
}
