<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Tai_Khoan extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    //
    protected $table = 'Tai_Khoan';
    protected $fillable = ['tenDangNhap', 'password', 'hash_password', 'trangThai'];
}
