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
        Schema::create('Thanh_Toan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maHoaDon');
            $table->bigInteger('soTien');
            $table->string('phuongThuc', 50)->comment('tien mat, qr, the');
            $table->string('maGiaoDich')->unique();
            $table->dateTime('thoiGian');
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
        Schema::dropIfExists('Thanh_Toan');
    }
};
