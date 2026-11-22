<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hoa_Don extends Model
{
    protected $table = 'Hoa_Don';

    protected $fillable = [
        'maLuuTru',
        'ngayLap',
        'tongTien',
        'trangThai'
    ];

    public function luuTru()
    {
        return $this->belongsTo(Luu_Tru::class, 'maLuuTru');
    }

    public function chiTietHoaDons()
    {
        return $this->hasMany(Chi_Tiet_Hoa_Don::class, 'maHoaDon');
    }
}
