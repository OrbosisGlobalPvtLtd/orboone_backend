<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateEmployeesNewLifecycleAndSalaryHistory extends Migration
{
    private string $employeeTable = 'employees_new';
    private string $salaryHistoryTable = 'employee_salary_histories';

    public function up()
    {
        if (! Schema::hasTable($this->employeeTable)) {
            return;
        }

        $this->ensureEmploymentTypeSupportsPartTime();

        if (! Schema::hasColumn($this->employeeTable, 'employee_stage')) {
            Schema::table($this->employeeTable, function (Blueprint $table) {
                $table->enum('employee_stage', [
                    'internship',
                    'probation',
                    'permanent',
                    'freelance',
                    'contract',
                ])->nullable()->after('employment_type');
            });
        }

        if (! Schema::hasColumn($this->employeeTable, 'work_schedule_type')) {
            Schema::table($this->employeeTable, function (Blueprint $table) {
                $table->enum('work_schedule_type', [
                    'full_day',
                    'part_day',
                    'hourly',
                    'shift_based',
                ])->nullable()->after('work_mode');
            });
        }

        if (! Schema::hasTable($this->salaryHistoryTable)) {
            Schema::create($this->salaryHistoryTable, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('employee_id');
                $table->string('stage', 50);
                $table->decimal('salary_amount', 12, 2)->default(0);
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->string('reason')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'effective_from']);
                $table->index(['employee_id', 'effective_to']);

                $table->foreign('employee_id')
                    ->references('id')
                    ->on($this->employeeTable)
                    ->onDelete('cascade');

                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');

                    $table->foreign('updated_by')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');
                }
            });
        }

        $this->backfillEmployeeStages();
        $this->backfillSalaryHistory();
    }

    public function down()
    {
        if (Schema::hasTable($this->salaryHistoryTable)) {
            Schema::dropIfExists($this->salaryHistoryTable);
        }

        if (Schema::hasTable($this->employeeTable)) {
            Schema::table($this->employeeTable, function (Blueprint $table) {
                if (Schema::hasColumn($this->employeeTable, 'work_schedule_type')) {
                    $table->dropColumn('work_schedule_type');
                }

                if (Schema::hasColumn($this->employeeTable, 'employee_stage')) {
                    $table->dropColumn('employee_stage');
                }
            });

            if (DB::getDriverName() === 'mysql') {
                DB::table($this->employeeTable)
                    ->where('employment_type', 'part_time')
                    ->update(['employment_type' => 'full_time']);

                DB::statement(
                    "ALTER TABLE {$this->employeeTable} MODIFY employment_type ENUM('full_time','intern','freelancer','contract') NOT NULL"
                );
            }
        }
    }

    private function ensureEmploymentTypeSupportsPartTime(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $columns = DB::select("SHOW COLUMNS FROM {$this->employeeTable} WHERE Field = 'employment_type'");
        $type = $columns[0]->Type ?? '';

        if (strpos($type, "'part_time'") !== false) {
            return;
        }

        DB::statement(
            "ALTER TABLE {$this->employeeTable} MODIFY employment_type ENUM('full_time','part_time','intern','freelancer','contract') NOT NULL"
        );
    }

    private function backfillEmployeeStages(): void
    {
        if (! Schema::hasColumn($this->employeeTable, 'employee_stage')) {
            return;
        }

        DB::table($this->employeeTable)
            ->whereNull('employee_stage')
            ->update([
                'employee_stage' => DB::raw(
                    "CASE
                        WHEN employment_type = 'intern' THEN 'internship'
                        WHEN employment_type = 'full_time' AND probation_status IN ('completed', 'confirmed') THEN 'permanent'
                        WHEN employment_type = 'full_time' THEN 'probation'
                        WHEN employment_type = 'part_time' THEN 'permanent'
                        WHEN employment_type = 'freelancer' THEN 'freelance'
                        WHEN employment_type = 'contract' THEN 'contract'
                        ELSE 'probation'
                    END"
                ),
                'updated_at' => now(),
            ]);
    }

    private function backfillSalaryHistory(): void
    {
        if (! Schema::hasTable($this->salaryHistoryTable)) {
            return;
        }

        DB::table($this->employeeTable)
            ->select([
                'id',
                'employee_stage',
                'employment_type',
                'probation_status',
                'actual_salary',
                'joining_date',
                'internship_start_date',
                'created_at',
                'created_by',
                'updated_by',
            ])
            ->orderBy('id')
            ->chunkById(100, function ($employees) {
                foreach ($employees as $employee) {
                    $exists = DB::table($this->salaryHistoryTable)
                        ->where('employee_id', $employee->id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    DB::table($this->salaryHistoryTable)->insert([
                        'employee_id' => $employee->id,
                        'stage' => $employee->employee_stage ?: $this->deriveStage($employee),
                        'salary_amount' => $employee->actual_salary ?? 0,
                        'effective_from' => $this->initialEffectiveDate($employee),
                        'effective_to' => null,
                        'reason' => 'Initial salary backfill',
                        'created_by' => $employee->created_by,
                        'updated_by' => $employee->updated_by,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    private function deriveStage($employee): string
    {
        if ($employee->employment_type === 'intern') {
            return 'internship';
        }

        if ($employee->employment_type === 'freelancer') {
            return 'freelance';
        }

        if ($employee->employment_type === 'contract') {
            return 'contract';
        }

        if ($employee->employment_type === 'full_time' && in_array($employee->probation_status, ['completed', 'confirmed'], true)) {
            return 'permanent';
        }

        if ($employee->employment_type === 'part_time') {
            return 'permanent';
        }

        return 'probation';
    }

    private function initialEffectiveDate($employee): string
    {
        $date = $employee->joining_date
            ?: $employee->internship_start_date
            ?: $employee->created_at;

        return $date
            ? Carbon::parse($date)->toDateString()
            : now()->toDateString();
    }
}
