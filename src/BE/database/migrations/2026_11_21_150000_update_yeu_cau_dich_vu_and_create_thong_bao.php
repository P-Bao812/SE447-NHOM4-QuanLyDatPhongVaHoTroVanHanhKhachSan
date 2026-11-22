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
        // 1. Cập nhật bảng Yeu_Cau_Dich_Vu
        Schema::table('Yeu_Cau_Dich_Vu', function (Blueprint $table) {
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'maYeuCauCode')) {
                $table->string('maYeuCauCode', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'boPhanPhuTrach')) {
                $table->string('boPhanPhuTrach', 50)->default('Lễ tân')->after('trangThai');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'maNhanVien')) {
                $table->unsignedBigInteger('maNhanVien')->nullable()->after('boPhanPhuTrach');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'mucDoUuTien')) {
                $table->string('mucDoUuTien', 20)->default('Bình thường')->after('maNhanVien');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'thoiGianHen')) {
                $table->string('thoiGianHen', 100)->nullable()->after('mucDoUuTien');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'lyDoTuChoi')) {
                $table->text('lyDoTuChoi')->nullable()->after('thoiGianHen');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'baoSuCo')) {
                $table->text('baoSuCo')->nullable()->after('lyDoTuChoi');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'maPhieuBuongPhong')) {
                $table->unsignedBigInteger('maPhieuBuongPhong')->nullable()->after('baoSuCo');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'daGhiHoaDon')) {
                $table->boolean('daGhiHoaDon')->default(false)->after('maPhieuBuongPhong');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'thoiGianTiepNhan')) {
                $table->dateTime('thoiGianTiepNhan')->nullable()->after('daGhiHoaDon');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'thoiGianNhanViec')) {
                $table->dateTime('thoiGianNhanViec')->nullable()->after('thoiGianTiepNhan');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'thoiGianBatDau')) {
                $table->dateTime('thoiGianBatDau')->nullable()->after('thoiGianNhanViec');
            }
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'thoiGianHoanThanh')) {
                $table->dateTime('thoiGianHoanThanh')->nullable()->after('thoiGianBatDau');
            }
        });

        // 2. Cập nhật bảng Phieu_Buong_Phong để liên kết 2 chiều với Yeu_Cau_Dich_Vu
        Schema::table('Phieu_Buong_Phong', function (Blueprint $table) {
            if (!Schema::hasColumn('Phieu_Buong_Phong', 'maYeuCauDichVu')) {
                $table->unsignedBigInteger('maYeuCauDichVu')->nullable()->after('maPhong');
            }
        });

        // 3. Tạo bảng Thong_Bao để lưu trữ hệ thống thông báo đa vai trò
        if (!Schema::hasTable('Thong_Bao')) {
            Schema::create('Thong_Bao', function (Blueprint $table) {
                $table->id();
                $table->string('loai', 50); // yeu_cau_moi, tiep_nhan, nhan_viec, thuc_hien, hoan_thanh, tu_choi, su_co
                $table->string('tieuDe', 255);
                $table->text('noiDung');
                $table->unsignedBigInteger('maYeuCauDichVu')->nullable();
                $table->unsignedBigInteger('maPhieuBuongPhong')->nullable();
                $table->string('vaiTroNhan', 50)->nullable(); // Admin, Lễ tân, Buồng phòng, Nhà hàng & Bếp, Khách hàng
                $table->unsignedBigInteger('maNhanVienNhan')->nullable();
                $table->unsignedBigInteger('maKhachHangNhan')->nullable();
                $table->string('trangThai', 20)->default('Chua_Xem'); // Chua_Xem, Da_Xem
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Thong_Bao');

        Schema::table('Phieu_Buong_Phong', function (Blueprint $table) {
            if (Schema::hasColumn('Phieu_Buong_Phong', 'maYeuCauDichVu')) {
                $table->dropColumn('maYeuCauDichVu');
            }
        });

        Schema::table('Yeu_Cau_Dich_Vu', function (Blueprint $table) {
            $table->dropColumn([
                'maYeuCauCode',
                'boPhanPhuTrach',
                'maNhanVien',
                'mucDoUuTien',
                'thoiGianHen',
                'lyDoTuChoi',
                'baoSuCo',
                'maPhieuBuongPhong',
                'daGhiHoaDon',
                'thoiGianTiepNhan',
                'thoiGianNhanViec',
                'thoiGianBatDau',
                'thoiGianHoanThanh'
            ]);
        });
    }
};
