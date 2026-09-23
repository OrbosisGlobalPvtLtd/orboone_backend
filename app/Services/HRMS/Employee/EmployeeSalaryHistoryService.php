<?php

namespace App\Services\HRMS\Employee;

use App\Services\HRMS\EnterprisePayroll\EnterpriseSalaryStructureSyncS;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeSalaryHistoryService
{
    private string $employeeTable = 'employees_new';
    private string $salaryHistoryTable = 'employee_salary_histories';

    public function syncSalary(
        int $employeeId,
        ?string $stage,
        $salaryAmount,
        ?string $effectiveFrom = null,
        ?string $reason = null,
        ?int $actorId = null,
        bool $syncEnterprise = true
    ): void {
        $this->performSyncSalary($employeeId, $stage, $salaryAmount, $effectiveFrom, $reason, $actorId);

        if (! $syncEnterprise) {
            return;
        }

        try {
            $updatedEmployee = DB::table($this->employeeTable)->where('id', $employeeId)->first();
            if ($updatedEmployee) {
                app(EnterpriseSalaryStructureSyncS::class)->syncFromEmployee($updatedEmployee, $reason);
            }
        } catch (\Throwable $e) {
            Log::error('Enterprise payroll salary sync failed: '.$e->getMessage());
        }
    }

    /** Record the activation-date salary revision inside the lifecycle transaction. */
    public function syncPermanentActivation(int $employeeId, string $effectiveDate, ?int $actorId = null): void
    {
        if (! Schema::hasTable($this->employeeTable) || ! Schema::hasTable($this->salaryHistoryTable)) {
            throw new RuntimeException('Employee salary history schema is unavailable for permanent activation.');
        }

        $employee = DB::table($this->employeeTable)->where('id', $employeeId)->first();
        if (! $employee || $employee->actual_salary === null) {
            return;
        }

        $this->performSyncSalary(
            $employeeId,
            'permanent',
            $employee->actual_salary,
            $effectiveDate,
            'Permanent activation salary history',
            $actorId,
            true
        );
    }

    private function performSyncSalary(
        int $employeeId,
        ?string $stage,
        $salaryAmount,
        ?string $effectiveFrom = null,
        ?string $reason = null,
        ?int $actorId = null,
        bool $forceLifecycleRevision = false
    ): void {
        if (
            ! Schema::hasTable($this->employeeTable)
            || ! Schema::hasTable($this->salaryHistoryTable)
        ) {
            return;
        }

        $employee = DB::table($this->employeeTable)->where('id', $employeeId)->first();

        if (! $employee) {
            return;
        }

        $effectiveDate = $this->normaliseEffectiveDate($effectiveFrom, $employee);
        $newSalary = $this->normaliseSalary($salaryAmount);
        $stage = $stage ?: ($employee->employee_stage ?? null) ?: 'probation';
        $currentSalary = $this->normaliseSalary($employee->actual_salary ?? 0);

        $activeHistory = DB::table($this->salaryHistoryTable)
            ->where('employee_id', $employeeId)
            ->whereNull('effective_to')
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        if ($activeHistory && $this->sameDate($activeHistory->effective_from, $effectiveDate)) {
            DB::table($this->salaryHistoryTable)
                ->where('id', $activeHistory->id)
                ->update([
                    'stage' => $stage,
                    'salary_amount' => $newSalary,
                    'reason' => $reason ?: $activeHistory->reason,
                    'updated_by' => $actorId,
                    'updated_at' => now(),
                ]);

            $this->updateEmployeeSalary($employeeId, $newSalary, $actorId);

            return;
        }

        if (! $forceLifecycleRevision && $activeHistory && $this->sameMoney($activeHistory->salary_amount, $newSalary)) {
            if (! $this->sameMoney($currentSalary, $newSalary)) {
                $this->updateEmployeeSalary($employeeId, $newSalary, $actorId);
            }

            return;
        }

        if ($activeHistory) {
            $previousEffectiveTo = Carbon::parse($effectiveDate, 'Asia/Kolkata')->subDay()->toDateString();

            if (Carbon::parse($previousEffectiveTo, 'Asia/Kolkata')->lt(Carbon::parse($activeHistory->effective_from, 'Asia/Kolkata'))) {
                DB::table($this->salaryHistoryTable)
                    ->where('id', $activeHistory->id)
                    ->update([
                        'effective_to' => $activeHistory->effective_from,
                        'updated_by' => $actorId,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table($this->salaryHistoryTable)
                    ->where('id', $activeHistory->id)
                    ->update([
                        'effective_to' => $previousEffectiveTo,
                        'updated_by' => $actorId,
                        'updated_at' => now(),
                    ]);
            }
        }

        DB::table($this->salaryHistoryTable)->insert([
            'employee_id' => $employeeId,
            'stage' => $stage,
            'salary_amount' => $newSalary,
            'effective_from' => $effectiveDate,
            'effective_to' => null,
            'reason' => $reason,
            'created_by' => $actorId,
            'updated_by' => $actorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->updateEmployeeSalary($employeeId, $newSalary, $actorId);
    }

    private function updateEmployeeSalary(int $employeeId, float $salary, ?int $actorId): void
    {
        DB::table($this->employeeTable)
            ->where('id', $employeeId)
            ->update([
                'actual_salary' => $salary,
                'updated_by' => $actorId,
                'updated_at' => now(),
            ]);
    }

    private function normaliseEffectiveDate(?string $effectiveFrom, $employee): string
    {
        $date = $effectiveFrom
            ?: ($employee->joining_date ?? null)
            ?: ($employee->internship_start_date ?? null)
            ?: now('Asia/Kolkata')->toDateString();

        return Carbon::parse($date, 'Asia/Kolkata')->toDateString();
    }

    private function normaliseSalary($salary): float
    {
        return round((float) ($salary ?? 0), 2);
    }

    private function sameMoney($left, $right): bool
    {
        return abs($this->normaliseSalary($left) - $this->normaliseSalary($right)) < 0.01;
    }

    private function sameDate($left, string $right): bool
    {
        return Carbon::parse($left, 'Asia/Kolkata')->toDateString() === Carbon::parse($right, 'Asia/Kolkata')->toDateString();
    }
}
