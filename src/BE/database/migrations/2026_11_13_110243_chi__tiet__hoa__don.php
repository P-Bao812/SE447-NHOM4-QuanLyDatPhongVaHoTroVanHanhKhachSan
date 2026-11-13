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
        Schema::create('Chi_Tiet_Hoa_Don', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maHoaDon');
            $table->string('noiDung', 100);
            $table->integer('soLuong');
            $table->bigInteger('donGia');
            $table->bigInteger('thanhTien');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('Chi_Tiet_Hoa_Don');
    }
};
