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
        Schema::create('Phan_Hoi', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('maDanhGia');
            $table->bigInteger('maNhanVien');
            $table->string('noiDung');
            $table->dateTime('ngayPhanHoi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('Phan_Hoi');
    }
};
