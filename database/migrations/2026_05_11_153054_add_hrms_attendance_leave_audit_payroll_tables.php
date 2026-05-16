<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public function up(): void
    {
        if (!$this->hasTable('payroll_attendance_impacts')) {
            Schema::create('payroll_attendance_impacts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->unsignedBigInteger('attendance_id')->nullable();
                $table->unsignedBigInteger('leave_request_id')->nullable();
                $table->unsignedBigInteger('payroll_id')->nullable();
                $table->unsignedTinyInteger('month');
                $table->unsignedInteger('year');
                $table->string('impact_type', 80);
                $table->decimal('impact_days', 8, 2)->default(0);
                $table->decimal('impact_amount', 12, 2)->default(0);
                $table->text('remarks')->nullable();
                $table->boolean('is_processed_in_payroll')->default(false);
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'month', 'year'], 'pai_employee_month_year_idx');
                $table->index('attendance_id', 'pai_attendance_idx');
                $table->index('leave_request_id', 'pai_leave_request_idx');
                $table->index('payroll_id', 'pai_payroll_idx');
                $table->index('impact_type', 'pai_impact_type_idx');
                $table->index('is_processed_in_payroll', 'pai_processed_idx');
            });
        }

        if (!$this->hasTable('attendance_daily_status_logs')) {
            Schema::create('attendance_daily_status_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->unsignedBigInteger('attendance_id')->nullable();
                $table->date('status_date');
                $table->string('old_status', 80)->nullable();
                $table->string('new_status', 80)->nullable();
                $table->string('source', 80)->default('system');
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'status_date'], 'adsl_employee_date_idx');
                $table->index('attendance_id', 'adsl_attendance_idx');
                $table->index('new_status', 'adsl_new_status_idx');
                $table->index('source', 'adsl_source_idx');
                $table->index('created_by_user_id', 'adsl_created_by_idx');
            });
        }

        if (!$this->hasTable('policy_change_logs')) {
            Schema::create('policy_change_logs', function (Blueprint $table) {
                $table->id();
                $table->string('policy_type', 80);
                $table->unsignedBigInteger('policy_id')->nullable();
                $table->unsignedBigInteger('changed_by_user_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['policy_type', 'policy_id'], 'pcl_policy_idx');
                $table->index('changed_by_user_id', 'pcl_changed_by_idx');
                $table->index('created_at', 'pcl_created_at_idx');
            });
        }

        if (!$this->hasTable('employee_policy_assignments')) {
            Schema::create('employee_policy_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->string('policy_type', 80);
                $table->unsignedBigInteger('policy_id');
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->unsignedBigInteger('assigned_by_user_id')->nullable();
                $table->text('remarks')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['employee_id', 'policy_type', 'is_active'], 'epa_employee_policy_active_idx');
                $table->index(['policy_type', 'policy_id'], 'epa_policy_idx');
                $table->index(['effective_from', 'effective_to'], 'epa_effective_idx');
                $table->index('assigned_by_user_id', 'epa_assigned_by_idx');
                $table->unique(
                    ['employee_id', 'policy_type', 'policy_id', 'effective_from'],
                    'epa_unique_policy_assignment'
                );
            });
        }

        if ($this->hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('leave_requests', 'applied_from')) {
                    $table->string('applied_from', 80)->nullable()->after('status');
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

        if ($this->hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'payroll_processed')) {
                    if (Schema::hasColumn('attendances', 'attendance_source')) {
                        $table->boolean('payroll_processed')->default(false)->after('attendance_source');
                    } else {
                        $table->boolean('payroll_processed')->default(false);
                    }
                }

                if (!Schema::hasColumn('attendances', 'payroll_processed_at')) {
                    $table->timestamp('payroll_processed_at')->nullable()->after('payroll_processed');
                }
            });
        }
    }

    public function down(): void
    {
        if ($this->hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                foreach (['payroll_processed_at', 'payroll_processed'] as $column) {
                    if (Schema::hasColumn('attendances', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if ($this->hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                foreach (['hr_note', 'manager_note', 'emergency_leave', 'applied_from'] as $column) {
                    if (Schema::hasColumn('leave_requests', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('employee_policy_assignments');
        Schema::dropIfExists('policy_change_logs');
        Schema::dropIfExists('attendance_daily_status_logs');
        Schema::dropIfExists('payroll_attendance_impacts');
    }
};
