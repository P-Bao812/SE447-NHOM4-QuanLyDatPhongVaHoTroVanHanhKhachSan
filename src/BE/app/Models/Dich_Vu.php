<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dich_Vu extends Model
{
    use HasFactory;

    protected $table = 'dich_vu';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'tenDichVu',
        'moTa',
        'donGia',
        'trangThai',
    ];

    protected $casts = [
        'id'        => 'integer',
        'donGia'    => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quan hệ tới các yêu cầu sử dụng dịch vụ này.
     */
    public function yeuCauDichVus(): HasMany
    {
        return $this->hasMany(Yeu_Cau_Dich_Vu::class, 'maDichVu');
    }
}
