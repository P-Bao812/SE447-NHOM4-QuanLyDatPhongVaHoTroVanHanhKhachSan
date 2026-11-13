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
        Schema::create('Dat_Phong', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maKhachHang');
            $table->unsignedBigInteger('maNhanVien');
            $table->dateTime('ngayDat');
            $table->dateTime('ngayNhanPhong');
            $table->dateTime('ngayTraPhong');
            $table->integer('soLuongKhach');
            $table->bigInteger('tienCoc');
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
        Schema::dropIfExists('Dat_Phong');
    }
};
