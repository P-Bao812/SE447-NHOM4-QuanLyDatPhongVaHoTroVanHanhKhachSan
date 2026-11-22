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
        Schema::table('Dich_Vu', function (Blueprint $table) {
            if (!Schema::hasColumn('Dich_Vu', 'loaiDichVu')) {
                $table->string('loaiDichVu', 50)->default('Khác')->nullable()->after('tenDichVu');
            }
            if (!Schema::hasColumn('Dich_Vu', 'donViTinh')) {
                $table->string('donViTinh', 50)->default('Lượt')->nullable()->after('donGia');
            }
            if (!Schema::hasColumn('Dich_Vu', 'hinhAnh')) {
                $table->text('hinhAnh')->nullable()->after('moTa');
            }
        });

        Schema::table('Yeu_Cau_Dich_Vu', function (Blueprint $table) {
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'thoiGianMongMuon')) {
                $table->dateTime('thoiGianMongMuon')->nullable()->after('ngayYeuCau');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Dich_Vu', function (Blueprint $table) {
            $table->dropColumn(['loaiDichVu', 'donViTinh', 'hinhAnh']);
        });

        Schema::table('Yeu_Cau_Dich_Vu', function (Blueprint $table) {
            $table->dropColumn(['thoiGianMongMuon']);
        });
    }
};
