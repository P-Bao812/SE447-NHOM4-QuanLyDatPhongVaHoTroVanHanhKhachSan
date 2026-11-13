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
        Schema::create('Khuyen_Mai', function (Blueprint $table) {
            $table->id();

            $table->string('tenKhuyenMai', 100);
            $table->string('loaiKhuyenMai', 20);
            $table->integer('giaTriGiam');
            $table->dateTime('ngayBatDau');
            $table->dateTime('ngayKetThuc');
            $table->string('dieuKienApDung', 100);
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
        Schema::dropIfExists('Khuyen_Mai');
    }
};
