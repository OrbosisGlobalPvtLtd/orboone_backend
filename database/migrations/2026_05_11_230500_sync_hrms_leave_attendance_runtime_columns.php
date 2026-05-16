<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureCoreTables();
        $this->ensureAttendanceColumns();
        $this->ensureLeaveRequestColumns();
        $this->ensureLeaveRequestDateColumns();
        $this->ensureLeaveAllocationColumns();
        $this->ensureLeaveBalanceLogColumns();
        $this->ensureCompOffColumns();
        $this->ensureAttendanceRegularizationColumns();
        $this->ensureHolidayWorkRequestColumns();
        $this->ensureMonthlyAttendanceSummaryColumns();
        $this->ensurePayrollAttendanceImpactColumns();
        $this->ensureAttendanceDailyStatusLogColumns();
        $this->ensureEmployeePolicyAssignmentColumns();
        $this->ensurePolicyChangeLogColumns();
        $this->ensureEmployeeRuntimeColumns();
        $this->ensureIndexes();
    }

    public function down(): void
    {
        // Intentionally no-op. This migration only adds missing runtime-safe columns/indexes.
    }

    private function ensureCoreTables(): void
    {
        if (! Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $this->attendanceColumns($table);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $this->leaveRequestColumns($table);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('leave_request_dates')) {
            Schema::create('leave_request_dates', function (Blueprint $table) {
                $table->id();
                $this->leaveRequestDateColumns($table);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('leave_allocations')) {
            Schema::create('leave_allocations', function (Blueprint $table) {
                $table->id();
                $this->leaveAllocationColumns($table);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('leave_balance_logs')) {
            Schema::create('leave_balance_logs', function (Blueprint $table) {
                $table->id();
                $this->leaveBalanceLogColumns($table);
                $table->timestamps();
            });
        }
    }

    private function ensureAttendanceColumns(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            $this->addUnsignedBigInteger($table, 'attendances', 'user_id');
            $this->addUnsignedBigInteger($table, 'attendances', 'employee_id');
            $this->addUnsignedBigInteger($table, 'attendances', 'attendance_time_id');
            $this->addUnsignedBigInteger($table, 'attendances', 'attendance_type_id');
            $this->addUnsignedBigInteger($table, 'attendances', 'leave_request_id');
            $this->addUnsignedBigInteger($table, 'attendances', 'comp_off_id');
            $this->addDate($table, 'attendances', 'attendance_date');
            $this->addTimestamp($table, 'attendances', 'punch_in_time');
            $this->addTimestamp($table, 'attendances', 'punch_out_time');
            $this->addTime($table, 'attendances', 'target_punch_out_time');
            $this->addString($table, 'attendances', 'attendance_status');
            $this->addString($table, 'attendances', 'attendance_source');
            $this->addString($table, 'attendances', 'work_mode');
            $this->addDecimal($table, 'attendances', 'punch_in_latitude', 10, 7);
            $this->addDecimal($table, 'attendances', 'punch_in_longitude', 10, 7);
            $this->addText($table, 'attendances', 'punch_in_address');
            $this->addDecimal($table, 'attendances', 'punch_out_latitude', 10, 7);
            $this->addDecimal($table, 'attendances', 'punch_out_longitude', 10, 7);
            $this->addText($table, 'attendances', 'punch_out_address');
            $this->addString($table, 'attendances', 'punch_in_ip');
            $this->addString($table, 'attendances', 'punch_out_ip');
            $this->addText($table, 'attendances', 'punch_in_device');
            $this->addText($table, 'attendances', 'punch_out_device');
            $this->addInteger($table, 'attendances', 'gross_work_minutes', 0);
            $this->addInteger($table, 'attendances', 'break_minutes', 0);
            $this->addInteger($table, 'attendances', 'lunch_break_minutes', 0);
            $this->addInteger($table, 'attendances', 'total_work_minutes', 0);
            $this->addBoolean($table, 'attendances', 'is_late', false);
            $this->addInteger($table, 'attendances', 'late_minutes', 0);
            $this->addBoolean($table, 'attendances', 'is_early_out', false);
            $this->addInteger($table, 'attendances', 'early_out_minutes', 0);
            $this->addInteger($table, 'attendances', 'violation_count', 0);
            $this->addBoolean($table, 'attendances', 'is_blocked', false);
            $this->addBoolean($table, 'attendances', 'is_punch_blocked', false);
            $this->addText($table, 'attendances', 'blocked_reason');
            $this->addText($table, 'attendances', 'block_reason');
            $this->addTimestamp($table, 'attendances', 'auto_blocked_at');
            $this->addText($table, 'attendances', 'auto_block_reason');
            $this->addBoolean($table, 'attendances', 'is_admin_unlocked', false);
            $this->addString($table, 'attendances', 'unlock_type');
            $this->addString($table, 'attendances', 'unlock_reason_category');
            $this->addText($table, 'attendances', 'unlock_remarks');
            $this->addTime($table, 'attendances', 'approved_punch_in_time');
            $this->addBoolean($table, 'attendances', 'is_late_exempted', false);
            $this->addUnsignedBigInteger($table, 'attendances', 'unlocked_by');
            $this->addTimestamp($table, 'attendances', 'unlocked_at');
            $this->addUnsignedBigInteger($table, 'attendances', 'hr_approved_by');
            $this->addTimestamp($table, 'attendances', 'hr_approved_at');
            $this->addText($table, 'attendances', 'hr_approval_note');
            $this->addText($table, 'attendances', 'old_pending_hr_logic');
            $this->addText($table, 'attendances', 'pending_hr_reason');
            $this->addText($table, 'attendances', 'remarks');
            $this->addBoolean($table, 'attendances', 'is_profile_completed_at_punch', false);
            $this->addBoolean($table, 'attendances', 'is_locked', false);
            $this->addBoolean($table, 'attendances', 'missed_punch', false);
            $this->addBoolean($table, 'attendances', 'is_missed_punch', false);
            $this->addText($table, 'attendances', 'missed_punch_reason');
            $this->addBoolean($table, 'attendances', 'is_lwp', false);
            $this->addText($table, 'attendances', 'lwp_reason');
            $this->addBoolean($table, 'attendances', 'is_half_day', false);
            $this->addText($table, 'attendances', 'half_day_reason');
            $this->addBoolean($table, 'attendances', 'payroll_processed', false);
            $this->addTimestamp($table, 'attendances', 'payroll_processed_at');
            $this->addText($table, 'attendances', 'punch_in_note');
            $this->addText($table, 'attendances', 'punch_out_note');
            $this->addTimestampsIfMissing($table, 'attendances');
        });
    }

    private function ensureLeaveRequestColumns(): void
    {
        if (! Schema::hasTable('leave_requests')) {
            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            $this->leaveRequestColumns($table);
            $this->addBoolean($table, 'leave_requests', 'payroll_processed', false);
            $this->addBoolean($table, 'leave_requests', 'attendance_synced', false);
            $this->addTimestampsIfMissing($table, 'leave_requests');
        });
    }

    private function ensureLeaveRequestDateColumns(): void
    {
        if (! Schema::hasTable('leave_request_dates')) {
            return;
        }

        Schema::table('leave_request_dates', function (Blueprint $table) {
            $this->leaveRequestDateColumns($table);
            $this->addTimestampsIfMissing($table, 'leave_request_dates');
        });
    }

    private function ensureLeaveAllocationColumns(): void
    {
        if (! Schema::hasTable('leave_allocations')) {
            return;
        }

        Schema::table('leave_allocations', function (Blueprint $table) {
            $this->leaveAllocationColumns($table);
            $this->addDate($table, 'leave_allocations', 'confirmation_date');
            $this->addBoolean($table, 'leave_allocations', 'is_locked', false);
            $this->addTimestampsIfMissing($table, 'leave_allocations');
        });
    }

    private function ensureLeaveBalanceLogColumns(): void
    {
        if (! Schema::hasTable('leave_balance_logs')) {
            return;
        }

        Schema::table('leave_balance_logs', function (Blueprint $table) {
            $this->leaveBalanceLogColumns($table);
            $this->addTimestampsIfMissing($table, 'leave_balance_logs');
        });
    }

    private function ensureCompOffColumns(): void
    {
        if (! Schema::hasTable('comp_offs')) {
            Schema::create('comp_offs', function (Blueprint $table) {
                $table->id();
                $this->compOffColumns($table);
                $table->timestamps();
            });
            return;
        }

        Schema::table('comp_offs', function (Blueprint $table) {
            $this->compOffColumns($table);
            $this->addTimestampsIfMissing($table, 'comp_offs');
        });
    }

    private function ensureAttendanceRegularizationColumns(): void
    {
        if (! Schema::hasTable('attendance_regularizations')) {
            Schema::create('attendance_regularizations', function (Blueprint $table) {
                $table->id();
                $this->attendanceRegularizationColumns($table);
                $table->timestamps();
                $table->softDeletes();
            });
            return;
        }

        Schema::table('attendance_regularizations', function (Blueprint $table) {
            $this->attendanceRegularizationColumns($table);
            $this->addTimestampsIfMissing($table, 'attendance_regularizations');
            if (! Schema::hasColumn('attendance_regularizations', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    private function ensureHolidayWorkRequestColumns(): void
    {
        if (! Schema::hasTable('holiday_work_requests')) {
            Schema::create('holiday_work_requests', function (Blueprint $table) {
                $table->id();
                $this->holidayWorkRequestColumns($table);
                $table->timestamps();
                $table->softDeletes();
            });
            return;
        }

        Schema::table('holiday_work_requests', function (Blueprint $table) {
            $this->holidayWorkRequestColumns($table);
            $this->addTimestampsIfMissing($table, 'holiday_work_requests');
            if (! Schema::hasColumn('holiday_work_requests', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    private function ensureMonthlyAttendanceSummaryColumns(): void
    {
        if (! Schema::hasTable('monthly_attendance_summaries')) {
            Schema::create('monthly_attendance_summaries', function (Blueprint $table) {
                $table->id();
                $this->monthlyAttendanceSummaryColumns($table);
                $table->timestamps();
            });
            return;
        }

        Schema::table('monthly_attendance_summaries', function (Blueprint $table) {
            $this->monthlyAttendanceSummaryColumns($table);
            $this->addTimestampsIfMissing($table, 'monthly_attendance_summaries');
        });
    }

    private function ensurePayrollAttendanceImpactColumns(): void
    {
        if (! Schema::hasTable('payroll_attendance_impacts')) {
            Schema::create('payroll_attendance_impacts', function (Blueprint $table) {
                $table->id();
                $this->payrollAttendanceImpactColumns($table);
                $table->timestamps();
            });
            return;
        }

        Schema::table('payroll_attendance_impacts', function (Blueprint $table) {
            $this->payrollAttendanceImpactColumns($table);
            $this->addTimestampsIfMissing($table, 'payroll_attendance_impacts');
        });
    }

    private function ensureAttendanceDailyStatusLogColumns(): void
    {
        if (! Schema::hasTable('attendance_daily_status_logs')) {
            Schema::create('attendance_daily_status_logs', function (Blueprint $table) {
                $table->id();
                $this->attendanceDailyStatusLogColumns($table);
                $table->timestamps();
            });
            return;
        }

        Schema::table('attendance_daily_status_logs', function (Blueprint $table) {
            $this->attendanceDailyStatusLogColumns($table);
            $this->addTimestampsIfMissing($table, 'attendance_daily_status_logs');
        });
    }

    private function ensureEmployeePolicyAssignmentColumns(): void
    {
        if (! Schema::hasTable('employee_policy_assignments')) {
            Schema::create('employee_policy_assignments', function (Blueprint $table) {
                $table->id();
                $this->employeePolicyAssignmentColumns($table);
                $table->timestamps();
            });
            return;
        }

        Schema::table('employee_policy_assignments', function (Blueprint $table) {
            $this->employeePolicyAssignmentColumns($table);
            $this->addTimestampsIfMissing($table, 'employee_policy_assignments');
        });
    }

    private function ensurePolicyChangeLogColumns(): void
    {
        if (! Schema::hasTable('policy_change_logs')) {
            Schema::create('policy_change_logs', function (Blueprint $table) {
                $table->id();
                $this->addString($table, 'policy_change_logs', 'policy_type', 80);
                $this->addUnsignedBigInteger($table, 'policy_change_logs', 'policy_id');
                $this->addUnsignedBigInteger($table, 'policy_change_logs', 'changed_by_user_id');
                $this->addJson($table, 'policy_change_logs', 'old_values');
                $this->addJson($table, 'policy_change_logs', 'new_values');
                $this->addText($table, 'policy_change_logs', 'remarks');
                $table->timestamps();
            });
            return;
        }

        Schema::table('policy_change_logs', function (Blueprint $table) {
            $this->addString($table, 'policy_change_logs', 'policy_type', 80);
            $this->addUnsignedBigInteger($table, 'policy_change_logs', 'policy_id');
            $this->addUnsignedBigInteger($table, 'policy_change_logs', 'changed_by_user_id');
            $this->addJson($table, 'policy_change_logs', 'old_values');
            $this->addJson($table, 'policy_change_logs', 'new_values');
            $this->addText($table, 'policy_change_logs', 'remarks');
            $this->addTimestampsIfMissing($table, 'policy_change_logs');
        });
    }

    private function ensureEmployeeRuntimeColumns(): void
    {
        if (! Schema::hasTable('employees_new')) {
            return;
        }

        Schema::table('employees_new', function (Blueprint $table) {
            $this->addString($table, 'employees_new', 'employee_stage');
            $this->addDate($table, 'employees_new', 'confirmation_date');
            $this->addDate($table, 'employees_new', 'probation_start_date');
            $this->addDate($table, 'employees_new', 'probation_end_date');
            $this->addDate($table, 'employees_new', 'internship_start_date');
            $this->addDate($table, 'employees_new', 'internship_end_date');
            $this->addUnsignedBigInteger($table, 'employees_new', 'leave_policy_id');
            $this->addUnsignedBigInteger($table, 'employees_new', 'attendance_policy_rule_id');
        });
    }

    private function attendanceColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'attendances', 'user_id');
        $this->addUnsignedBigInteger($table, 'attendances', 'employee_id');
        $this->addUnsignedBigInteger($table, 'attendances', 'attendance_time_id');
        $this->addUnsignedBigInteger($table, 'attendances', 'attendance_type_id');
        $this->addUnsignedBigInteger($table, 'attendances', 'leave_request_id');
        $this->addUnsignedBigInteger($table, 'attendances', 'comp_off_id');
        $this->addDate($table, 'attendances', 'attendance_date');
        $this->addTimestamp($table, 'attendances', 'punch_in_time');
        $this->addTimestamp($table, 'attendances', 'punch_out_time');
        $this->addString($table, 'attendances', 'attendance_status');
        $this->addString($table, 'attendances', 'attendance_source');
        $this->addString($table, 'attendances', 'work_mode');
        $this->addDecimal($table, 'attendances', 'punch_in_latitude', 10, 7);
        $this->addDecimal($table, 'attendances', 'punch_in_longitude', 10, 7);
        $this->addText($table, 'attendances', 'punch_in_address');
        $this->addDecimal($table, 'attendances', 'punch_out_latitude', 10, 7);
        $this->addDecimal($table, 'attendances', 'punch_out_longitude', 10, 7);
        $this->addText($table, 'attendances', 'punch_out_address');
        $this->addString($table, 'attendances', 'punch_in_ip');
        $this->addString($table, 'attendances', 'punch_out_ip');
        $this->addText($table, 'attendances', 'punch_in_device');
        $this->addText($table, 'attendances', 'punch_out_device');
        $this->addInteger($table, 'attendances', 'gross_work_minutes', 0);
        $this->addInteger($table, 'attendances', 'lunch_break_minutes', 0);
        $this->addInteger($table, 'attendances', 'total_work_minutes', 0);
        $this->addBoolean($table, 'attendances', 'is_late', false);
        $this->addInteger($table, 'attendances', 'late_minutes', 0);
        $this->addBoolean($table, 'attendances', 'is_early_out', false);
        $this->addInteger($table, 'attendances', 'early_out_minutes', 0);
        $this->addBoolean($table, 'attendances', 'is_blocked', false);
        $this->addText($table, 'attendances', 'block_reason');
        $this->addTimestamp($table, 'attendances', 'auto_blocked_at');
        $this->addText($table, 'attendances', 'auto_block_reason');
        $this->addUnsignedBigInteger($table, 'attendances', 'hr_approved_by');
        $this->addTimestamp($table, 'attendances', 'hr_approved_at');
        $this->addText($table, 'attendances', 'hr_approval_note');
        $this->addBoolean($table, 'attendances', 'is_profile_completed_at_punch', false);
        $this->addBoolean($table, 'attendances', 'is_locked', false);
        $this->addBoolean($table, 'attendances', 'is_missed_punch', false);
        $this->addText($table, 'attendances', 'missed_punch_reason');
        $this->addBoolean($table, 'attendances', 'is_lwp', false);
        $this->addText($table, 'attendances', 'lwp_reason');
        $this->addBoolean($table, 'attendances', 'is_half_day', false);
        $this->addText($table, 'attendances', 'half_day_reason');
        $this->addBoolean($table, 'attendances', 'payroll_processed', false);
        $this->addTimestamp($table, 'attendances', 'payroll_processed_at');
        $this->addText($table, 'attendances', 'punch_in_note');
        $this->addText($table, 'attendances', 'punch_out_note');
    }

    private function leaveRequestColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'leave_requests', 'employee_id');
        $this->addUnsignedBigInteger($table, 'leave_requests', 'user_id');
        $this->addUnsignedBigInteger($table, 'leave_requests', 'leave_type_id');
        $this->addUnsignedBigInteger($table, 'leave_requests', 'reporting_manager_employee_id');
        $this->addDate($table, 'leave_requests', 'start_date');
        $this->addDate($table, 'leave_requests', 'end_date');
        $this->addDecimal($table, 'leave_requests', 'requested_days', 8, 2, 0);
        $this->addDecimal($table, 'leave_requests', 'deducted_days', 8, 2, 0);
        $this->addBoolean($table, 'leave_requests', 'is_half_day', false);
        $this->addString($table, 'leave_requests', 'half_day_type');
        $this->addText($table, 'leave_requests', 'reason');
        $this->addString($table, 'leave_requests', 'attachment_path');
        $this->addString($table, 'leave_requests', 'status', 80, 'pending');
        $this->addString($table, 'leave_requests', 'approval_level');
        $this->addUnsignedBigInteger($table, 'leave_requests', 'approved_by_user_id');
        $this->addTimestamp($table, 'leave_requests', 'approved_at');
        $this->addUnsignedBigInteger($table, 'leave_requests', 'manager_approved_by');
        $this->addTimestamp($table, 'leave_requests', 'manager_approved_at');
        $this->addUnsignedBigInteger($table, 'leave_requests', 'hr_approved_by');
        $this->addTimestamp($table, 'leave_requests', 'hr_approved_at');
        $this->addText($table, 'leave_requests', 'rejection_reason');
        $this->addText($table, 'leave_requests', 'cancel_reason');
        $this->addUnsignedBigInteger($table, 'leave_requests', 'cancelled_by_user_id');
        $this->addTimestamp($table, 'leave_requests', 'cancelled_at');
        $this->addBoolean($table, 'leave_requests', 'sandwich_applied', false);
        $this->addDecimal($table, 'leave_requests', 'paid_days', 8, 2, 0);
        $this->addDecimal($table, 'leave_requests', 'sick_days', 8, 2, 0);
        $this->addDecimal($table, 'leave_requests', 'comp_off_days', 8, 2, 0);
        $this->addDecimal($table, 'leave_requests', 'lwp_days', 8, 2, 0);
        $this->addBoolean($table, 'leave_requests', 'auto_converted_to_lwp', false);
        $this->addString($table, 'leave_requests', 'applied_from', 80, 'web');
        $this->addBoolean($table, 'leave_requests', 'emergency_leave', false);
        $this->addText($table, 'leave_requests', 'manager_note');
        $this->addText($table, 'leave_requests', 'hr_note');
    }

    private function leaveRequestDateColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'leave_request_dates', 'leave_request_id');
        $this->addUnsignedBigInteger($table, 'leave_request_dates', 'employee_id');
        $this->addDate($table, 'leave_request_dates', 'leave_date');
        $this->addString($table, 'leave_request_dates', 'day_name');
        $this->addBoolean($table, 'leave_request_dates', 'is_working_day', true);
        $this->addBoolean($table, 'leave_request_dates', 'is_weekoff', false);
        $this->addBoolean($table, 'leave_request_dates', 'is_holiday', false);
        $this->addBoolean($table, 'leave_request_dates', 'is_sandwich_day', false);
        $this->addBoolean($table, 'leave_request_dates', 'deduct_as_leave', true);
        $this->addString($table, 'leave_request_dates', 'leave_type_code');
        $this->addDecimal($table, 'leave_request_dates', 'paid_day', 4, 2, 0);
        $this->addDecimal($table, 'leave_request_dates', 'sick_day', 4, 2, 0);
        $this->addDecimal($table, 'leave_request_dates', 'comp_off_day', 4, 2, 0);
        $this->addDecimal($table, 'leave_request_dates', 'lwp_day', 4, 2, 0);
        $this->addText($table, 'leave_request_dates', 'remarks');
    }

    private function leaveAllocationColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'leave_allocations', 'employee_id');
        $this->addUnsignedInteger($table, 'leave_allocations', 'year');
        $this->addUnsignedBigInteger($table, 'leave_allocations', 'policy_id');
        $this->addString($table, 'leave_allocations', 'employment_stage');
        $this->addDate($table, 'leave_allocations', 'allocation_from_date');
        $this->addDate($table, 'leave_allocations', 'allocation_to_date');
        $this->addDecimal($table, 'leave_allocations', 'total_allocated', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'paid_allocated', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'sick_allocated', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'comp_off_allocated', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'total_used', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'paid_used', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'sick_used', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'comp_off_used', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'lwp_used', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'total_remaining', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'paid_remaining', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'sick_remaining', 8, 2, 0);
        $this->addDecimal($table, 'leave_allocations', 'comp_off_remaining', 8, 2, 0);
        $this->addString($table, 'leave_allocations', 'allocation_reason');
        $this->addUnsignedBigInteger($table, 'leave_allocations', 'created_by_user_id');
    }

    private function leaveBalanceLogColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'leave_balance_logs', 'employee_id');
        $this->addUnsignedBigInteger($table, 'leave_balance_logs', 'leave_allocation_id');
        $this->addUnsignedBigInteger($table, 'leave_balance_logs', 'leave_request_id');
        $this->addUnsignedBigInteger($table, 'leave_balance_logs', 'leave_type_id');
        $this->addString($table, 'leave_balance_logs', 'action');
        $this->addDecimal($table, 'leave_balance_logs', 'credit', 8, 2, 0);
        $this->addDecimal($table, 'leave_balance_logs', 'debit', 8, 2, 0);
        $this->addDecimal($table, 'leave_balance_logs', 'balance_before', 8, 2, 0);
        $this->addDecimal($table, 'leave_balance_logs', 'balance_after', 8, 2, 0);
        $this->addText($table, 'leave_balance_logs', 'remarks');
        $this->addUnsignedBigInteger($table, 'leave_balance_logs', 'created_by_user_id');
    }

    private function compOffColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'comp_offs', 'employee_id');
        $this->addDate($table, 'comp_offs', 'worked_date');
        $this->addDecimal($table, 'comp_offs', 'earned_days', 4, 2, 1);
        $this->addDate($table, 'comp_offs', 'expiry_date');
        $this->addString($table, 'comp_offs', 'status', 80, 'earned');
        $this->addUnsignedBigInteger($table, 'comp_offs', 'used_against_leave_request_id');
        $this->addUnsignedBigInteger($table, 'comp_offs', 'approved_by_user_id');
        $this->addTimestamp($table, 'comp_offs', 'approved_at');
        $this->addText($table, 'comp_offs', 'remarks');
    }

    private function attendanceRegularizationColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'attendance_regularizations', 'employee_id');
        $this->addUnsignedBigInteger($table, 'attendance_regularizations', 'attendance_id');
        $this->addString($table, 'attendance_regularizations', 'request_type', 80, 'manual_correction');
        $this->addTimestamp($table, 'attendance_regularizations', 'existing_punch_in');
        $this->addTimestamp($table, 'attendance_regularizations', 'existing_punch_out');
        $this->addTimestamp($table, 'attendance_regularizations', 'requested_punch_in');
        $this->addTimestamp($table, 'attendance_regularizations', 'requested_punch_out');
        $this->addText($table, 'attendance_regularizations', 'reason');
        $this->addString($table, 'attendance_regularizations', 'attachment_path');
        $this->addString($table, 'attendance_regularizations', 'status', 80, 'pending');
        $this->addUnsignedBigInteger($table, 'attendance_regularizations', 'approved_by_user_id');
        $this->addTimestamp($table, 'attendance_regularizations', 'approved_at');
        $this->addText($table, 'attendance_regularizations', 'rejection_reason');
    }

    private function holidayWorkRequestColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'holiday_work_requests', 'employee_id');
        $this->addUnsignedBigInteger($table, 'holiday_work_requests', 'attendance_id');
        $this->addDate($table, 'holiday_work_requests', 'worked_date');
        $this->addString($table, 'holiday_work_requests', 'work_type', 80, 'holiday_work');
        $this->addBoolean($table, 'holiday_work_requests', 'comp_off_generated', false);
        $this->addUnsignedBigInteger($table, 'holiday_work_requests', 'comp_off_id');
        $this->addText($table, 'holiday_work_requests', 'reason');
        $this->addString($table, 'holiday_work_requests', 'status', 80, 'pending');
        $this->addUnsignedBigInteger($table, 'holiday_work_requests', 'approved_by_user_id');
        $this->addTimestamp($table, 'holiday_work_requests', 'approved_at');
        $this->addText($table, 'holiday_work_requests', 'rejection_reason');
    }

    private function monthlyAttendanceSummaryColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'monthly_attendance_summaries', 'employee_id');
        $this->addUnsignedTinyInteger($table, 'monthly_attendance_summaries', 'month');
        $this->addUnsignedInteger($table, 'monthly_attendance_summaries', 'year');
        $this->addDecimal($table, 'monthly_attendance_summaries', 'present_days', 8, 2, 0);
        $this->addDecimal($table, 'monthly_attendance_summaries', 'paid_leave_days', 8, 2, 0);
        $this->addDecimal($table, 'monthly_attendance_summaries', 'sick_leave_days', 8, 2, 0);
        $this->addDecimal($table, 'monthly_attendance_summaries', 'comp_off_days', 8, 2, 0);
        $this->addDecimal($table, 'monthly_attendance_summaries', 'holiday_days', 8, 2, 0);
        $this->addDecimal($table, 'monthly_attendance_summaries', 'week_off_days', 8, 2, 0);
        $this->addDecimal($table, 'monthly_attendance_summaries', 'half_days', 8, 2, 0);
        $this->addDecimal($table, 'monthly_attendance_summaries', 'lwp_days', 8, 2, 0);
        $this->addDecimal($table, 'monthly_attendance_summaries', 'absent_days', 8, 2, 0);
        $this->addUnsignedInteger($table, 'monthly_attendance_summaries', 'late_count', 0);
        $this->addUnsignedInteger($table, 'monthly_attendance_summaries', 'early_out_count', 0);
        $this->addUnsignedInteger($table, 'monthly_attendance_summaries', 'missed_punch_count', 0);
        $this->addInteger($table, 'monthly_attendance_summaries', 'total_work_minutes', 0);
        $this->addDecimal($table, 'monthly_attendance_summaries', 'payable_days', 8, 2, 0);
        $this->addBoolean($table, 'monthly_attendance_summaries', 'is_locked', false);
        $this->addUnsignedBigInteger($table, 'monthly_attendance_summaries', 'locked_by_user_id');
        $this->addTimestamp($table, 'monthly_attendance_summaries', 'locked_at');
    }

    private function payrollAttendanceImpactColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'payroll_attendance_impacts', 'employee_id');
        $this->addUnsignedBigInteger($table, 'payroll_attendance_impacts', 'attendance_id');
        $this->addUnsignedBigInteger($table, 'payroll_attendance_impacts', 'leave_request_id');
        $this->addUnsignedBigInteger($table, 'payroll_attendance_impacts', 'payroll_id');
        $this->addUnsignedTinyInteger($table, 'payroll_attendance_impacts', 'month');
        $this->addUnsignedInteger($table, 'payroll_attendance_impacts', 'year');
        $this->addString($table, 'payroll_attendance_impacts', 'impact_type', 80);
        $this->addDecimal($table, 'payroll_attendance_impacts', 'impact_days', 8, 2, 0);
        $this->addDecimal($table, 'payroll_attendance_impacts', 'impact_amount', 12, 2, 0);
        $this->addText($table, 'payroll_attendance_impacts', 'remarks');
        $this->addBoolean($table, 'payroll_attendance_impacts', 'is_processed_in_payroll', false);
        $this->addTimestamp($table, 'payroll_attendance_impacts', 'processed_at');
    }

    private function attendanceDailyStatusLogColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'attendance_daily_status_logs', 'employee_id');
        $this->addUnsignedBigInteger($table, 'attendance_daily_status_logs', 'attendance_id');
        $this->addDate($table, 'attendance_daily_status_logs', 'status_date');
        $this->addString($table, 'attendance_daily_status_logs', 'old_status', 80);
        $this->addString($table, 'attendance_daily_status_logs', 'new_status', 80);
        $this->addString($table, 'attendance_daily_status_logs', 'source', 80, 'system');
        $this->addText($table, 'attendance_daily_status_logs', 'remarks');
        $this->addUnsignedBigInteger($table, 'attendance_daily_status_logs', 'created_by_user_id');
    }

    private function employeePolicyAssignmentColumns(Blueprint $table): void
    {
        $this->addUnsignedBigInteger($table, 'employee_policy_assignments', 'employee_id');
        $this->addString($table, 'employee_policy_assignments', 'policy_type', 80);
        $this->addUnsignedBigInteger($table, 'employee_policy_assignments', 'policy_id');
        $this->addDate($table, 'employee_policy_assignments', 'effective_from');
        $this->addDate($table, 'employee_policy_assignments', 'effective_to');
        $this->addUnsignedBigInteger($table, 'employee_policy_assignments', 'assigned_by_user_id');
        $this->addText($table, 'employee_policy_assignments', 'remarks');
        $this->addBoolean($table, 'employee_policy_assignments', 'is_active', true);
    }

    private function ensureIndexes(): void
    {
        $this->addIndex('attendances', ['employee_id', 'attendance_date'], 'hrms_att_emp_date_idx');
        $this->addIndex('attendances', ['attendance_type_id'], 'hrms_att_type_idx');
        $this->addIndex('attendances', ['attendance_status'], 'hrms_att_status_idx');
        $this->addIndex('attendances', ['leave_request_id'], 'hrms_att_leave_req_idx');
        $this->addIndex('attendances', ['is_lwp', 'is_half_day'], 'hrms_att_lwp_half_idx');
        $this->addIndex('attendances', ['is_blocked'], 'hrms_att_blocked_idx');
        $this->addIndex('attendances', ['payroll_processed'], 'hrms_att_payroll_processed_idx');

        $this->addIndex('leave_requests', ['employee_id', 'status'], 'hrms_lr_employee_status_idx');
        $this->addIndex('leave_requests', ['start_date', 'end_date'], 'hrms_lr_date_range_idx');
        $this->addIndex('leave_requests', ['leave_type_id'], 'hrms_lr_leave_type_idx');
        $this->addIndex('leave_requests', ['reporting_manager_employee_id'], 'hrms_lr_reporting_mgr_idx');
        $this->addIndex('leave_requests', ['hr_approved_by'], 'hrms_lr_hr_approved_by_idx');

        $this->addIndex('leave_request_dates', ['leave_request_id', 'leave_date'], 'hrms_lrd_request_date_idx');
        $this->addIndex('leave_request_dates', ['employee_id', 'leave_date'], 'hrms_lrd_employee_date_idx');
        $this->addIndex('leave_request_dates', ['deduct_as_leave'], 'hrms_lrd_deduct_idx');

        $this->addIndex('leave_allocations', ['employee_id', 'year'], 'hrms_la_employee_year_idx');
        $this->addIndex('leave_balance_logs', ['employee_id', 'action'], 'hrms_lbl_employee_action_idx');
        $this->addIndex('comp_offs', ['employee_id', 'status'], 'hrms_co_employee_status_idx');
        $this->addIndex('payroll_attendance_impacts', ['employee_id', 'month', 'year'], 'hrms_pai_employee_period_idx');
        $this->addIndex('attendance_daily_status_logs', ['employee_id', 'status_date'], 'hrms_adsl_employee_date_idx');
        $this->addIndex('employee_policy_assignments', ['employee_id', 'policy_type', 'is_active'], 'hrms_epa_employee_policy_idx');
    }

    private function addUnsignedBigInteger(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->unsignedBigInteger($column)->nullable();
        }
    }

    private function addUnsignedInteger(Blueprint $table, string $tableName, string $column, ?int $default = null): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $definition = $table->unsignedInteger($column);
            $default === null ? $definition->nullable() : $definition->default($default);
        }
    }

    private function addUnsignedTinyInteger(Blueprint $table, string $tableName, string $column, ?int $default = null): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $definition = $table->unsignedTinyInteger($column);
            $default === null ? $definition->nullable() : $definition->default($default);
        }
    }

    private function addInteger(Blueprint $table, string $tableName, string $column, int $default): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->integer($column)->default($default);
        }
    }

    private function addString(Blueprint $table, string $tableName, string $column, int $length = 255, ?string $default = null): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $definition = $table->string($column, $length);
            $default === null ? $definition->nullable() : $definition->default($default);
        }
    }

    private function addText(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->text($column)->nullable();
        }
    }

    private function addJson(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->json($column)->nullable();
        }
    }

    private function addDate(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->date($column)->nullable();
        }
    }

    private function addTime(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->time($column)->nullable();
        }
    }

    private function addTimestamp(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->timestamp($column)->nullable();
        }
    }

    private function addDecimal(Blueprint $table, string $tableName, string $column, int $total, int $places, ?float $default = null): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $definition = $table->decimal($column, $total, $places);
            $default === null ? $definition->nullable() : $definition->default($default);
        }
    }

    private function addBoolean(Blueprint $table, string $tableName, string $column, bool $default): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->boolean($column)->default($default);
        }
    }

    private function addTimestampsIfMissing(Blueprint $table, string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'created_at')) {
            $table->timestamp('created_at')->nullable();
        }

        if (! Schema::hasColumn($tableName, 'updated_at')) {
            $table->timestamp('updated_at')->nullable();
        }
    }

    private function addIndex(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || $this->indexExists($tableName, $indexName)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return;
            }
        }

        try {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        } catch (Throwable $e) {
            // Existing databases may already have equivalent indexes under legacy names.
        }
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        try {
            $database = DB::getDatabaseName();
            $prefix = DB::getTablePrefix();
            $rows = DB::select(
                'select 1 from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ? limit 1',
                [$database, $prefix . $tableName, $indexName]
            );

            return count($rows) > 0;
        } catch (Throwable $e) {
            return false;
        }
    }
};
