<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Phong extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'phong';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'maLoaiPhong',
        'tenPhong',
        'tang',
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
            'maLoaiPhong' => 'integer',
            'tang'        => 'integer',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
        ];
    }

    /**
     * Loại phòng của phòng này.
     */
    public function loaiPhong(): BelongsTo
    {
        return $this->belongsTo(Loai_Phong::class, 'maLoaiPhong', 'id');
    }

    /**
     * Các chi tiết đặt phòng liên quan tới phòng này.
     */
    public function chiTietDatPhong(): HasMany
    {
        return $this->hasMany(Chi_Tiet_Dat_Phong::class, 'maPhong', 'id');
    }

    /**
     * Danh sách tài sản trang bị tại phòng này.
     */
    public function taiSan(): HasMany
    {
        return $this->hasMany(Tai_San::class, 'maPhong', 'id');
    }

    /**
     * Danh sách phiếu buồng phòng của phòng này.
     */
    public function phieuBuongPhong(): HasMany
    {
        return $this->hasMany(Phieu_Buong_Phong::class, 'maPhong', 'id');
    }
}
