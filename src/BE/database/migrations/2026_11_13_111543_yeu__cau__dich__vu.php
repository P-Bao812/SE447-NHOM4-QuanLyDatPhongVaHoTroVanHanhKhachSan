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
        Schema::create('Yeu_Cau_Dich_Vu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maLuuTru');
            $table->unsignedBigInteger('maDichVu');
            $table->integer('soLuong');
            $table->bigInteger('donGiaApDung');
            $table->dateTime('ngayYeuCau');
            $table->string('trangThai', 20);
            $table->string('ghiChu', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('Yeu_Cau_Dich_Vu');
    }
};
