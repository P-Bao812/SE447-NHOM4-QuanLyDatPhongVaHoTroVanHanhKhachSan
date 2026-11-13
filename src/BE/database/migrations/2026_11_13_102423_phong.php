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
        Schema::create('Phong', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maLoaiPhong');
            $table->string('tenPhong', 20)->unique();
            $table->integer('tang');
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
        Schema::dropIfExists('Phong');
    }
};
