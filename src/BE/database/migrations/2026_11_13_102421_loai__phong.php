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
        Schema::create('Loai_Phong', function (Blueprint $table) {
            $table->id();
            $table->string('tenLoai', 50);
            $table->string('moTa', 100);
            $table->bigInteger('giaTieuChuan');
            $table->integer('sucChuaToiDa');
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
        Schema::dropIfExists('Loai_Phong');
    }
};
