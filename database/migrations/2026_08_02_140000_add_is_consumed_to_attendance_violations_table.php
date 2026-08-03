<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_violations')) {
            Schema::table('attendance_violations', function (Blueprint $table) {
                if (! Schema::hasColumn('attendance_violations', 'is_consumed')) {
                    $table->boolean('is_consumed')->default(false)->after('remarks');
                }
                if (! Schema::hasColumn('attendance_violations', 'consumed_at')) {
                    $table->timestamp('consumed_at')->nullable()->after('is_consumed');
                }
                if (! Schema::hasColumn('attendance_violations', 'penalty_attendance_id')) {
                    $table->unsignedBigInteger('penalty_attendance_id')->nullable()->after('consumed_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_violations')) {
            Schema::table('attendance_violations', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_violations', 'penalty_attendance_id')) {
                    $table->dropColumn('penalty_attendance_id');
                }
                if (Schema::hasColumn('attendance_violations', 'consumed_at')) {
                    $table->dropColumn('consumed_at');
                }
                if (Schema::hasColumn('attendance_violations', 'is_consumed')) {
                    $table->dropColumn('is_consumed');
                }
            });
        }
    }
};
