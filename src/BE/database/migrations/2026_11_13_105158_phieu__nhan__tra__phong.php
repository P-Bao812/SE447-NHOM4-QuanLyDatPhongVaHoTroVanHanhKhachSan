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
        Schema::create('Phieu_Nhan_Tra_Phong', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maLuuTru');
            $table->unsignedBigInteger('maNhanVien');
            $table->string('loaiPhieu', 20);
            $table->dateTime('thoiGian');
            $table->string('ghiChu', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('Phieu_Nhan_Tra_Phong');
    }
};
