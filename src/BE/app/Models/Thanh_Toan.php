<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thanh_Toan extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'thanh_toan';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'maHoaDon',
        'soTien',
        'phuongThuc',
        'maGiaoDich',
        'thoiGian',
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
            'maHoaDon'   => 'integer',
            'soTien'     => 'integer',
            'thoiGian'   => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Hóa đơn thanh toán liên kết.
     */
    public function hoaDon(): BelongsTo
    {
        return $this->belongsTo(Hoa_Don::class, 'maHoaDon', 'id');
    }

    /**
     * Các lần hoàn tiền liên quan đến giao dịch này.
     */
    public function hoanTiens(): HasMany
    {
        return $this->hasMany(Hoan_Tien::class, 'maThanhToan', 'id');
    }
}
