<?php

namespace App\Services\HRMS\Attendance;

use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\WfhRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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

    public function calculateRangeStats(EmployeeM $employee, string $fromDateStr, string $toDateStr): array
    {
        $from = Carbon::parse($fromDateStr, AttendanceRuleResolverService::TIMEZONE)->startOfDay();
        $to = Carbon::parse($toDateStr, AttendanceRuleResolverService::TIMEZONE)->startOfDay();

        if ($to->lt($from)) {
            throw ValidationException::withMessages(['date_to' => 'To Date must be on or after From Date.']);
        }

        $period = CarbonPeriod::create($from, $to);
        $totalDays = 0;
        $workingDays = 0;
        $weekoffDays = 0;
        $holidayDays = 0;
        $daysDetail = [];

        foreach ($period as $date) {
            $totalDays++;
            $dayContext = $this->ruleResolver->getDayContext($employee, $date);

            $isWeekoff = (bool) ($dayContext['is_weekoff'] ?? false);
            $isHoliday = (bool) ($dayContext['is_holiday'] ?? false);
            $isOnLeave = (bool) ($dayContext['is_on_leave'] ?? false);
            $isWorkingDay = ! $isWeekoff && ! $isHoliday && ! $isOnLeave;

            if ($isWeekoff) {
                $weekoffDays++;
            }
            if ($isHoliday) {
                $holidayDays++;
            }
            if ($isWorkingDay) {
                $workingDays++;
            }

            $daysDetail[] = [
                'date' => $date->toDateString(),
                'formatted' => $date->format('d M Y'),
                'day_name' => $date->format('l'),
                'is_working_day' => $isWorkingDay,
                'is_weekoff' => $isWeekoff,
                'is_holiday' => $isHoliday,
                'is_on_leave' => $isOnLeave,
                'holiday_name' => $dayContext['holiday_name'] ?? null,
            ];
        }

        return [
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'period_label' => $from->format('d M Y') . ' – ' . $to->format('d M Y'),
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'weekoff_days' => $weekoffDays,
            'holiday_days' => $holidayDays,
            'actual_wfh_days' => $workingDays,
            'days_detail' => $daysDetail,
        ];
    }

    public function validateRange(EmployeeM $employee, string $fromDateStr, string $toDateStr): array
    {
        $policy = $this->policy();
        if (! ($policy['wfh_enabled'] ?? true)) {
            throw ValidationException::withMessages(['wfh' => 'WFH requests are currently disabled by policy.']);
        }

        $stats = $this->calculateRangeStats($employee, $fromDateStr, $toDateStr);
        $from = Carbon::parse($fromDateStr, AttendanceRuleResolverService::TIMEZONE)->startOfDay();
        $to = Carbon::parse($toDateStr, AttendanceRuleResolverService::TIMEZONE)->startOfDay();

        // 1. Working days check
        if ($stats['working_days'] === 0 && ! ($policy['wfh_allow_on_holiday'] ?? false) && ! ($policy['wfh_allow_on_weekoff'] ?? false)) {
            throw ValidationException::withMessages([
                'request_date' => 'Selected date range contains no working days (only weekoffs/holidays).',
            ]);
        }

        // 2. Range overlap check
        $duplicate = WfhRequestM::query()
            ->where('employee_id', $employee->id)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->where(function ($q) use ($from, $to) {
                $q->where(function ($q2) use ($from, $to) {
                    $q2->whereDate('from_date', '<=', $to->toDateString())
                       ->whereDate('to_date', '>=', $from->toDateString());
                })->orWhere(function ($q3) use ($from, $to) {
                    $q3->whereNull('from_date')
                       ->whereDate('request_date', '>=', $from->toDateString())
                       ->whereDate('request_date', '<=', $to->toDateString());
                });
            })
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'request_date' => 'A WFH request already exists overlapping with the selected dates.',
            ]);
        }

        // 3. Check Leave overlap
        $period = CarbonPeriod::create($from, $to);
        foreach ($period as $date) {
            $dayContext = $this->ruleResolver->getDayContext($employee, $date);
            if ($dayContext['is_on_leave']) {
                throw ValidationException::withMessages([
                    'request_date' => "Cannot request WFH on {$date->format('d-m-Y')} because you are on approved leave.",
                ]);
            }
        }

        return $stats;
    }

    public function apply(EmployeeM $employee, array $payload): WfhRequestM
    {
        $policy = $this->policy();
        if (! $policy['wfh_enabled']) {
            throw ValidationException::withMessages(['wfh' => 'WFH requests are currently disabled by policy.']);
        }

        if ($employee->isPermanentWfh()) {
            throw ValidationException::withMessages(['wfh' => 'You are a Permanent Work From Home employee. No WFH approval is required.']);
        }

        $fromDateStr = $payload['from_date'] ?? $payload['date_from'] ?? $payload['request_date'] ?? null;
        $toDateStr = $payload['to_date'] ?? $payload['date_to'] ?? $fromDateStr;

        if (! $fromDateStr) {
            throw ValidationException::withMessages(['request_date' => 'From Date / Request Date is required.']);
        }

        $stats = $this->validateRange($employee, (string) $fromDateStr, (string) $toDateStr);

        if ($policy['wfh_requires_reason'] && empty(trim((string) ($payload['reason'] ?? '')))) {
            throw ValidationException::withMessages(['reason' => 'Reason is required by WFH policy.']);
        }

        $from = Carbon::parse($fromDateStr, AttendanceRuleResolverService::TIMEZONE)->startOfDay();
        $to = Carbon::parse($toDateStr, AttendanceRuleResolverService::TIMEZONE)->startOfDay();
        $batchId = 'WFH-' . date('YmdHis') . '-' . Str::random(6);

        $record = DB::transaction(function () use ($employee, $from, $to, $payload, $batchId, $stats) {
            $createPayload = [
                'employee_id' => $employee->id,
                'request_date' => $from->toDateString(),
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
                'batch_id' => $batchId,
                'total_days' => $stats['total_days'],
                'working_days' => $stats['working_days'],
                'weekoff_days' => $stats['weekoff_days'],
                'holiday_days' => $stats['holiday_days'],
                'actual_wfh_days' => $stats['actual_wfh_days'],
                'request_type' => 'working_day_wfh',
                'reason_category' => $payload['reason_category'] ?? 'normal',
                'reason' => $payload['reason'] ?? null,
                'status' => 'pending',
                'counts_in_monthly_quota' => true,
                'payroll_impact' => 'none',
                'remarks' => $payload['remarks'] ?? null,
            ];

            return WfhRequestM::create($createPayload);
        });

        // Single Notification to HR/Admin
        try {
            $notificationService = app(NotificationS::class);
            $rangeLabel = ($fromDateStr === $toDateStr)
                ? Carbon::parse($fromDateStr)->format('d M Y')
                : Carbon::parse($fromDateStr)->format('d M Y') . ' – ' . Carbon::parse($toDateStr)->format('d M Y');

            $notificationService->notifyHrAndSuperAdmin(
                'WFH Request Submitted',
                "{$employee->name} submitted a WFH request for {$rangeLabel} ({$stats['working_days']} working days).",
                'wfh_submitted',
                'wfh.index',
                [],
                ['employee_id' => $employee->id, 'wfh_request_id' => $record->id, 'batch_id' => $batchId]
            );
        } catch (\Throwable $e) {
            Log::warning('WFH submission notification skipped: ' . $e->getMessage());
        }

        return $record;
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

            $stats = $this->calculateRangeStats($employee, $from->toDateString(), $to->toDateString());
            $batchId = 'WFH-ASSIGN-' . date('YmdHis') . '-' . Str::random(6);

            $exists = WfhRequestM::query()
                ->where('employee_id', $employee->id)
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->where(function ($q) use ($from, $to) {
                    $q->where(function ($q2) use ($from, $to) {
                        $q2->whereDate('from_date', '<=', $to->toDateString())
                           ->whereDate('to_date', '>=', $from->toDateString());
                    })->orWhere(function ($q3) use ($from, $to) {
                        $q3->whereNull('from_date')
                           ->whereDate('request_date', '>=', $from->toDateString())
                           ->whereDate('request_date', '<=', $to->toDateString());
                    });
                })
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
                'request_date' => $from->toDateString(),
                'from_date' => $from->toDateString(),
                'to_date' => $to->toDateString(),
                'batch_id' => $batchId,
                'total_days' => $stats['total_days'],
                'working_days' => $stats['working_days'],
                'weekoff_days' => $stats['weekoff_days'],
                'holiday_days' => $stats['holiday_days'],
                'actual_wfh_days' => $stats['actual_wfh_days'],
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

        return ['created' => $created, 'skipped' => $skipped];
    }

    public function approve(WfhRequestM $request, int $actorId, ?array $partialRange = null, bool $canOverrideQuota = false): WfhRequestM
    {
        $policy = $this->policy();
        if ($request->status === 'rejected' || $request->status === 'cancelled') {
            throw ValidationException::withMessages(['status' => 'Cancelled or rejected request cannot be approved.']);
        }

        $requiresManager = (bool) ($policy['wfh_requires_manager_approval'] ?? false);
        $requiresHr = (bool) ($policy['wfh_requires_hr_approval'] ?? false);

        $employee = $request->employee ?: EmployeeM::find($request->employee_id);
        if (! $employee) {
            throw ValidationException::withMessages(['employee' => 'Employee profile not found for this WFH request.']);
        }

        $fromDate = ! empty($partialRange['approved_from_date']) ? $partialRange['approved_from_date'] : ($request->from_date ?: $request->request_date);
        $toDate = ! empty($partialRange['approved_to_date']) ? $partialRange['approved_to_date'] : ($request->to_date ?: $fromDate);
        $fromDateCarbon = Carbon::parse($fromDate)->startOfDay();
        $toDateCarbon = Carbon::parse($toDate)->startOfDay();

        if ($toDateCarbon->lt($fromDateCarbon)) {
            throw ValidationException::withMessages(['approved_to_date' => 'Approved To Date must be on or after Approved From Date.']);
        }

        $stats = $this->calculateRangeStats($employee, $fromDateCarbon->toDateString(), $toDateCarbon->toDateString());
        $requestedWorkingDays = $stats['working_days'];

        $isQuotaOverride = false;
        if ((bool) ($request->counts_in_monthly_quota ?? true)) {
            $month = (int) $fromDateCarbon->month;
            $year = (int) $fromDateCarbon->year;
            $monthlyQuota = (int) ($policy['wfh_monthly_limit'] ?? 2);
            $alreadyApproved = $this->approvedQuotaCountExcluding($request->employee_id, $month, $year, [$request->id]);

            if (($alreadyApproved + $requestedWorkingDays) > $monthlyQuota) {
                $monthName = $fromDateCarbon->format('F Y');
                if (! $canOverrideQuota) {
                    throw ValidationException::withMessages([
                        'quota' => "Cannot approve WFH request. Monthly WFH limit exceeded for {$monthName}. Limit: {$monthlyQuota} days, approved: {$alreadyApproved} days, requested: {$requestedWorkingDays} working days.",
                    ]);
                }
                $isQuotaOverride = true;
            }
        }

        DB::transaction(function () use ($request, $employee, $actorId, $fromDateCarbon, $toDateCarbon, $stats, $isQuotaOverride) {
            $request->from_date = $fromDateCarbon->toDateString();
            $request->to_date = $toDateCarbon->toDateString();
            $request->request_date = $fromDateCarbon->toDateString();
            $request->total_days = $stats['total_days'];
            $request->working_days = $stats['working_days'];
            $request->weekoff_days = $stats['weekoff_days'];
            $request->holiday_days = $stats['holiday_days'];
            $request->actual_wfh_days = $stats['actual_wfh_days'];

            $request->status = 'approved';
            $request->hr_approved_by = $actorId;
            $request->hr_approved_at = now();
            if (! $request->manager_approved_at) {
                $request->manager_approved_by = $actorId;
                $request->manager_approved_at = now();
            }

            if ($isQuotaOverride) {
                $overrideAudit = 'Approved with Quota Override | Override By: User #' . $actorId . ' | Override Date: ' . now()->format('d-m-Y H:i:s');
                $existingRemarks = trim((string) ($request->remarks ?? ''));
                $request->remarks = trim($existingRemarks . ($existingRemarks !== '' ? ' | ' : '') . $overrideAudit);
            }

            $request->save();
        });

        // Single Notification to Employee
        try {
            $notificationService = app(NotificationS::class);
            $user = $employee->user;
            if ($user) {
                $rangeLabel = ($request->from_date && $request->to_date)
                    ? $request->from_date->format('d M Y') . ' – ' . $request->to_date->format('d M Y')
                    : Carbon::parse($request->request_date)->format('d M Y');

                $notificationService->notifyEmployee(
                    'WFH Request Approved',
                    "Your Work From Home request has been approved.\nYou can mark attendance remotely during the approved period.",
                    'wfh_approved',
                    'my-wfh.index',
                    [],
                    ['employee_id' => $request->employee_id, 'wfh_request_id' => $request->id],
                    $user->id
                );
            }
        } catch (\Throwable $e) {
            Log::warning('WFH approval notification skipped: ' . $e->getMessage());
        }

        return $request->fresh();
    }

    public function generateAttendanceRecordsForApprovedWfh(WfhRequestM $request): void
    {
        // Attendance records are no longer automatically created during WFH approval.
    }

    public function reject(WfhRequestM $request, int $actorId, string $reason): WfhRequestM
    {
        $request->update([
            'status' => 'rejected',
            'rejected_by' => $actorId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Single Notification to Employee
        try {
            $notificationService = app(NotificationS::class);
            $user = $request->employee?->user;
            if ($user) {
                $rangeLabel = ($request->from_date && $request->to_date)
                    ? $request->from_date->format('d M Y') . ' – ' . $request->to_date->format('d M Y')
                    : Carbon::parse($request->request_date)->format('d M Y');

                $notificationService->notifyEmployee(
                    'WFH Request Rejected',
                    "Your Work From Home request has been rejected.\nPlease contact HR for more details.",
                    'wfh_rejected',
                    'my-wfh.index',
                    [],
                    ['employee_id' => $request->employee_id, 'wfh_request_id' => $request->id],
                    $user->id
                );
            }
        } catch (\Throwable $e) {
            Log::warning('WFH rejection notification skipped: ' . $e->getMessage());
        }

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
            ->where('status', 'approved')
            ->where(function ($q) use ($date) {
                $q->where(function ($q2) use ($date) {
                    $q2->whereDate('from_date', '<=', $date)
                       ->whereDate('to_date', '>=', $date);
                })->orWhere(function ($q3) use ($date) {
                    $q3->whereNull('from_date')
                       ->whereDate('request_date', $date);
                });
            })
            ->first();
    }

    public function applyLwpConversionIfRequired(AttendanceM $attendance): void
    {
        return;
    }

    public function markAsLwp(WfhRequestM $request, int $actorId, string $lwpReason, ?string $remarks = null): WfhRequestM
    {
        if ($request->status === 'rejected' || $request->status === 'cancelled') {
            throw ValidationException::withMessages(['status' => 'Rejected or cancelled request cannot be marked as LWP.']);
        }

        DB::transaction(function () use ($request, $actorId, $lwpReason, $remarks) {
            $from = Carbon::parse($request->from_date ?: $request->request_date)->startOfDay();
            $to = Carbon::parse($request->to_date ?: $from)->startOfDay();
            $period = CarbonPeriod::create($from, $to);

            foreach ($period as $date) {
                $attendance = AttendanceM::query()
                    ->where('employee_id', $request->employee_id)
                    ->whereDate('attendance_date', $date->toDateString())
                    ->first();

                if (! $attendance) {
                    $attendance = new AttendanceM();
                    $attendance->employee_id = $request->employee_id;
                    $attendance->user_id = $request->employee?->user_id;
                    $attendance->attendance_date = $date->toDateString();
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
            }

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

    public function approvedQuotaCountForEmployee(int $employeeId, int $month, int $year, array $excludeIds = []): int
    {
        return $this->approvedQuotaCountExcluding($employeeId, $month, $year, $excludeIds);
    }

    private function approvedQuotaCountExcluding(int $employeeId, int $month, int $year, array $excludeIds = []): int
    {
        return (int) WfhRequestM::query()
            ->where('employee_id', $employeeId)
            ->where(function ($q) use ($month, $year) {
                $q->where(function ($q2) use ($month, $year) {
                    $q2->whereMonth('from_date', $month)->whereYear('from_date', $year);
                })->orWhere(function ($q3) use ($month, $year) {
                    $q3->whereNull('from_date')->whereMonth('request_date', $month)->whereYear('request_date', $year);
                });
            })
            ->where('status', 'approved')
            ->where('counts_in_monthly_quota', true)
            ->when(! empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->sum('working_days');
    }

    private function approvedNonQuotaCount(int $employeeId, int $month, int $year): int
    {
        return (int) WfhRequestM::query()
            ->where('employee_id', $employeeId)
            ->where(function ($q) use ($month, $year) {
                $q->where(function ($q2) use ($month, $year) {
                    $q2->whereMonth('from_date', $month)->whereYear('from_date', $year);
                })->orWhere(function ($q3) use ($month, $year) {
                    $q3->whereNull('from_date')->whereMonth('request_date', $month)->whereYear('request_date', $year);
                });
            })
            ->where('status', 'approved')
            ->where('counts_in_monthly_quota', false)
            ->sum('working_days');
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
