<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loai_Phong extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'loai_phong';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenLoai',
        'moTa',
        'giaTieuChuan',
        'sucChuaToiDa',
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
            'giaTieuChuan' => 'integer',
            'sucChuaToiDa' => 'integer',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    /**
     * Các phòng thuộc loại phòng này.
     */
    public function phong(): HasMany
    {
        return $this->hasMany(Phong::class, 'maLoaiPhong', 'id');
    }
}
