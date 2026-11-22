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
        Schema::table('Phieu_Buong_Phong', function (Blueprint $table) {
            $table->unsignedBigInteger('maPhong')->nullable()->change();
            $table->string('viTri', 100)->nullable()->after('maPhong');
            $table->string('tieuDe', 150)->nullable()->after('viTri');
            $table->string('loaiCongViec', 50)->default('Dọn phòng')->after('tieuDe');
            $table->string('mucDoUuTien', 20)->default('Vừa')->after('loaiCongViec');
            $table->string('thoiHan', 50)->nullable()->after('mucDoUuTien');
            $table->string('chiTietTienDo', 100)->nullable()->after('thoiHan');
            $table->string('ketQuaKiemTra', 50)->nullable()->after('chiTietTienDo');
            $table->text('ghiChuKiemTra')->nullable()->after('ketQuaKiemTra');
            $table->dateTime('thoiGianHoanThanh')->nullable()->after('ghiChuKiemTra');
            $table->string('nguoiKiemTra', 100)->nullable()->after('thoiGianHoanThanh');
            $table->text('ghiChu')->nullable()->change();
        });

        Schema::table('Phong', function (Blueprint $table) {
            $table->string('ghiChuDonDep', 255)->nullable()->after('trangThai');
            $table->dateTime('thoiGianCheckOut')->nullable()->after('ghiChuDonDep');
            $table->dateTime('thoiGianDonGanNhat')->nullable()->after('thoiGianCheckOut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Phieu_Buong_Phong', function (Blueprint $table) {
            $table->dropColumn([
                'viTri', 'tieuDe', 'loaiCongViec', 'mucDoUuTien',
                'thoiHan', 'chiTietTienDo', 'ketQuaKiemTra',
                'ghiChuKiemTra', 'thoiGianHoanThanh', 'nguoiKiemTra'
            ]);
        });

        Schema::table('Phong', function (Blueprint $table) {
            $table->dropColumn(['ghiChuDonDep', 'thoiGianCheckOut', 'thoiGianDonGanNhat']);
        });
    }
};
