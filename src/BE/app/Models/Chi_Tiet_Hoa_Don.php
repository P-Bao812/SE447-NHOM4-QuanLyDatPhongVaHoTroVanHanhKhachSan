<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chi_Tiet_Hoa_Don extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'chi_tiet_hoa_don';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'maHoaDon',
        'noiDung',
        'soLuong',
        'donGia',
        'thanhTien',
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
            'soLuong'    => 'integer',
            'donGia'     => 'integer',
            'thanhTien'  => 'integer',
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
}

