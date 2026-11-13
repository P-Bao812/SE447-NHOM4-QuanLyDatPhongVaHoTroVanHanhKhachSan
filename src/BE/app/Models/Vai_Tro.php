<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vai_Tro extends Model
{
    //
    protected $table = 'Vai_Tro';
    protected $fillable = [
        'tenVaiTro',
        'moTa'
    ];
}
