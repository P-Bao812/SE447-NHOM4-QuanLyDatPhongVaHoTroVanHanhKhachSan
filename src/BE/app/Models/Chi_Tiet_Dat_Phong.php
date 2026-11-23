<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chi_Tiet_Dat_Phong extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'chi_tiet_dat_phong';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'maDatPhong',
        'maPhong',
        'giaPhongApDung',
        'soLuongKhach',
    ];

    /**
     * Ép kiểu dữ liệu các thuộc tính.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'maDatPhong'     => 'integer',
            'maPhong'        => 'integer',
            'giaPhongApDung' => 'integer',
            'soLuongKhach'   => 'integer',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    /**
     * Đơn đặt phòng gốc.
     */
    public function datPhong(): BelongsTo
    {
        return $this->belongsTo(Dat_Phong::class, 'maDatPhong', 'id');
    }

    /**
     * Phòng vật lý được gán.
     */
    public function phong(): BelongsTo
    {
        return $this->belongsTo(Phong::class, 'maPhong', 'id');
    }

    /**
     * Đợt lưu trú tương ứng.
     */
    public function luuTru()
    {
        return $this->hasOne(Luu_Tru::class, 'maChiTiet', 'id');
    }

    /**
     * Danh sách các đợt lưu trú.
     */
    public function luuTrus()
    {
        return $this->hasMany(Luu_Tru::class, 'maChiTiet', 'id');
    }
}
