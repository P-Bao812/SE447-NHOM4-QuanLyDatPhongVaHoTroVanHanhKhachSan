<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tien_Nghi extends Model
{
    protected $table = 'Tien_Nghi';
    protected $fillable = ['tenTienNghi', 'moTa'];

    public function loaiPhongs()
    {
        return $this->belongsToMany(Loai_Phong::class, 'Loai_Phong_Tien_Nghi', 'maTienNghi', 'maLoaiPhong');
    }
}
