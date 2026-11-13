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
        //
        Schema::create('Danh_Gia', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('maKhachHang');
            $table->bigInteger('maLuuTru');
            $table->integer('diemDanhGia');
            $table->string('noiDung', 50);
            $table->dateTime('ngayDanhGia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('Danh_Gia');
    }
};
