<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Luu_Tru extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'luu_tru';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'maChiTiet',
        'thoiGianNhan',
        'thoiGianTra',
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
            'maChiTiet'    => 'integer',
            'thoiGianNhan' => 'datetime',
            'thoiGianTra'  => 'datetime',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    /**
     * Chi tiết đặt phòng liên kết.
     */
    public function chiTietDatPhong(): BelongsTo
    {
        return $this->belongsTo(Chi_Tiet_Dat_Phong::class, 'maChiTiet', 'id');
    }

    /**
     * Hóa đơn thanh toán phát sinh cho đợt lưu trú.
     */
    public function hoaDon(): HasOne
    {
        return $this->hasOne(Hoa_Don::class, 'maLuuTru', 'id');
    }

    /**
     * Các phiếu nhận và trả phòng phát sinh cho đợt lưu trú này.
     */
    public function phieuNhanTraPhongs(): HasMany
    {
        return $this->hasMany(Phieu_Nhan_Tra_Phong::class, 'maLuuTru', 'id');
    }
}
