<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPerformanceIndexesToHrmsTables extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        try {
            $conn = DB::connection();
            $db = $conn->getDatabaseName();
            $result = DB::select("
                SELECT 1 
                FROM information_schema.statistics 
                WHERE table_schema = ? 
                AND table_name = ? 
                AND index_name = ?
                LIMIT 1
            ", [$db, $table, $indexName]);
            
            return count($result) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function up()
    {
        // 1. attendances indexes
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'employee_id') && !$this->hasIndex('attendances', 'attendances_employee_id_index')) {
                    $table->index('employee_id');
                }
                if (Schema::hasColumn('attendances', 'attendance_date') && !$this->hasIndex('attendances', 'attendances_attendance_date_index')) {
                    $table->index('attendance_date');
                }
            });
        }

        // 2. generated_documents indexes
        if (Schema::hasTable('generated_documents')) {
            Schema::table('generated_documents', function (Blueprint $table) {
                if (Schema::hasColumn('generated_documents', 'employee_id') && !$this->hasIndex('generated_documents', 'generated_documents_employee_id_index')) {
                    $table->index('employee_id');
                }
                if (Schema::hasColumn('generated_documents', 'user_id') && !$this->hasIndex('generated_documents', 'generated_documents_user_id_index')) {
                    $table->index('user_id');
                }
            });
        }

        // 3. employee_documents_new indexes
        if (Schema::hasTable('employee_documents_new')) {
            Schema::table('employee_documents_new', function (Blueprint $table) {
                if (Schema::hasColumn('employee_documents_new', 'employee_id') && !$this->hasIndex('employee_documents_new', 'employee_documents_new_employee_id_index')) {
                    $table->index('employee_id');
                }
            });
        }

        // 4. leave_requests indexes
        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('leave_requests', 'employee_id') && !$this->hasIndex('leave_requests', 'leave_requests_employee_id_index')) {
                    $table->index('employee_id');
                }
            });
        }

        // 5. enterprise_payrolls indexes
        if (Schema::hasTable('enterprise_payrolls')) {
            Schema::table('enterprise_payrolls', function (Blueprint $table) {
                if (Schema::hasColumn('enterprise_payrolls', 'employee_id') && !$this->hasIndex('enterprise_payrolls', 'enterprise_payrolls_employee_id_index')) {
                    $table->index('employee_id');
                }
                if (Schema::hasColumn('enterprise_payrolls', 'payroll_run_id') && !$this->hasIndex('enterprise_payrolls', 'enterprise_payrolls_payroll_run_id_index')) {
                    $table->index('payroll_run_id');
                }
            });
        }

        // 6. notifications indexes
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (Schema::hasColumn('notifications', 'user_id') && !$this->hasIndex('notifications', 'notifications_user_id_index')) {
                    $table->index('user_id');
                }
            });
        }

        // 7. wfh_requests indexes
        if (Schema::hasTable('wfh_requests')) {
            Schema::table('wfh_requests', function (Blueprint $table) {
                if (Schema::hasColumn('wfh_requests', 'employee_id') && !$this->hasIndex('wfh_requests', 'wfh_requests_employee_id_index')) {
                    $table->index('employee_id');
                }
            });
        }

        // 8. attendance_regularizations indexes
        if (Schema::hasTable('attendance_regularizations')) {
            Schema::table('attendance_regularizations', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_regularizations', 'employee_id') && !$this->hasIndex('attendance_regularizations', 'attendance_regularizations_employee_id_index')) {
                    $table->index('employee_id');
                }
            });
        }

        // 9. holiday_work_requests indexes
        if (Schema::hasTable('holiday_work_requests')) {
            Schema::table('holiday_work_requests', function (Blueprint $table) {
                if (Schema::hasColumn('holiday_work_requests', 'employee_id') && !$this->hasIndex('holiday_work_requests', 'holiday_work_requests_employee_id_index')) {
                    $table->index('employee_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if ($this->hasIndex('attendances', 'attendances_employee_id_index')) {
                    $table->dropIndex(['employee_id']);
                }
                if ($this->hasIndex('attendances', 'attendances_attendance_date_index')) {
                    $table->dropIndex(['attendance_date']);
                }
            });
        }

        if (Schema::hasTable('generated_documents')) {
            Schema::table('generated_documents', function (Blueprint $table) {
                if ($this->hasIndex('generated_documents', 'generated_documents_employee_id_index')) {
                    $table->dropIndex(['employee_id']);
                }
                if ($this->hasIndex('generated_documents', 'generated_documents_user_id_index')) {
                    $table->dropIndex(['user_id']);
                }
            });
        }

        if (Schema::hasTable('employee_documents_new')) {
            Schema::table('employee_documents_new', function (Blueprint $table) {
                if ($this->hasIndex('employee_documents_new', 'employee_documents_new_employee_id_index')) {
                    $table->dropIndex(['employee_id']);
                }
            });
        }

        if (Schema::hasTable('leave_requests')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                if ($this->hasIndex('leave_requests', 'leave_requests_employee_id_index')) {
                    $table->dropIndex(['employee_id']);
                }
            });
        }

        if (Schema::hasTable('enterprise_payrolls')) {
            Schema::table('enterprise_payrolls', function (Blueprint $table) {
                if ($this->hasIndex('enterprise_payrolls', 'enterprise_payrolls_employee_id_index')) {
                    $table->dropIndex(['employee_id']);
                }
                if ($this->hasIndex('enterprise_payrolls', 'enterprise_payrolls_payroll_run_id_index')) {
                    $table->dropIndex(['payroll_run_id']);
                }
            });
        }

        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if ($this->hasIndex('notifications', 'notifications_user_id_index')) {
                    $table->dropIndex(['user_id']);
                }
            });
        }

        if (Schema::hasTable('wfh_requests')) {
            Schema::table('wfh_requests', function (Blueprint $table) {
                if ($this->hasIndex('wfh_requests', 'wfh_requests_employee_id_index')) {
                    $table->dropIndex(['employee_id']);
                }
            });
        }

        if (Schema::hasTable('attendance_regularizations')) {
            Schema::table('attendance_regularizations', function (Blueprint $table) {
                if ($this->hasIndex('attendance_regularizations', 'attendance_regularizations_employee_id_index')) {
                    $table->dropIndex(['employee_id']);
                }
            });
        }

        if (Schema::hasTable('holiday_work_requests')) {
            Schema::table('holiday_work_requests', function (Blueprint $table) {
                if ($this->hasIndex('holiday_work_requests', 'holiday_work_requests_employee_id_index')) {
                    $table->dropIndex(['employee_id']);
                }
            });
        }
    }
}
