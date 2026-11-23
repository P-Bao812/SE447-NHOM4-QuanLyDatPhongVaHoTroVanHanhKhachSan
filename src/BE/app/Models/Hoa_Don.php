<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hoa_Don extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'hoa_don';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'maLuuTru',
        'ngayLap',
        'tongTien',
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
            'maLuuTru'   => 'integer',
            'tongTien'   => 'integer',
            'ngayLap'    => 'datetime',
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
     * Các giao dịch thanh toán thuộc hóa đơn.
     */
    public function thanhToans(): HasMany
    {
        return $this->hasMany(Thanh_Toan::class, 'maHoaDon', 'id');
    }

    /**
     * Danh sách các khoản mục chi tiết trong hóa đơn.
     */
    public function chiTietHoaDons(): HasMany
    {
        return $this->hasMany(Chi_Tiet_Hoa_Don::class, 'maHoaDon', 'id');
    }
}
