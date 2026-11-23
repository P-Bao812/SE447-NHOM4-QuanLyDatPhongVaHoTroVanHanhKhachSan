<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Yeu_Cau_Dich_Vu extends Model
{
    use HasFactory;

    protected $table = 'yeu_cau_dich_vu';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'maLuuTru',
        'maDichVu',
        'soLuong',
        'donGiaApDung',
        'ngayYeuCau',
        'trangThai',
        'ghiChu',
    ];

    protected $casts = [
        'id'           => 'integer',
        'maLuuTru'     => 'integer',
        'maDichVu'     => 'integer',
        'soLuong'      => 'integer',
        'donGiaApDung' => 'integer',
        'ngayYeuCau'   => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * Dịch vụ tương ứng
     */
    public function dichVu(): BelongsTo
    {
        return $this->belongsTo(Dich_Vu::class, 'maDichVu');
    }

    /**
     * Đợt lưu trú tương ứng
     */
    public function luuTru(): BelongsTo
    {
        return $this->belongsTo(Luu_Tru::class, 'maLuuTru');
    }
}
