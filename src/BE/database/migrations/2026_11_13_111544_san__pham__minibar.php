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
        Schema::create('San_Pham_Minibar', function (Blueprint $table) {
            $table->id();
            $table->string('tenSanPham');
            $table->bigInteger('donGia');
            $table->string('donViTinh', 20);
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
        Schema::dropIfExists('San_Pham_Minibar');
    }
};
