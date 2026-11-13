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
        Schema::create('Loai_Phong_Tien_Nghi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maLoaiPhong');
            $table->unsignedBigInteger('maTienNghi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('Loai_Phong_Tien_Nghi');
    }
};
