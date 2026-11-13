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
        Schema::create('Phat_Sinh_Minibar', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('maLuuTru');
            $table->bigInteger('maSanPham');
            $table->integer('soLuong');
            $table->bigInteger('donGiaApDung');
            $table->dateTime('thoiGian');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('Phat_Sinh_Minibar');
    }
};
