<?php

namespace App\Services\HRMS\Employee;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeLifecycleService
{
    public function buildLifecyclePayload(
        array $input,
        ?string $existingProbationStatus = null,
        ?string $existingEmployeeStage = null,
        bool $preserveExistingStage = false
    ): array {
        $employmentType = (string) ($input['employment_type'] ?? '');
        $employeeStage = $this->resolveEmployeeStage($employmentType, $existingEmployeeStage, $preserveExistingStage);
        $joiningDate = ! empty($input['joining_date'])
            ? Carbon::parse($input['joining_date'])
            : null;

        $isInternshipStage = $employeeStage === 'internship';

        $salary = (float) ($input['actual_salary'] ?? 0);
        if ($isInternshipStage && (int) ($input['is_paid_intern'] ?? 0) === 0) {
            $salary = 0;
        }

        $probationStart = null;
        $probationEnd = null;
        $probationStatus = 'pending';

        if ($employeeStage === 'probation' && $joiningDate) {
            $probationStart = $joiningDate->copy()->format('Y-m-d');
            $probationEnd = $joiningDate->copy()->addMonths(3)->format('Y-m-d');
            $probationStatus = in_array($existingProbationStatus, ['completed', 'confirmed'], true)
                ? $existingProbationStatus
                : 'ongoing';
        } elseif ($employeeStage === 'permanent') {
            $probationStatus = 'completed';
        }

        return [
            'employee_stage' => $employeeStage,
            'work_schedule_type' => Arr::get($input, 'work_schedule_type'),
            'joining_date' => $isInternshipStage ? null : Arr::get($input, 'joining_date'),
            'relieving_date' => Arr::get($input, 'relieving_date'),
            'probation_start_date' => $probationStart,
            'probation_end_date' => $probationEnd,
            'probation_status' => $probationStatus,
            'internship_start_date' => $isInternshipStage ? Arr::get($input, 'internship_start_date') : null,
            'internship_end_date' => $isInternshipStage ? Arr::get($input, 'internship_end_date') : null,
            'is_paid_intern' => $isInternshipStage
                ? (Arr::has($input, 'is_paid_intern') ? (int) $input['is_paid_intern'] : null)
                : null,
            'actual_salary' => $salary,
        ];
    }

    private function resolveEmployeeStage(
        string $employmentType,
        ?string $existingEmployeeStage,
        bool $preserveExistingStage
    ): string {
        if (
            $preserveExistingStage
            && in_array($existingEmployeeStage, ['internship', 'probation', 'permanent', 'freelance', 'contract'], true)
        ) {
            return $existingEmployeeStage;
        }

        return match ($employmentType) {
            'intern' => 'internship',
            'freelancer' => 'freelance',
            'contract' => 'contract',
            'full_time', 'part_time' => 'probation',
            default => 'probation',
        };
    }

    public function isAdminActor($actor): bool
    {
        if (! $actor) {
            return false;
        }

        if (method_exists($actor, 'hasRole')) {
            return $actor->hasRole([
                'super_admin',
                'admin',
                'hr_admin',
                'finance_admin',
                'project_admin',
                'operations_admin',
            ]);
        }

        return false;
    }

    public function applyEditableFieldPolicy(array $input, $actor): array
    {
        if ($this->isAdminActor($actor)) {
            return $input;
        }

        $allowed = [
            'name',
            'phone',
            'address',
            'date_of_birth',
            'bank_account_no',
            'bank_account_type',
            'bank_holder_name',
            'ifsc_code',
            'bank_branch',
            'profile_image',
            'resume_file',
        ];

        return Arr::only($input, $allowed);
    }

    public function autoAllocateLeaveAfterProbationIfEligible(
        int $employeeId,
        ?string $probationEndDate
    ): void {
        if (
            ! $probationEndDate
            || ! Schema::hasTable('leave_allocations')
            || ! Schema::hasColumn('leave_allocations', 'total_pl')
            || ! Schema::hasColumn('leave_allocations', 'total_sl')
        ) {
            return;
        }

        try {
            $probationEnded = Carbon::parse($probationEndDate)->lte(now());
        } catch (\Throwable $e) {
            $probationEnded = false;
        }

        if (! $probationEnded) {
            return;
        }

        // Current leave allocation table references employees (legacy table),
        // so skip auto allocation when that record doesn't exist.
        if (! Schema::hasTable('employees') || ! DB::table('employees')->where('id', $employeeId)->exists()) {
            return;
        }

        $year = (int) now()->format('Y');

        $existing = DB::table('leave_allocations')
            ->where('employee_id', $employeeId)
            ->where('year', $year)
            ->first();

        if ($existing) {
            DB::table('leave_allocations')
                ->where('id', $existing->id)
                ->update([
                    'total_pl' => 18,
                    'total_sl' => 7,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('leave_allocations')->insert([
            'employee_id' => $employeeId,
            'year' => $year,
            'total_pl' => 18,
            'total_sl' => 7,
            'used_pl' => 0,
            'used_sl' => 0,
            'lwp_days' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
