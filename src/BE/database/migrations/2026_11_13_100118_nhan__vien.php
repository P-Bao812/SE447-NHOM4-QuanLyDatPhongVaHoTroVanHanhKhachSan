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
        //maTaiKhoan FK, hoTen, ngaySinh, cccd, soDienThoai, chucVu, trangThaiLamViec
        Schema::create('Nhan_Vien', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maTaiKhoan');
            $table->string('hoTen', 100);
            $table->string('ngaySinh');
            $table->string('cccd', 20)->unique();
            $table->string('soDienThoai', 15);
            $table->string('chucVu', 50);
            $table->string('trangThai', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('Nhan_Vien');
    }
};
