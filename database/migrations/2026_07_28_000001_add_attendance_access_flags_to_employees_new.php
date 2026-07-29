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
        if (Schema::hasTable('employees_new')) {
            Schema::table('employees_new', function (Blueprint $table) {
                if (! Schema::hasColumn('employees_new', 'allow_mobile_attendance')) {
                    $table->boolean('allow_mobile_attendance')->default(true)->after('work_mode');
                }
                if (! Schema::hasColumn('employees_new', 'allow_web_attendance')) {
                    $table->boolean('allow_web_attendance')->default(false)->after('allow_mobile_attendance');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees_new')) {
            Schema::table('employees_new', function (Blueprint $table) {
                if (Schema::hasColumn('employees_new', 'allow_web_attendance')) {
                    $table->dropColumn('allow_web_attendance');
                }
                if (Schema::hasColumn('employees_new', 'allow_mobile_attendance')) {
                    $table->dropColumn('allow_mobile_attendance');
                }
            });
        }
    }
};
