<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loai_Phong extends Model
{
    protected $table = 'Loai_Phong';
    protected $fillable = ['tenLoai', 'moTa', 'giaTieuChuan', 'sucChuaToiDa', 'trangThai'];

    public function phongs()
    {
        return $this->hasMany(Phong::class, 'maLoaiPhong', 'id');
    }

    public function tienNghis()
    {
        return $this->belongsToMany(Tien_Nghi::class, 'Loai_Phong_Tien_Nghi', 'maLoaiPhong', 'maTienNghi');
    }
}
