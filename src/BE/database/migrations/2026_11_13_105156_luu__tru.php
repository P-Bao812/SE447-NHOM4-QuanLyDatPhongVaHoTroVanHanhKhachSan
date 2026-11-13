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
        Schema::create('Luu_Tru', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maChiTiet');
            $table->dateTime('thoiGianNhan');
            $table->dateTime('thoiGianTra');
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
        Schema::dropIfExists('Luu_Tru');
    }
};
