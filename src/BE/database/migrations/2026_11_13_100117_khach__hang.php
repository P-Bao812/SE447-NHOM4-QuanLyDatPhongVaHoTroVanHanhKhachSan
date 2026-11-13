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
        //maTaiKhoan FK, hoTen, cccd, email, soDienThoai, diaChi
        Schema::create('Khach_Hang',function(Blueprint $table){
            $table -> id();
            $table -> unsignedBigInteger('maTaiKhoan');
            $table -> string('hoTen',100);
            $table -> string('cccd',20)->unique();
            $table -> string('email',100);
            $table -> string('soDienThoai',20);
            $table -> string('diaChi',255);
             $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
          Schema::drop('Khach_Hang');
    }
};
