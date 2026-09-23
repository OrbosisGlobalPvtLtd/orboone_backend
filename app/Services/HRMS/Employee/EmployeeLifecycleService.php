<?php

namespace App\Services\HRMS\Employee;

use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Leave\LeaveAllocationService;
use App\Services\HRMS\EnterprisePayroll\EnterpriseSalaryStructureSyncS;
use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class EmployeeLifecycleService
{
    private ?array $employeeColumns = null;

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

            $today = Carbon::today('Asia/Kolkata');
            $probationEndCarbon = Carbon::parse($probationEnd, 'Asia/Kolkata')->startOfDay();

            if ($probationEndCarbon->greaterThanOrEqualTo($today)) {
                if ($requestedStage !== 'permanent') {
                    $employeeStage = 'probation';
                    $probationStatus = 'ongoing';
                }
            } else {
                if ($employeeStage === 'probation') {
                    $probationStatus = in_array($existingProbationStatus, ['completed', 'confirmed', 'extended', 'scheduled_permanent'], true)
                        ? $existingProbationStatus
                        : 'ongoing';
                } else {
                    $probationStatus = 'completed';
                }
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

        return [
            'employee_stage' => $employeeStage,
            'work_schedule_type' => Arr::get($input, 'work_schedule_type'),
            'joining_date' => $joiningDateStr,
            'relieving_date' => Arr::get($input, 'relieving_date'),
            'probation_start_date' => $isInternshipStage ? null : $probationStart,
            'probation_end_date' => $isInternshipStage ? null : $probationEnd,
            'confirmation_effective_date' => $isInternshipStage ? null : $confirmationEffectiveDate,
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
        if (! $probationEndDate) {
            return;
        }
        $this->activatePermanent(new EmployeeM(['id' => $employeeId]), '', 'auto_expiry');
    }

    /** The single transactional entry point for every probation-to-permanent activation source. */
    public function activatePermanent(
        EmployeeM $employee,
        string $effectiveDate,
        ?string $source = null,
        ?int $actorId = null,
        mixed $salaryAmount = null
    ): array
    {
        $employeeId = (int) $employee->id;
        $source = $source ?: 'manual';

        try {
            return DB::transaction(function () use ($employeeId, $effectiveDate, $source, $actorId, $salaryAmount) {
            $employeeRow = DB::table('employees_new')->where('id', $employeeId)->lockForUpdate()->first();
            $fresh = $employeeRow ? (new EmployeeM())->newFromBuilder((array) $employeeRow) : null;
            if (! $fresh || $fresh->employee_stage !== 'probation'
                || in_array($fresh->probation_status, ['completed', 'confirmed'], true)
                || (int) ($fresh->is_active ?? 1) === 0
                || in_array(strtolower((string) ($fresh->employment_status ?? 'active')), ['inactive', 'terminated', 'exited', 'resigned_and_exited'], true)) {
                return ['status' => 'skipped', 'employee_id' => $employeeId, 'effective_date' => null, 'reason' => 'employee_not_on_probation'];
            }

            $today = Carbon::today('Asia/Kolkata');
            if ($source === 'scheduled') {
                if ($fresh->probation_status !== 'scheduled_permanent' || ! $fresh->confirmation_effective_date
                    || Carbon::parse($fresh->confirmation_effective_date, 'Asia/Kolkata')->gt($today)) {
                    return ['status' => 'skipped', 'employee_id' => $employeeId, 'effective_date' => null, 'reason' => 'scheduled_confirmation_not_due'];
                }
                $effectiveDate = Carbon::parse($fresh->confirmation_effective_date, 'Asia/Kolkata')->toDateString();
            } elseif ($source === 'auto_expiry') {
                if (in_array($fresh->probation_status, ['completed', 'confirmed', 'extended', 'scheduled_permanent'], true)
                    || ! $fresh->probation_end_date
                    || ! Carbon::parse($fresh->probation_end_date, 'Asia/Kolkata')->lt($today)) {
                    return ['status' => 'skipped', 'employee_id' => $employeeId, 'effective_date' => null, 'reason' => 'probation_not_expired'];
                }
                $effectiveDate = Carbon::parse($fresh->probation_end_date, 'Asia/Kolkata')->addDay()->toDateString();
            } else {
                $effectiveDate = Carbon::parse($effectiveDate, 'Asia/Kolkata')->toDateString();
            }

            $update = [
                'employee_stage' => 'permanent',
                'probation_status' => 'completed',
                'confirmation_date' => $effectiveDate,
                'confirmation_effective_date' => $effectiveDate,
                'permanent_activated_at' => now('Asia/Kolkata'),
                'updated_at' => now('Asia/Kolkata'),
            ];
            if ($salaryAmount !== null) {
                $update['actual_salary'] = $salaryAmount;
            }
            $columns = $this->employeeColumns ??= Schema::getColumnListing('employees_new');
            if (in_array('is_permanent', $columns, true)) $update['is_permanent'] = 1;
            if (in_array('permanent_at', $columns, true)) $update['permanent_at'] = $effectiveDate;
            if ($actorId && in_array('updated_by', $columns, true)) $update['updated_by'] = $actorId;
            DB::table('employees_new')->where('id', $employeeId)->update($update);

            $fresh->fill($update);
            $effectiveDateCarbon = Carbon::parse($effectiveDate, 'Asia/Kolkata')->startOfDay();
            $this->leaveAllocationService->generateForEmployee(
                $fresh,
                $effectiveDateCarbon->year,
                $actorId,
                'permanent',
                $effectiveDateCarbon
            );

            // If activation is correcting a prior-year lifecycle, also establish
            // this year's independent full annual allocation. The allocation
            // service ignores the old effective date for later years.
            if ($effectiveDateCarbon->year < $today->year) {
                $this->leaveAllocationService->generateForEmployee(
                    $fresh,
                    $today->year,
                    $actorId,
                    'permanent'
                );
            }

            if ($fresh->actual_salary !== null) {
                app(EmployeeSalaryHistoryService::class)->syncPermanentActivation(
                    $employeeId,
                    $effectiveDate,
                    $actorId
                );
                $salaryEmployee = (object) $fresh->getAttributes();
                $salaryEmployee->salary_effective_from = $effectiveDate;
                app(EnterpriseSalaryStructureSyncS::class)->syncFromEmployee(
                    $salaryEmployee,
                    'Auto-synced on permanent confirmation date',
                    true
                );
            }

            app(NotificationS::class)->markEmployeeLifecycleNotificationsResolved(
                $employeeId,
                ['probation_ending_soon', 'probation_ending_reminder']
            );

            $eventKey = "permanent_activated:{$employeeId}:{$effectiveDate}";
            app(NotificationS::class)->ensurePermanentActivationEvent(
                $employeeId,
                (int) $fresh->user_id,
                $effectiveDate
            );

            DB::afterCommit(function () use ($employeeId, $effectiveDate, $eventKey) {
                try {
                    app(NotificationS::class)->dispatchPermanentActivationEvent($eventKey);
                } catch (\Throwable $e) {
                    Log::error('Permanent activation notification dispatch failed', [
                        'employee_id' => $employeeId,
                        'effective_date' => $effectiveDate,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

            return ['status' => 'activated', 'employee_id' => $employeeId, 'effective_date' => $effectiveDate, 'reason' => null];
            });
        } catch (\Throwable $e) {
            Log::error('Permanent lifecycle activation failed', [
                'employee_id' => $employeeId,
                'effective_date' => $effectiveDate,
                'source' => $source,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
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

        if ($stage === 'permanent' && ! $date) {
            $authoritativeDate = $employee->confirmation_effective_date ?: $employee->confirmation_date ?: $employee->permanent_at;
            $date = $authoritativeDate ? Carbon::parse($authoritativeDate, 'Asia/Kolkata') : null;
            if ($date) $year = (int) $date->year;
        }
        $this->leaveAllocationService->generateForEmployee($employee, $year, $actorId, $stage, $date);

        if ($stage === 'permanent' && $date && $date->year < Carbon::today('Asia/Kolkata')->year) {
            $this->leaveAllocationService->generateForEmployee(
                $employee,
                Carbon::today('Asia/Kolkata')->year,
                $actorId,
                $stage
            );
        }
    }
}
