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
        Schema::create('Chi_Tiet_Buong_Phong', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maPhieu');
            $table->string('noiDungCongViec', 100);
            $table->string('trangThai', 20);
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
        Schema::dropIfExists('Chi_Tiet_Buong_Phong');
    }
};
