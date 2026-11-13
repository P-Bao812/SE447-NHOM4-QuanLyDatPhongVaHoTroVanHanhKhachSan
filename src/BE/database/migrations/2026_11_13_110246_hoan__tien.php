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
        Schema::create('Hoan_Tien', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maThanhToan');
            $table->bigInteger('soTien');
            $table->string('lyDo', 100);
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
        Schema::dropIfExists('Hoan_Tien');
    }
};
