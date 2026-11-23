<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Khach_Hang extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong CSDL (tuân thủ cdio4.sql).
     *
     * @var string
     */
    protected $table = 'khach_hang';

    /**
     * Các trường được phép gán hàng loạt.
     *
     * @var list<string>
     */
    protected $fillable = [
        'maTaiKhoan',
        'hoTen',
        'cccd',
        'email',
        'soDienThoai',
        'diaChi',
    ];

    /**
     * Ép kiểu dữ liệu các thuộc tính.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'maTaiKhoan' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Liên kết tới tài khoản đăng nhập tương ứng.
     */
    public function taiKhoan(): BelongsTo
    {
        return $this->belongsTo(Tai_Khoan::class, 'maTaiKhoan', 'id');
    }

    /**
     * Lịch sử các đơn đặt phòng của khách hàng.
     */
    public function datPhongs(): HasMany
    {
        return $this->hasMany(Dat_Phong::class, 'maKhachHang', 'id');
    }
}

