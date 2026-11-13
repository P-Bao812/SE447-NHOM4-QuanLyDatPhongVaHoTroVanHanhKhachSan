<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //maVaiTro FK, tenDangNhap, matKhauHash, trangThai
        Schema::create('Tai_Khoan', function (Blueprint $table) {
            $table->id();
            $table->string('tenDangNhap');
            $table->string('password');
            $table->string('hash_password')->nullable();
            $table->string('trangThai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('Tai_Khoan');
    }
};
