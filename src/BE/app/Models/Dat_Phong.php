<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dat_Phong extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'dat_phong';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'maKhachHang',
        'maNhanVien',
        'ngayDat',
        'ngayNhanPhong',
        'ngayTraPhong',
        'soLuongKhach',
        'tienCoc',
        'trangThai',
    ];

    /**
     * Ép kiểu dữ liệu các thuộc tính.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'maKhachHang'   => 'integer',
            'maNhanVien'    => 'integer',
            'ngayDat'       => 'datetime',
            'ngayNhanPhong' => 'datetime',
            'ngayTraPhong'  => 'datetime',
            'soLuongKhach'  => 'integer',
            'tienCoc'       => 'integer',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
        ];
    }

    /**
     * Khách hàng sở hữu đơn đặt phòng.
     */
    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(Khach_Hang::class, 'maKhachHang', 'id');
    }

    /**
     * Nhân viên / Lễ tân tiếp nhận (nếu có).
     */
    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(Nhan_Vien::class, 'maNhanVien', 'id');
    }

    /**
     * Danh sách phòng chi tiết trong đơn đặt.
     */
    public function chiTietDatPhong(): HasMany
    {
        return $this->hasMany(Chi_Tiet_Dat_Phong::class, 'maDatPhong', 'id');
    }
}
