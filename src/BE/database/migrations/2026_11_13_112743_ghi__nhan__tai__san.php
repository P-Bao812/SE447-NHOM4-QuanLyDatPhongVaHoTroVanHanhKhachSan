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
        Schema::create('Ghi_Nhan_Tai_San', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('maLuuTru');
            $table->bigInteger('maTaiSan');
            $table->string('loaiPhatSinh', 50);
            $table->dateTime('thoiGian');
            $table->bigInteger('soTienBoiThuong');
            $table->string('ghiChu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('Ghi_Nhan_Tai_San');
    }
};
