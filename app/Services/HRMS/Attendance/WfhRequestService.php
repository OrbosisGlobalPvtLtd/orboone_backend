<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\WfhRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class WfhRequestService
{
    public function __construct(
        private WfhPolicyService $policyService,
        private AttendanceRuleResolverService $ruleResolver
    ) {
    }

    public function policy(): array
    {
        $this->policyService->ensureDefaults();
        return $this->policyService->all();
    }

    public function balance(EmployeeM $employee, int $month, int $year): array
    {
        $policy = $this->policy();
        $used = $this->approvedQuotaCount($employee->id, $month, $year);
        $limit = max(0, (int) ($policy['wfh_monthly_limit'] ?? 2));

        return [
            'month' => $month,
            'year' => $year,
            'monthly_limit' => $limit,
            'used' => $used,
            'remaining' => max(0, $limit - $used),
            'non_quota_approved' => $this->approvedNonQuotaCount($employee->id, $month, $year),
        ];
    }

    public function apply(EmployeeM $employee, array $payload): WfhRequestM
    {
        $policy = $this->policy();
        if (! $policy['wfh_enabled']) {
            throw ValidationException::withMessages(['wfh' => 'WFH requests are currently disabled by policy.']);
        }

        $date = Carbon::parse($payload['request_date'], AttendanceRuleResolverService::TIMEZONE);
        $dayContext = $this->ruleResolver->getDayContext($employee, $date);

        $isWeekoff = (bool) ($dayContext['is_weekoff'] ?? false);
        $isHoliday = (bool) ($dayContext['is_holiday'] ?? false);
        $isWorkingDay = ! $isWeekoff && ! $isHoliday && ! (bool) ($dayContext['is_on_leave'] ?? false);
        $countsInQuota = $isWorkingDay;
        $requestType = $isWorkingDay ? 'working_day_wfh' : ($isHoliday ? 'holiday_wfh' : 'weekoff_wfh');

        if ($isHoliday && ! $policy['wfh_allow_on_holiday']) {
            throw ValidationException::withMessages(['request_date' => 'WFH is not allowed on holidays by policy.']);
        }
        if ($isWeekoff && ! $policy['wfh_allow_on_weekoff']) {
            throw ValidationException::withMessages(['request_date' => 'WFH is not allowed on weekoffs by policy.']);
        }

        $duplicate = WfhRequestM::query()
            ->where('employee_id', $employee->id)
            ->whereDate('request_date', $date->toDateString())
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['request_date' => 'A WFH request already exists for this date.']);
        }

        if ($countsInQuota) {
            $used = $this->approvedQuotaCount($employee->id, (int) $date->month, (int) $date->year);
            $limit = (int) ($policy['wfh_monthly_limit'] ?? 2);
            if ($used >= $limit) {
                throw ValidationException::withMessages([
                    'quota' => "Monthly WFH limit exceeded. You have already used {$limit} WFH days this month.",
                ]);
            }
        }

        if ($policy['wfh_requires_reason'] && empty(trim((string) ($payload['reason'] ?? '')))) {
            throw ValidationException::withMessages(['reason' => 'Reason is required by WFH policy.']);
        }

        return WfhRequestM::create([
            'employee_id' => $employee->id,
            'request_date' => $date->toDateString(),
            'request_type' => $requestType,
            'reason_category' => $payload['reason_category'] ?? 'normal',
            'reason' => $payload['reason'] ?? null,
            'status' => 'pending',
            'counts_in_monthly_quota' => $countsInQuota,
            'payroll_impact' => 'none',
            'remarks' => $payload['remarks'] ?? null,
        ]);
    }

    public function assignCompanyWfh(array $payload, int $actorId, string $source): array
    {
        $policy = $this->policy();
        if (! $policy['wfh_enabled']) {
            throw ValidationException::withMessages(['wfh' => 'WFH requests are currently disabled by policy.']);
        }

        $from = Carbon::parse($payload['date_from'], AttendanceRuleResolverService::TIMEZONE)->startOfDay();
        $to = Carbon::parse($payload['date_to'], AttendanceRuleResolverService::TIMEZONE)->startOfDay();
        if ($to->lt($from)) {
            throw ValidationException::withMessages(['date_to' => 'Date To must be after or equal to Date From.']);
        }

        $employeeIds = $this->resolveTargetEmployees($payload);
        if (empty($employeeIds)) {
            throw ValidationException::withMessages(['target' => 'No active employees found for selected assignment scope.']);
        }

        $reasonCategory = (string) ($payload['reason_category'] ?? 'company_assigned');
        $payrollImpact = (string) ($payload['payroll_impact'] ?? 'none');
        $countsInMonthlyQuota = (bool) ($payload['counts_in_monthly_quota'] ?? false);
        $workReportRequired = (bool) ($payload['work_report_required'] ?? true);
        $requestType = 'company_assigned_wfh';

        $created = 0;
        $skipped = 0;

        foreach ($employeeIds as $employeeId) {
            $employee = EmployeeM::find($employeeId);
            if (! $employee || ! (bool) ($employee->is_active ?? true)) {
                continue;
            }

            $period = CarbonPeriod::create($from, $to);
            foreach ($period as $date) {
                $exists = WfhRequestM::query()
                    ->where('employee_id', $employee->id)
                    ->whereDate('request_date', $date->toDateString())
                    ->whereNotIn('status', ['rejected', 'cancelled'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $remarks = [
                    'source' => $source,
                    'work_report_required' => $workReportRequired,
                    'assigned_by_user_id' => $actorId,
                ];

                $createPayload = [
                    'employee_id' => $employee->id,
                    'request_date' => $date->toDateString(),
                    'request_type' => $requestType,
                    'reason_category' => $reasonCategory,
                    'reason' => $payload['reason'] ?? 'Company assigned WFH',
                    'status' => 'approved',
                    'counts_in_monthly_quota' => $countsInMonthlyQuota,
                    'payroll_impact' => $payrollImpact,
                    'remarks' => json_encode($remarks),
                    'hr_approved_by' => $actorId,
                    'hr_approved_at' => now(),
                ];
                if (Schema::hasColumn('wfh_requests', 'assigned_by')) {
                    $createPayload['assigned_by'] = $actorId;
                }
                if (Schema::hasColumn('wfh_requests', 'assigned_at')) {
                    $createPayload['assigned_at'] = now();
                }

                $record = WfhRequestM::create($createPayload);

                if ($source === 'manager_assigned') {
                    $record->manager_approved_by = $actorId;
                    $record->manager_approved_at = now();
                    $record->save();
                }

                $created++;
            }
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    public function approve(WfhRequestM $request, int $actorId): WfhRequestM
    {
        $policy = $this->policy();
        if ($request->status === 'rejected' || $request->status === 'cancelled') {
            throw ValidationException::withMessages(['status' => 'Cancelled or rejected request cannot be approved.']);
        }

        $requiresManager = (bool) $policy['wfh_requires_manager_approval'];
        $requiresHr = (bool) $policy['wfh_requires_hr_approval'];
        if ($requiresManager && ! $request->manager_approved_at) {
            $request->status = $requiresHr ? 'manager_approved' : 'approved';
            $request->manager_approved_by = $actorId;
            $request->manager_approved_at = now();
            $request->save();
            return $request->fresh();
        }

        if ($requiresHr && ! $request->hr_approved_at) {
            $request->status = 'approved';
            $request->hr_approved_by = $actorId;
            $request->hr_approved_at = now();
            $request->save();
            return $request->fresh();
        }

        $request->status = 'approved';
        $request->save();
        return $request->fresh();
    }

    public function reject(WfhRequestM $request, int $actorId, string $reason): WfhRequestM
    {
        $request->update([
            'status' => 'rejected',
            'rejected_by' => $actorId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $request->fresh();
    }

    public function cancel(WfhRequestM $request): WfhRequestM
    {
        if (! in_array($request->status, ['pending', 'manager_approved'], true)) {
            throw ValidationException::withMessages(['status' => 'Only pending WFH request can be cancelled.']);
        }
        $request->update(['status' => 'cancelled']);
        return $request->fresh();
    }

    public function approvedForDate(int $employeeId, string $date): ?WfhRequestM
    {
        return WfhRequestM::query()
            ->where('employee_id', $employeeId)
            ->whereDate('request_date', $date)
            ->where('status', 'approved')
            ->first();
    }

    public function applyLwpConversionIfRequired(AttendanceM $attendance): void
    {
        // Auto-conversion is intentionally disabled.
        // WFH -> LWP now requires explicit HR/Admin/Manager review action.
        return;
    }

    public function markAsLwp(WfhRequestM $request, int $actorId, string $lwpReason, ?string $remarks = null): WfhRequestM
    {
        if ($request->status === 'rejected' || $request->status === 'cancelled') {
            throw ValidationException::withMessages(['status' => 'Rejected or cancelled request cannot be marked as LWP.']);
        }

        DB::transaction(function () use ($request, $actorId, $lwpReason, $remarks) {
            $attendance = AttendanceM::query()
                ->where('employee_id', $request->employee_id)
                ->whereDate('attendance_date', (string) $request->request_date)
                ->first();

            if (! $attendance) {
                $attendance = new AttendanceM();
                $attendance->employee_id = $request->employee_id;
                $attendance->attendance_date = (string) $request->request_date;
                $attendance->work_mode = 'wfh';
            }

            $updates = [
                'attendance_status' => 'lwp',
                'is_lwp' => true,
                'lwp_reason' => $lwpReason,
                'remarks' => trim((string) (($attendance->remarks ?? '') . ' WFH marked as LWP by reviewer.')),
            ];

            if (Schema::hasColumn('attendances', 'payroll_processed')) {
                $updates['payroll_processed'] = false;
                $updates['payroll_processed_at'] = null;
            }

            $attendance->fill($updates);
            $attendance->save();

            $existingRemarks = trim((string) ($request->remarks ?? ''));
            $manualRemark = 'Manual LWP by user #' . $actorId . ': ' . $lwpReason;
            if ($remarks) {
                $manualRemark .= ' | ' . $remarks;
            }

            $request->payroll_impact = 'lwp';
            if (Schema::hasColumn('wfh_requests', 'lwp_reason')) {
                $request->lwp_reason = $lwpReason;
            }
            $request->remarks = trim($existingRemarks . ($existingRemarks !== '' ? ' | ' : '') . $manualRemark);
            $request->save();
        });

        return $request->fresh();
    }

    private function approvedQuotaCount(int $employeeId, int $month, int $year): int
    {
        return (int) WfhRequestM::query()
            ->where('employee_id', $employeeId)
            ->whereMonth('request_date', $month)
            ->whereYear('request_date', $year)
            ->where('status', 'approved')
            ->where('counts_in_monthly_quota', true)
            ->count();
    }

    private function approvedNonQuotaCount(int $employeeId, int $month, int $year): int
    {
        return (int) WfhRequestM::query()
            ->where('employee_id', $employeeId)
            ->whereMonth('request_date', $month)
            ->whereYear('request_date', $year)
            ->where('status', 'approved')
            ->where('counts_in_monthly_quota', false)
            ->count();
    }

    private function resolveTargetEmployees(array $payload): array
    {
        $scope = (string) ($payload['assignment_scope'] ?? 'single');
        $query = EmployeeM::query()->where('is_active', 1);

        return match ($scope) {
            'single' => ! empty($payload['employee_id']) ? [(int) $payload['employee_id']] : [],
            'multiple' => collect($payload['employee_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all(),
            'department' => ! empty($payload['department_id'])
                ? $query->where('department_id', (int) $payload['department_id'])->pluck('id')->map(fn ($id) => (int) $id)->all()
                : [],
            'designation' => ! empty($payload['designation_id'])
                ? $query->where('designation_id', (int) $payload['designation_id'])->pluck('id')->map(fn ($id) => (int) $id)->all()
                : [],
            'all' => $query->pluck('id')->map(fn ($id) => (int) $id)->all(),
            default => [],
        };
    }
}
