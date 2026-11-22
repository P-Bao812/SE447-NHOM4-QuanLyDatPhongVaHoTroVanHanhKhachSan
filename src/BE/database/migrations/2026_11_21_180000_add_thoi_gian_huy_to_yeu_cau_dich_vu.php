<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('Yeu_Cau_Dich_Vu')) {
            Schema::table('Yeu_Cau_Dich_Vu', function (Blueprint $table) {
                if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'thoiGianHuy')) {
                    $table->dateTime('thoiGianHuy')->nullable()->after('lyDoHuy');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('Yeu_Cau_Dich_Vu')) {
            Schema::table('Yeu_Cau_Dich_Vu', function (Blueprint $table) {
                if (Schema::hasColumn('Yeu_Cau_Dich_Vu', 'thoiGianHuy')) {
                    $table->dropColumn('thoiGianHuy');
                }
            });
        }
    }
};
