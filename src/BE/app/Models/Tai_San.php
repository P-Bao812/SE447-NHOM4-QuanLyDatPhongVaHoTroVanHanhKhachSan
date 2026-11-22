<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tai_San extends Model
{
    protected $table = 'Tai_San';
    protected $fillable = [
        'maTaiSan',
        'maPhong',
        'tenTaiSan',
        'soLuong',
        'tinhTrang',
        'giaTri',
        'ghiChu'
    ];

    public function phong()
    {
        return $this->belongsTo(Phong::class, 'maPhong');
    }
}
