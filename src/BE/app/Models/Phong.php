<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phong extends Model
{
    //
    protected $table = 'Phong';
    protected $fillable = ['tenPhong', 'tang', 'trangThai'];
}
