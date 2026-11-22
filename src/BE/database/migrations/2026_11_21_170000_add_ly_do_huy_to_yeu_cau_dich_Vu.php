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
        Schema::table('Yeu_Cau_Dich_Vu', function (Blueprint $table) {
            if (!Schema::hasColumn('Yeu_Cau_Dich_Vu', 'lyDoHuy')) {
                $table->text('lyDoHuy')->nullable()->after('lyDoTuChoi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Yeu_Cau_Dich_Vu', function (Blueprint $table) {
            if (Schema::hasColumn('Yeu_Cau_Dich_Vu', 'lyDoHuy')) {
                $table->dropColumn('lyDoHuy');
            }
        });
    }
};
