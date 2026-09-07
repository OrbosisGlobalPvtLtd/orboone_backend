<?php

namespace App\Services\HRMS\Employee;

use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Leave\LeaveAllocationService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeLifecycleService
{
    public function __construct(private LeaveAllocationService $leaveAllocationService) {}

    public function calculateProbationDates(string $startDateStr, string $durationType = 'months', int $durationValue = 3): array
    {
        $durationType = strtolower(trim($durationType));
        if (! in_array($durationType, ['months', 'days'], true)) {
            $durationType = 'months';
        }

        $durationValue = max(1, $durationValue);
        $startDate = Carbon::parse($startDateStr, 'Asia/Kolkata');

        if ($durationType === 'days') {
            $probationEnd = $startDate->copy()->addDays($durationValue - 1);
        } else {
            $targetMonth = $startDate->copy()->addMonthsNoOverflow($durationValue);
            if ($startDate->isLastOfMonth() || $startDate->day > $targetMonth->daysInMonth) {
                $probationEnd = $targetMonth->copy()->endOfMonth();
            } else {
                $probationEnd = $startDate->copy()->addMonthsNoOverflow($durationValue)->subDay();
            }
        }

        $permanentEffectiveDate = $probationEnd->copy()->addDay();

        return [
            'probation_start_date' => $startDate->toDateString(),
            'probation_end_date' => $probationEnd->toDateString(),
            'confirmation_effective_date' => $permanentEffectiveDate->toDateString(),
            'permanent_effective_date' => $permanentEffectiveDate->toDateString(),
            'probation_duration_type' => $durationType,
            'probation_duration_value' => $durationValue,
            'probation_months' => $durationType === 'months' ? $durationValue : (int) ceil($durationValue / 30.0),
        ];
    }

    public function buildLifecyclePayload(
        array $input,
        ?string $existingProbationStatus = null,
        ?string $existingEmployeeStage = null,
        bool $preserveExistingStage = false
    ): array {
        $employmentType = (string) ($input['employment_type'] ?? '');
        $requestedStage = (string) ($input['employee_stage'] ?? '');
        $employeeStage = $this->resolveEmployeeStage($employmentType, $existingEmployeeStage, $preserveExistingStage, $requestedStage);
        $joiningDateStr = ! empty($input['joining_date'])
            ? (string) $input['joining_date']
            : null;
        $joiningDate = $joiningDateStr ? Carbon::parse($joiningDateStr, 'Asia/Kolkata') : null;

        $isInternshipStage = $employeeStage === 'internship';

        $salary = (float) ($input['actual_salary'] ?? 0);
        if ($isInternshipStage && (int) ($input['is_paid_intern'] ?? 0) === 0) {
            $salary = 0;
        }

        $probationStart = Arr::get($input, 'probation_start_date');
        $probationEnd = Arr::get($input, 'probation_end_date');
        $probationStatus = Arr::get($input, 'probation_status') ?: ($existingProbationStatus ?: 'pending');

        $durationOption = Arr::get($input, 'probation_duration_option');
        $durationType = 'months';
        $durationValue = 3;

        if ($durationOption === '6_months') {
            $durationType = 'months';
            $durationValue = 6;
        } elseif ($durationOption === '3_months') {
            $durationType = 'months';
            $durationValue = 3;
        } elseif ($durationOption === 'custom') {
            $durationType = strtolower((string) Arr::get($input, 'probation_duration_type', Arr::get($input, 'custom_duration_unit', 'months')));
            $durationValue = (int) Arr::get($input, 'probation_duration_value', Arr::get($input, 'custom_duration_value', 1));
            if ($durationValue < 1) {
                $durationValue = 1;
            }
        } elseif (isset($input['probation_duration_type']) && isset($input['probation_duration_value'])) {
            $durationType = strtolower((string) $input['probation_duration_type']);
            $durationValue = max(1, (int) $input['probation_duration_value']);
        } elseif (isset($input['probation_months']) && is_numeric($input['probation_months'])) {
            $durationType = 'months';
            $durationValue = max(1, (int) $input['probation_months']);
        }

        $confirmationEffectiveDate = null;

        if ($joiningDate && !$isInternshipStage) {
            if (!$probationStart) {
                $probationStart = $joiningDate->format('Y-m-d');
            }

            $calculatedDates = $this->calculateProbationDates($probationStart, $durationType, $durationValue);
            $probationEnd = $calculatedDates['probation_end_date'];
            $confirmationEffectiveDate = $calculatedDates['confirmation_effective_date'];

            $manualConfirmationDate = Arr::get($input, 'confirmation_date')
                ?: (Arr::get($input, 'confirmation_effective_date')
                    ?: Arr::get($input, 'permanent_at'));

            if (!empty($manualConfirmationDate)) {
                try {
                    $confirmationEffectiveDate = Carbon::parse($manualConfirmationDate, 'Asia/Kolkata')->format('Y-m-d');
                } catch (\Throwable $e) {
                    // Fallback to calculated date if parse fails
                }
            }

            if ($employeeStage === 'probation') {
                if (Carbon::now('Asia/Kolkata')->startOfDay()->greaterThan(Carbon::parse($probationEnd, 'Asia/Kolkata')->startOfDay())) {
                    $employeeStage = 'permanent';
                    $probationStatus = 'completed';
                } else {
                    $probationStatus = in_array($existingProbationStatus, ['completed', 'confirmed'], true)
                        ? $existingProbationStatus
                        : 'ongoing';
                }
            } else {
                $probationStatus = 'completed';
            }
        } elseif ($employeeStage === 'permanent') {
            $probationStatus = 'completed';
            $confirmationEffectiveDate = Arr::get($input, 'confirmation_effective_date')
                ?: ($probationEnd ? Carbon::parse($probationEnd, 'Asia/Kolkata')->addDay()->format('Y-m-d') : ($joiningDateStr ?: now('Asia/Kolkata')->toDateString()));
        }

        $internshipStart = Arr::get($input, 'internship_start_date');
        $internshipEnd = Arr::get($input, 'internship_end_date');

        if ($isInternshipStage && $internshipStart && !$internshipEnd) {
            $duration = isset($input['internship_duration_months']) && is_numeric($input['internship_duration_months'])
                ? (int)$input['internship_duration_months']
                : 3;
            $startDateObj = Carbon::parse($internshipStart, 'Asia/Kolkata');
            $internshipEnd = $startDateObj->copy()->addMonthsNoOverflow($duration)->subDay()->format('Y-m-d');
        }

        $actualPermanentAt = $employeeStage === 'permanent' ? $confirmationEffectiveDate : null;

        return [
            'employee_stage' => $employeeStage,
            'work_schedule_type' => Arr::get($input, 'work_schedule_type'),
            'joining_date' => $joiningDateStr,
            'relieving_date' => Arr::get($input, 'relieving_date'),
            'probation_start_date' => $isInternshipStage ? null : $probationStart,
            'probation_end_date' => $isInternshipStage ? null : $probationEnd,
            'confirmation_effective_date' => $actualPermanentAt,
            'probation_status' => $isInternshipStage ? null : $probationStatus,
            'probation_months' => $isInternshipStage ? null : $durationValue,
            'probation_duration_type' => $isInternshipStage ? null : $durationType,
            'probation_duration_value' => $isInternshipStage ? null : $durationValue,
            'internship_start_date' => $internshipStart,
            'internship_end_date' => $internshipEnd,
            'is_paid_intern' => $isInternshipStage
                ? (Arr::has($input, 'is_paid_intern') ? (int) $input['is_paid_intern'] : null)
                : null,
            'actual_salary' => $salary,
        ];
    }

    private function resolveEmployeeStage(
        string $employmentType,
        ?string $existingEmployeeStage,
        bool $preserveExistingStage,
        ?string $requestedStage = null
    ): string {
        if ($requestedStage && in_array($requestedStage, ['internship', 'probation', 'permanent', 'freelance', 'contract'], true)) {
            return $requestedStage;
        }

        if ($employmentType === 'intern') {
            return 'internship';
        }

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
            || ! Schema::hasColumn('leave_allocations', 'paid_allocated')
            || ! Schema::hasColumn('leave_allocations', 'sick_allocated')
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
                    'total_allocated' => 25,
                    'paid_allocated' => 18,
                    'sick_allocated' => 7,
                    'comp_off_allocated' => (float) ($existing->comp_off_allocated ?? 0),
                    'total_remaining' => max(0, 25 - (float) ($existing->total_used ?? 0)),
                    'paid_remaining' => max(0, 18 - (float) ($existing->paid_used ?? 0)),
                    'sick_remaining' => max(0, 7 - (float) ($existing->sick_used ?? 0)),
                    'comp_off_remaining' => max(0, (float) ($existing->comp_off_allocated ?? 0) - (float) ($existing->comp_off_used ?? 0)),
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('leave_allocations')->insert([
            'employee_id' => $employeeId,
            'year' => $year,
            'total_allocated' => 25,
            'paid_allocated' => 18,
            'sick_allocated' => 7,
            'comp_off_allocated' => 0,
            'total_used' => 0,
            'paid_used' => 0,
            'sick_used' => 0,
            'comp_off_used' => 0,
            'lwp_used' => 0,
            'total_remaining' => 25,
            'paid_remaining' => 18,
            'sick_remaining' => 7,
            'comp_off_remaining' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function autoAllocateForStage(int $employeeId, ?string $forceStage = null, ?string $effectiveDate = null, ?int $actorId = null): void
    {
        $employee = EmployeeM::find($employeeId);
        if (! $employee) {
            return;
        }

        $stage = $forceStage ? strtolower($forceStage) : strtolower((string) ($employee->employee_stage ?: $employee->employment_type));
        if ($stage === 'intern') {
            $stage = 'internship';
        }

        $date = $effectiveDate ? Carbon::parse($effectiveDate, 'Asia/Kolkata') : null;
        $year = (int) ($date?->year ?: Carbon::now('Asia/Kolkata')->year);

        $this->leaveAllocationService->generateForEmployee($employee, $year, $actorId, $stage, $date);
    }
}
