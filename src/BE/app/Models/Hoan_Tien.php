<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hoan_Tien extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'hoan_tien';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'maThanhToan',
        'soTien',
        'lyDo',
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
            'maThanhToan' => 'integer',
            'soTien'      => 'integer',
            'thoiGian'    => 'datetime',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
        ];
    }

    /**
     * Giao dịch thanh toán gốc được hoàn.
     */
    public function thanhToan(): BelongsTo
    {
        return $this->belongsTo(Thanh_Toan::class, 'maThanhToan', 'id');
    }
}
