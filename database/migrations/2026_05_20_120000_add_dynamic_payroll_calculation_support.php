<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensurePayrollColumns();
        $this->ensurePayslipColumns();
        $this->ensureClaimColumns();
        $this->ensurePayrollAdjustmentsTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }

    private function ensurePayrollColumns(): void
    {
        if (! Schema::hasTable('payrolls')) {
            return;
        }

        Schema::table('payrolls', function (Blueprint $table) {
            $this->addUnsignedBigInteger($table, 'payrolls', 'salary_structure_id');
            $this->addUnsignedBigInteger($table, 'payrolls', 'monthly_attendance_summary_id');
            $this->addDecimal($table, 'payrolls', 'payable_days', 8, 2, 0);
            $this->addDecimal($table, 'payrolls', 'present_days', 8, 2, 0);
            $this->addDecimal($table, 'payrolls', 'paid_leave_days', 8, 2, 0);
            $this->addDecimal($table, 'payrolls', 'sick_leave_days', 8, 2, 0);
            $this->addDecimal($table, 'payrolls', 'comp_off_days', 8, 2, 0);
            $this->addDecimal($table, 'payrolls', 'holiday_days', 8, 2, 0);
            $this->addDecimal($table, 'payrolls', 'week_off_days', 8, 2, 0);
            $this->addDecimal($table, 'payrolls', 'half_days', 8, 2, 0);
            $this->addDecimal($table, 'payrolls', 'lwp_days', 8, 2, 0);
            $this->addDecimal($table, 'payrolls', 'absent_days', 8, 2, 0);
            $this->addDecimal($table, 'payrolls', 'monthly_gross_salary', 12, 2, 0);
            $this->addDecimal($table, 'payrolls', 'daily_gross_rate', 12, 4, 0);
            $this->addDecimal($table, 'payrolls', 'attendance_loss_days', 8, 2, 0);
            $this->addDecimal($table, 'payrolls', 'lwp_deduction', 12, 2, 0);
            $this->addDecimal($table, 'payrolls', 'absent_deduction', 12, 2, 0);
            $this->addDecimal($table, 'payrolls', 'half_day_deduction', 12, 2, 0);
            $this->addDecimal($table, 'payrolls', 'bonus', 12, 2, 0);
            $this->addDecimal($table, 'payrolls', 'incentive', 12, 2, 0);
            $this->addDecimal($table, 'payrolls', 'reimbursements', 12, 2, 0);
            $this->addDecimal($table, 'payrolls', 'tds', 12, 2, 0);
            $this->addDecimal($table, 'payrolls', 'other_deductions', 12, 2, 0);
            $this->addJson($table, 'payrolls', 'calculation_snapshot');
            $this->addUnsignedBigInteger($table, 'payrolls', 'generated_by');
            $this->addTimestamp($table, 'payrolls', 'generated_at');
            $this->addUnsignedBigInteger($table, 'payrolls', 'approved_by');
            $this->addTimestamp($table, 'payrolls', 'approved_at');
            $this->addUnsignedBigInteger($table, 'payrolls', 'locked_by');
            $this->addTimestamp($table, 'payrolls', 'locked_at');
        });

        $this->addIndex('payrolls', ['employee_id', 'month', 'year'], 'payrolls_employee_period_idx');
        $this->addIndex('payrolls', ['status'], 'payrolls_status_idx');
    }

    private function ensurePayslipColumns(): void
    {
        if (! Schema::hasTable('payslips')) {
            return;
        }

        Schema::table('payslips', function (Blueprint $table) {
            $this->addUnsignedBigInteger($table, 'payslips', 'generated_by');
            $this->addTimestamp($table, 'payslips', 'generated_at');
        });

        $this->addIndex('payslips', ['employee_id', 'month', 'year'], 'payslips_employee_period_idx');
    }

    private function ensureClaimColumns(): void
    {
        if (! Schema::hasTable('claims')) {
            return;
        }

        Schema::table('claims', function (Blueprint $table) {
            $this->addUnsignedTinyInteger($table, 'claims', 'payroll_month');
            $this->addUnsignedInteger($table, 'claims', 'payroll_year');
            $this->addUnsignedBigInteger($table, 'claims', 'approved_by');
            $this->addTimestamp($table, 'claims', 'approved_at');
            $this->addUnsignedBigInteger($table, 'claims', 'rejected_by');
            $this->addTimestamp($table, 'claims', 'rejected_at');
            $this->addText($table, 'claims', 'approval_note');
        });

        $this->addIndex('claims', ['employee_id', 'payroll_month', 'payroll_year', 'status'], 'claims_employee_period_status_idx');
    }

    private function ensurePayrollAdjustmentsTable(): void
    {
        if (Schema::hasTable('payroll_adjustments')) {
            return;
        }

        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('year');
            $table->string('type', 40);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('title')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status', 40)->default('approved');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('payroll_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'month', 'year'], 'payroll_adj_employee_period_idx');
            $table->index(['type', 'status'], 'payroll_adj_type_status_idx');
            $table->index('payroll_id', 'payroll_adj_payroll_idx');
        });
    }

    private function addUnsignedBigInteger(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->unsignedBigInteger($column)->nullable();
        }
    }

    private function addUnsignedInteger(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->unsignedInteger($column)->nullable();
        }
    }

    private function addUnsignedTinyInteger(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->unsignedTinyInteger($column)->nullable();
        }
    }

    private function addDecimal(Blueprint $table, string $tableName, string $column, int $total, int $places, float $default): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->decimal($column, $total, $places)->default($default);
        }
    }

    private function addJson(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->json($column)->nullable();
        }
    }

    private function addText(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->text($column)->nullable();
        }
    }

    private function addTimestamp(Blueprint $table, string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            $table->timestamp($column)->nullable();
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
            // Older installs may already have equivalent indexes with legacy names.
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
