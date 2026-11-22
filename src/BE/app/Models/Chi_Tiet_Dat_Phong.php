<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chi_Tiet_Dat_Phong extends Model
{
    protected $table = 'Chi_Tiet_Dat_Phong';

    protected $fillable = [
        'maDatPhong',
        'maPhong',
        'giaPhongApDung',
        'soLuongKhach'
    ];

    public function datPhong()
    {
        return $this->belongsTo(Dat_Phong::class, 'maDatPhong');
    }

    public function phong()
    {
        return $this->belongsTo(Phong::class, 'maPhong');
    }

    public function luuTrus()
    {
        return $this->hasMany(Luu_Tru::class, 'maChiTiet');
    }
}
