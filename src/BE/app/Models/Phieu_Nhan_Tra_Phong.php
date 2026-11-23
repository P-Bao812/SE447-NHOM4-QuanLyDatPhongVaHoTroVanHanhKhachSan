<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Phieu_Nhan_Tra_Phong extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'phieu_nhan_tra_phong';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'maLuuTru',
        'maNhanVien',
        'loaiPhieu',
        'thoiGian',
        'ghiChu',
    ];

    /**
     * Ép kiểu dữ liệu các thuộc tính.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'maLuuTru'   => 'integer',
            'maNhanVien' => 'integer',
            'thoiGian'   => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Đợt lưu trú liên kết.
     */
    public function luuTru(): BelongsTo
    {
        return $this->belongsTo(Luu_Tru::class, 'maLuuTru', 'id');
    }

    /**
     * Nhân viên lễ tân lập phiếu.
     */
    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(Nhan_Vien::class, 'maNhanVien', 'id');
    }
}
