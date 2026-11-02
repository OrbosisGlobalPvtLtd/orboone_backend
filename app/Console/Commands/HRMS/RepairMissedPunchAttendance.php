<?php

namespace App\Console\Commands\HRMS;

use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Attendance\AttendanceViolationM;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Services\HRMS\Attendance\AttendanceRuleResolverService;
use App\Services\HRMS\Attendance\AttendanceService;
use App\Services\HRMS\Attendance\PayrollAttendanceSummaryService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RepairMissedPunchAttendance extends Command
{
    protected $signature = 'hrms:repair-missed-punches 
                            {--month= : Target month (1-12, defaults to current month)} 
                            {--year= : Target year (defaults to current year)} 
                            {--employee-id= : Optional single employee ID to repair} 
                            {--dry-run : Preview changes without applying them}';

    protected $description = 'Recalculates and repairs historical missed punch attendance statuses chronologically so only 3rd+ missed punches are LWP, while 1st & 2nd remain warnings without deduction.';

    public function handle(
        AttendanceService $attendanceService,
        AttendanceRuleResolverService $ruleResolver,
        PayrollAttendanceSummaryService $payrollSummaryService
    ): int {
        $timezone = $attendanceService->attendanceTimezone();
        $now = Carbon::now($timezone);

        $month = (int) ($this->option('month') ?: $now->month);
        $year = (int) ($this->option('year') ?: $now->year);
        $empId = $this->option('employee-id');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Starting Missed Punch Recalculation for Month: {$month}/{$year}" . ($dryRun ? ' (DRY RUN)' : ''));

        $missedPunchType = AttendanceType::where('code', 'missed_punch')->first();
        $lwpType = AttendanceType::where('code', 'lwp')->first();

        if (! $missedPunchType || ! $lwpType) {
            $this->error('Required attendance types (missed_punch or lwp) not found in database.');
            return self::FAILURE;
        }

        $employeesQuery = Employee::query();
        if ($empId) {
            $employeesQuery->where('id', (int) $empId);
        }
        $employees = $employeesQuery->get();

        $totalChecked = 0;
        $totalRepaired = 0;
        $affectedEmployees = [];

        foreach ($employees as $employee) {
            // Find all attendances for this employee in the month where employee punched in but did NOT punch out:
            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->whereNotNull('punch_in_time')
                ->whereNull('punch_out_time')
                ->whereNotIn('attendance_status', ['leave', 'holiday', 'week_off'])
                ->orderBy('attendance_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            if ($attendances->isEmpty()) {
                continue;
            }

            $empName = trim($employee->first_name . ' ' . $employee->last_name) ?: "Emp #{$employee->id}";
            $rank = 0;
            $empUpdatedCount = 0;

            foreach ($attendances as $attendance) {
                // If this attendance has an approved regularization, do not alter its approved status
                $hasApprovedReg = DB::table('attendance_regularizations')
                    ->where('attendance_id', $attendance->id)
                    ->where('status', 'approved')
                    ->whereNull('deleted_at')
                    ->exists();

                if ($hasApprovedReg) {
                    continue;
                }

                $totalChecked++;
                $rank++;

                $policy = $ruleResolver->resolveShiftPolicy($employee, (string) $attendance->attendance_date, $attendance->attendance_time_id);
                $allowedMissedPunches = (int) ($policy->allowed_missed_punches ?? 2);
                if ($allowedMissedPunches <= 0 && isset($policy->missed_punch_lwp_after) && (int) $policy->missed_punch_lwp_after > 1) {
                    $allowedMissedPunches = (int) $policy->missed_punch_lwp_after - 1;
                }
                if ($allowedMissedPunches <= 0) {
                    $allowedMissedPunches = 2;
                }

                $cycleSize = max(1, $allowedMissedPunches + 1);
                $cyclePos = (($rank - 1) % $cycleSize) + 1;
                $isWarning = $cyclePos <= $allowedMissedPunches;
                $expectedStatus = $isWarning ? 'missed_punch' : 'lwp';
                $expectedTypeId = $isWarning ? $missedPunchType->id : $lwpType->id;
                $expectedIsLwp = ! $isWarning;

                $currentStatus = (string) $attendance->attendance_status;
                $currentTypeId = (int) $attendance->attendance_type_id;
                $currentIsLwp = (bool) $attendance->is_lwp;

                $needsUpdate = ($currentStatus !== $expectedStatus)
                    || ($currentTypeId !== $expectedTypeId)
                    || ($currentIsLwp !== $expectedIsLwp);

                $attDateStr = Carbon::parse($attendance->attendance_date)->toDateString();

                if ($needsUpdate) {
                    $totalRepaired++;
                    $empUpdatedCount++;
                    $affectedEmployees[$employee->id] = $employee;

                    $this->line(sprintf(
                        '[%s] %s (Emp #%d) | Date: %s | AttID: %d | Rank: #%d (Cycle Pos: %d/%d) | Current: [%s, LWP: %d] => Target: [%s, LWP: %d]',
                        $dryRun ? 'WOULD REPAIR' : 'REPAIRING',
                        $empName,
                        $employee->id,
                        $attDateStr,
                        $attendance->id,
                        $rank,
                        $cyclePos,
                        $cycleSize,
                        $currentStatus,
                        $currentIsLwp ? 1 : 0,
                        $expectedStatus,
                        $expectedIsLwp ? 1 : 0
                    ));

                    if (! $dryRun) {
                        $reason = $isWarning
                            ? "Missed punch warning {$cyclePos} of {$allowedMissedPunches}. No deduction applied."
                            : "LWP applied because monthly Missed Punch violations reached policy limit. Missed Punch Count : {$cycleSize}";

                        $attendance->updateQuietly([
                            'attendance_status' => $expectedStatus,
                            'attendance_type_id' => $expectedTypeId,
                            'is_lwp' => $expectedIsLwp,
                            'lwp_reason' => $expectedIsLwp ? $reason : null,
                            'remarks' => $expectedIsLwp ? $reason : 'Missed punch-out. Regularization required.',
                            'missed_punch' => true,
                            'is_missed_punch' => true,
                            'missed_punch_reason' => $reason,
                            'pending_hr_reason' => $isWarning ? $reason : null,
                            'is_locked' => $expectedIsLwp,
                        ]);

                        // Sync or update violation record in attendance_violations
                        $violation = AttendanceViolationM::where('attendance_id', $attendance->id)
                            ->where('type', 'missed_punch')
                            ->first();

                        if ($violation) {
                            $violation->update([
                                'policy_action' => $isWarning ? 'warning' : 'lwp',
                                'status' => 'pending',
                                'resolved_at' => null,
                                'converted_to_lwp' => ! $isWarning,
                                'remarks' => $reason,
                            ]);
                        } else {
                            AttendanceViolationM::create([
                                'attendance_id' => $attendance->id,
                                'employee_id' => $employee->id,
                                'violation_date' => $attDateStr,
                                'cycle_month' => Carbon::parse($attDateStr)->format('Y-m'),
                                'type' => 'missed_punch',
                                'source' => 'system_auto',
                                'minutes' => 0,
                                'policy_action' => $isWarning ? 'warning' : 'lwp',
                                'converted_to_lwp' => ! $isWarning,
                                'remarks' => $reason,
                            ]);
                        }
                    }
                }
            }

            // If any record of employee was updated, recalculate employee's monthly payroll attendance summary
            if (! $dryRun && $empUpdatedCount > 0) {
                try {
                    $payrollSummaryService->generateForEmployee($employee, $month, $year);
                } catch (\Throwable $e) {
                    Log::warning("Could not recalculate payroll summary for Emp #{$employee->id}: " . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info(sprintf(
            'Completed! Checked: %d attendances | Repaired: %d records across %d employees | Dry run: %s',
            $totalChecked,
            $totalRepaired,
            count($affectedEmployees),
            $dryRun ? 'YES' : 'NO'
        ));

        return self::SUCCESS;
    }
}
