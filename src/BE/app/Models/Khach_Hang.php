<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Khach_Hang extends Model
{
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
}
