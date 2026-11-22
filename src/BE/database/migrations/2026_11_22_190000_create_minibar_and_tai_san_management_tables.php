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
        // 1. Cập nhật bảng San_Pham_Minibar
        Schema::table('San_Pham_Minibar', function (Blueprint $table) {
            if (!Schema::hasColumn('San_Pham_Minibar', 'maSanPham')) {
                $table->string('maSanPham', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('San_Pham_Minibar', 'loaiSanPham')) {
                $table->string('loaiSanPham', 20)->default('Tinh_Phi')->after('donViTinh'); // Tinh_Phi hoặc Mien_Phi
            }
            if (!Schema::hasColumn('San_Pham_Minibar', 'nguongToiThieu')) {
                $table->integer('nguongToiThieu')->default(10)->after('loaiSanPham');
            }
            if (!Schema::hasColumn('San_Pham_Minibar', 'tonDau')) {
                $table->integer('tonDau')->default(50)->after('nguongToiThieu');
            }
            if (!Schema::hasColumn('San_Pham_Minibar', 'soLuongNhap')) {
                $table->integer('soLuongNhap')->default(50)->after('tonDau');
            }
            if (!Schema::hasColumn('San_Pham_Minibar', 'soLuongXuat')) {
                $table->integer('soLuongXuat')->default(0)->after('soLuongNhap');
            }
            if (!Schema::hasColumn('San_Pham_Minibar', 'tonHienTai')) {
                $table->integer('tonHienTai')->default(50)->after('soLuongXuat');
            }
            if (!Schema::hasColumn('San_Pham_Minibar', 'dinhMucTieuChuan')) {
                $table->integer('dinhMucTieuChuan')->default(2)->after('tonHienTai');
            }
        });

        // 2. Cập nhật bảng Tai_San
        Schema::table('Tai_San', function (Blueprint $table) {
            if (!Schema::hasColumn('Tai_San', 'maTaiSan')) {
                $table->string('maTaiSan', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('Tai_San', 'soLuong')) {
                $table->integer('soLuong')->default(1)->after('tenTaiSan');
            }
            if (!Schema::hasColumn('Tai_San', 'ghiChu')) {
                $table->text('ghiChu')->nullable()->after('giaTri');
            }
        });

        // 3. Cập nhật bảng Ghi_Nhan_Tai_San
        Schema::table('Ghi_Nhan_Tai_San', function (Blueprint $table) {
            if (!Schema::hasColumn('Ghi_Nhan_Tai_San', 'maPhong')) {
                $table->unsignedBigInteger('maPhong')->nullable()->after('id');
            }
            if (!Schema::hasColumn('Ghi_Nhan_Tai_San', 'soLuong')) {
                $table->integer('soLuong')->default(1)->after('maTaiSan');
            }
            if (!Schema::hasColumn('Ghi_Nhan_Tai_San', 'maNhanVien')) {
                $table->unsignedBigInteger('maNhanVien')->nullable()->after('soLuong');
            }
            if (!Schema::hasColumn('Ghi_Nhan_Tai_San', 'trangThaiXuLy')) {
                $table->string('trangThaiXuLy', 50)->default('Cho_Xac_Minh')->after('ghiChu');
            }
        });

        // 4. Tạo bảng Minibar_Phong nếu chưa có
        if (!Schema::hasTable('Minibar_Phong')) {
            Schema::create('Minibar_Phong', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('maPhong');
                $table->unsignedBigInteger('maLuuTru')->nullable();
                $table->unsignedBigInteger('maSanPham');
                $table->integer('soLuongTieuChuan')->default(2);
                $table->integer('soLuongBanDau')->default(2);
                $table->integer('soLuongBoSung')->default(0);
                $table->integer('soLuongThucTe')->default(2);
                $table->integer('soLuongTieuThu')->default(0);
                $table->integer('soLuongChenhLech')->default(0);
                $table->string('lyDoChenhLech', 100)->nullable();
                $table->bigInteger('donGiaApDung')->default(0);
                $table->bigInteger('thanhTien')->default(0);
                $table->string('trangThai', 50)->default('Chua_Kiem_Ke'); // Chua_Kiem_Ke, Da_Chuan_Bi, Dang_Kiem_Ke, Da_Xac_Nhan, Co_Chenh_Lech
                $table->text('ghiChu')->nullable();
                $table->timestamps();
            });
        }

        // 5. Tạo bảng Phieu_Kiem_Ke nếu chưa có
        if (!Schema::hasTable('Phieu_Kiem_Ke')) {
            Schema::create('Phieu_Kiem_Ke', function (Blueprint $table) {
                $table->id();
                $table->string('maPhieu', 50)->unique();
                $table->unsignedBigInteger('maPhong')->nullable();
                $table->unsignedBigInteger('maLuuTru')->nullable();
                $table->unsignedBigInteger('maNhanVien')->nullable();
                $table->string('tenNhanVien', 100)->nullable();
                $table->string('loaiKiemKe', 50); // Nhap_Kho, Chuan_Bi_CheckIn, Bo_Sung_Minibar, Kiem_Ke_CheckOut, Kiem_Tra_Tai_San, Bao_Hong_Mat
                $table->dateTime('thoiGian');
                $table->string('trangThai', 50)->default('Da_Xac_Nhan'); // Da_Xac_Nhan, Co_Chenh_Lech, Cho_Xac_Nhan
                $table->bigInteger('tongTienPhatSinh')->default(0);
                $table->text('ghiChu')->nullable();
                $table->longText('chiTiet')->nullable(); // JSON chi tiết
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Phieu_Kiem_Ke');
        Schema::dropIfExists('Minibar_Phong');
    }
};
