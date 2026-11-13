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
        Schema::create('Tai_San', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('maPhong');
            $table->string('tenTaiSan', 100);
            $table->string('tinhTrang', 50);
            $table->bigInteger('giaTri');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('Tai_San');
    }
};
