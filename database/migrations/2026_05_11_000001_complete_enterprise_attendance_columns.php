<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_times', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_times', 'early_login_from')) {
                $table->time('early_login_from')->nullable()->after('punch_allowed_from');
            }
            if (! Schema::hasColumn('attendance_times', 'normal_login_from')) {
                $table->time('normal_login_from')->nullable()->after('early_login_from');
            }
            if (! Schema::hasColumn('attendance_times', 'warning_after_time')) {
                $table->time('warning_after_time')->nullable()->after('late_after_time');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'target_punch_out_time')) {
                $table->time('target_punch_out_time')->nullable()->after('punch_out_time');
            }
            if (! Schema::hasColumn('attendances', 'unlock_type')) {
                $table->string('unlock_type')->nullable()->after('is_admin_unlocked');
            }
            if (! Schema::hasColumn('attendances', 'unlock_reason_category')) {
                $table->string('unlock_reason_category')->nullable()->after('unlock_type');
            }
            if (! Schema::hasColumn('attendances', 'unlock_remarks')) {
                $table->text('unlock_remarks')->nullable()->after('unlock_reason_category');
            }
            if (! Schema::hasColumn('attendances', 'approved_punch_in_time')) {
                $table->time('approved_punch_in_time')->nullable()->after('unlock_remarks');
            }
            if (! Schema::hasColumn('attendances', 'is_late_exempted')) {
                $table->boolean('is_late_exempted')->default(false)->after('approved_punch_in_time');
            }
        });

        Schema::table('attendance_work_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_work_logs', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('work_summary');
            }
            if (! Schema::hasColumn('attendance_work_logs', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (! Schema::hasColumn('attendance_work_logs', 'device_info')) {
                $table->string('device_info')->nullable()->after('longitude');
            }
            if (! Schema::hasColumn('attendance_work_logs', 'ip_address')) {
                $table->string('ip_address', 100)->nullable()->after('device_info');
            }
            if (! Schema::hasColumn('attendance_work_logs', 'remarks')) {
                $table->text('remarks')->nullable()->after('ip_address');
            }
        });
    }

    public function down(): void
    {
    }
};
