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
        Schema::table('Nhan_Vien', function (Blueprint $table) {
            if (!Schema::hasColumn('Nhan_Vien', 'maNhanVien')) {
                $table->string('maNhanVien', 20)->nullable()->after('id');
            }
            if (!Schema::hasColumn('Nhan_Vien', 'email')) {
                $table->string('email', 100)->nullable()->after('hoTen');
            }
            if (!Schema::hasColumn('Nhan_Vien', 'boPhan')) {
                $table->string('boPhan', 50)->nullable()->default('Lễ tân')->after('chucVu');
            }
            if (!Schema::hasColumn('Nhan_Vien', 'caLam')) {
                $table->string('caLam', 50)->nullable()->default('Ca sáng (06:00 - 14:00)')->after('boPhan');
            }
            if (!Schema::hasColumn('Nhan_Vien', 'ngayGiaNhap')) {
                $table->string('ngayGiaNhap', 20)->nullable()->after('caLam');
            }
            if (!Schema::hasColumn('Nhan_Vien', 'nguoiQuanLy')) {
                $table->string('nguoiQuanLy', 100)->nullable()->default('Trần An')->after('ngayGiaNhap');
            }
            if (!Schema::hasColumn('Nhan_Vien', 'ghiChu')) {
                $table->text('ghiChu')->nullable()->after('nguoiQuanLy');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Nhan_Vien', function (Blueprint $table) {
            $table->dropColumn([
                'maNhanVien',
                'email',
                'boPhan',
                'caLam',
                'ngayGiaNhap',
                'nguoiQuanLy',
                'ghiChu'
            ]);
        });
    }
};
