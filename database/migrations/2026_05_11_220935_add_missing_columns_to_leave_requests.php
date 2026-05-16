<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('leave_requests')) {
            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'reporting_manager_employee_id')) {
                $table->unsignedBigInteger('reporting_manager_employee_id')->nullable()->after('leave_type_id');
            }

            if (!Schema::hasColumn('leave_requests', 'applied_from')) {
                $table->string('applied_from', 50)->default('web')->after('sandwich_applied');
            }

            if (!Schema::hasColumn('leave_requests', 'emergency_leave')) {
                $table->boolean('emergency_leave')->default(false)->after('applied_from');
            }

            if (!Schema::hasColumn('leave_requests', 'manager_note')) {
                $table->text('manager_note')->nullable()->after('rejection_reason');
            }

            if (!Schema::hasColumn('leave_requests', 'hr_note')) {
                $table->text('hr_note')->nullable()->after('manager_note');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('leave_requests')) {
            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            foreach (
                [
                    'hr_note',
                    'manager_note',
                    'emergency_leave',
                    'applied_from',
                    'reporting_manager_employee_id',
                ] as $column
            ) {
                if (Schema::hasColumn('leave_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
