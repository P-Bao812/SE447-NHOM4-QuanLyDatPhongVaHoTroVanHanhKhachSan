<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dich_Vu extends Model
{
    protected $table = 'Dich_Vu';

    protected $fillable = [
        'tenDichVu',
        'loaiDichVu',
        'moTa',
        'donGia',
        'donViTinh',
        'hinhAnh',
        'trangThai'
    ];

    public function yeuCauDichVus()
    {
        return $this->hasMany(Yeu_Cau_Dich_Vu::class, 'maDichVu');
    }
}
