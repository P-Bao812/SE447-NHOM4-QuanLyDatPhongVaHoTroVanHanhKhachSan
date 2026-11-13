<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vai_Tro_Quyen extends Model
{
    //
    protected $table = 'Vai_Tro_Quyen';
    protected $fillable = [
        'maVaiTro',
        'maQuyen'
    ];
}
