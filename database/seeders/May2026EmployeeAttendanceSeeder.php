<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class May2026EmployeeAttendanceSeeder extends Seeder
{
    private const TIMEZONE = 'Asia/Kolkata';
    private const REMARKS = 'May 2026 HRMS test seed';
    private const EMPLOYEE_ID = 44;
    private const USER_ID = 52;

    public function run()
    {
        $employeeId = self::EMPLOYEE_ID;
        $userId = self::USER_ID;
        $startDateStr = '2026-05-01';
        $endDateStr = '2026-05-31';

        $this->command->info("Starting May 2026 Employee Attendance Seeder for employee_id: $employeeId...");

        DB::beginTransaction();
        try {
            // STEP 3: CLEANUP BEFORE INSERT
            $this->command->info("Cleaning up existing May 2026 test data for employee $employeeId...");

            // 1. Get attendance IDs for May 2026
            $attendanceIds = DB::table('attendances')
                ->where('employee_id', $employeeId)
                ->whereBetween('attendance_date', [$startDateStr, $endDateStr])
                ->pluck('id')
                ->toArray();

            // 2. Delete attendance_work_logs
            if (!empty($attendanceIds)) {
                DB::table('attendance_work_logs')->whereIn('attendance_id', $attendanceIds)->delete();
            }
            DB::table('attendance_work_logs')
                ->where('employee_id', $employeeId)
                ->whereBetween('work_date', [$startDateStr, $endDateStr])
                ->delete();

            // 3. Delete attendance_regularizations
            if (!empty($attendanceIds)) {
                DB::table('attendance_regularizations')->whereIn('attendance_id', $attendanceIds)->delete();
            }
            DB::table('attendance_regularizations')
                ->where('employee_id', $employeeId)
                ->where(function ($q) use ($startDateStr, $endDateStr) {
                    $q->whereBetween('existing_punch_in', [$startDateStr . ' 00:00:00', $endDateStr . ' 23:59:59'])
                      ->orWhereBetween('requested_punch_in', [$startDateStr . ' 00:00:00', $endDateStr . ' 23:59:59']);
                })
                ->delete();

            // 4. Delete attendance_violations
            if (Schema::hasTable('attendance_violations')) {
                DB::table('attendance_violations')
                    ->where('employee_id', $employeeId)
                    ->whereBetween('violation_date', [$startDateStr, $endDateStr])
                    ->delete();
            }

            // 5. Delete wfh_requests
            DB::table('wfh_requests')
                ->where('employee_id', $employeeId)
                ->whereBetween('request_date', [$startDateStr, $endDateStr])
                ->delete();

            // 6. Delete holiday_work_requests
            DB::table('holiday_work_requests')
                ->where('employee_id', $employeeId)
                ->whereBetween('worked_date', [$startDateStr, $endDateStr])
                ->delete();

            // 7. Delete leave requests and leave request dates
            $leaveRequestIds = DB::table('leave_requests')
                ->where('employee_id', $employeeId)
                ->where(function ($q) use ($startDateStr, $endDateStr) {
                    $q->whereBetween('start_date', [$startDateStr, $endDateStr])
                      ->orWhereBetween('end_date', [$startDateStr, $endDateStr]);
                })
                ->pluck('id')
                ->toArray();

            if (!empty($leaveRequestIds)) {
                DB::table('leave_request_dates')->whereIn('leave_request_id', $leaveRequestIds)->delete();
                DB::table('leave_requests')->whereIn('id', $leaveRequestIds)->delete();
            }

            // 8. Delete comp_offs
            DB::table('comp_offs')
                ->where('employee_id', $employeeId)
                ->whereBetween('worked_date', [$startDateStr, $endDateStr])
                ->delete();

            // 9. Delete monthly_attendance_summaries
            DB::table('monthly_attendance_summaries')
                ->where('employee_id', $employeeId)
                ->where('month', 5)
                ->where('year', 2026)
                ->delete();

            // 10. Delete payroll_attendance_impacts
            if (Schema::hasTable('payroll_attendance_impacts')) {
                DB::table('payroll_attendance_impacts')
                    ->where('employee_id', $employeeId)
                    ->where('month', 5)
                    ->where('year', 2026)
                    ->delete();
            }

            // 11. Delete attendances
            DB::table('attendances')
                ->where('employee_id', $employeeId)
                ->whereBetween('attendance_date', [$startDateStr, $endDateStr])
                ->delete();

            $this->command->info("Cleanup completed.");

            // STEP 11: PAYROLL TEST READINESS - Ensure leave allocations and salary structures exist
            $this->command->info("Configuring Leave Allocations and Salary Structure...");
            
            // Adjust leave allocations to ensure enough balance
            DB::table('leave_allocations')->updateOrInsert(
                ['employee_id' => $employeeId, 'year' => 2026],
                [
                    'policy_id' => 1,
                    'employment_stage' => 'internship',
                    'allocation_from_date' => '2026-03-01',
                    'allocation_to_date' => '2026-12-31',
                    'total_allocated' => 30.00,
                    'paid_allocated' => 10.00,
                    'sick_allocated' => 10.00,
                    'comp_off_allocated' => 10.00,
                    'total_used' => 0.00,
                    'paid_used' => 0.00,
                    'sick_used' => 0.00,
                    'comp_off_used' => 0.00,
                    'lwp_used' => 0.00,
                    'total_remaining' => 30.00,
                    'paid_remaining' => 10.00,
                    'sick_remaining' => 10.00,
                    'comp_off_remaining' => 10.00,
                    'allocation_reason' => 'May 2026 HRMS test seed allocation',
                    'created_by_user_id' => 1,
                    'is_locked' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );

            // Create salary structure with 18000 gross salary
            DB::table('enterprise_salary_structures')->updateOrInsert(
                ['employee_id' => $employeeId, 'status' => 'active'],
                [
                    'effective_from' => '2026-03-01',
                    'annual_ctc' => 216000.00,
                    'monthly_ctc' => 18000.00,
                    'basic_annual' => 108000.00,
                    'basic_monthly' => 9000.00,
                    'hra_annual' => 54000.00,
                    'hra_monthly' => 4500.00,
                    'special_allowance_annual' => 54000.00,
                    'special_allowance_monthly' => 4500.00,
                    'professional_tax_monthly' => 200.00,
                    'tds_annual' => 0.00,
                    'tds_monthly' => 0.00,
                    'other_deduction_monthly' => 0.00,
                    'source' => 'test_seed',
                    'stage' => 'internship',
                    'sync_reference_type' => 'employee_onboarding',
                    'sync_reference_id' => $employeeId,
                    'revision_reason' => 'May 2026 HRMS test seed structure',
                    'created_by_user_id' => 1,
                    'approved_by_user_id' => 1,
                    'approved_at' => Carbon::now(),
                    'remarks' => self::REMARKS,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );

            // Fetch dynamic type IDs
            $typeIds = DB::table('attendance_types')->pluck('id', 'code')->toArray();

            // STEP 9: LEAVE REQUESTS
            $this->command->info("Creating leave requests...");
            
            // Paid Leave: 2026-05-08
            $paidLeaveId = DB::table('leave_requests')->insertGetId([
                'employee_id' => $employeeId,
                'user_id' => $userId,
                'leave_type_id' => 1, // Paid Leave
                'start_date' => '2026-05-08',
                'end_date' => '2026-05-08',
                'requested_days' => 1.00,
                'deducted_days' => 1.00,
                'is_half_day' => 0,
                'reason' => 'Family event paid leave',
                'status' => 'approved',
                'manager_approved_by' => 52,
                'manager_approved_at' => '2026-05-07 10:00:00',
                'approved_by_user_id' => 52,
                'approved_at' => '2026-05-07 10:00:00',
                'paid_days' => 1.00,
                'sick_days' => 0.00,
                'comp_off_days' => 0.00,
                'lwp_days' => 0.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'attendance_synced' => 1
            ]);

            DB::table('leave_request_dates')->insert([
                'leave_request_id' => $paidLeaveId,
                'employee_id' => $employeeId,
                'leave_date' => '2026-05-08',
                'day_name' => 'Friday',
                'is_working_day' => 1,
                'is_weekoff' => 0,
                'is_holiday' => 0,
                'is_sandwich_day' => 0,
                'deduct_as_leave' => 1,
                'leave_type_code' => 'paid_leave',
                'paid_day' => 1.00,
                'sick_day' => 0.00,
                'comp_off_day' => 0.00,
                'lwp_day' => 0.00,
                'remarks' => self::REMARKS,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // Sick Leave: 2026-05-26
            $sickLeaveId = DB::table('leave_requests')->insertGetId([
                'employee_id' => $employeeId,
                'user_id' => $userId,
                'leave_type_id' => 2, // Sick Leave
                'start_date' => '2026-05-26',
                'end_date' => '2026-05-26',
                'requested_days' => 1.00,
                'deducted_days' => 1.00,
                'is_half_day' => 0,
                'reason' => 'Sick leave due to viral fever',
                'status' => 'approved',
                'manager_approved_by' => 52,
                'manager_approved_at' => '2026-05-25 10:00:00',
                'approved_by_user_id' => 52,
                'approved_at' => '2026-05-25 10:00:00',
                'paid_days' => 0.00,
                'sick_days' => 1.00,
                'comp_off_days' => 0.00,
                'lwp_days' => 0.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'attendance_synced' => 1
            ]);

            DB::table('leave_request_dates')->insert([
                'leave_request_id' => $sickLeaveId,
                'employee_id' => $employeeId,
                'leave_date' => '2026-05-26',
                'day_name' => 'Tuesday',
                'is_working_day' => 1,
                'is_weekoff' => 0,
                'is_holiday' => 0,
                'is_sandwich_day' => 0,
                'deduct_as_leave' => 1,
                'leave_type_code' => 'sick_leave',
                'paid_day' => 0.00,
                'sick_day' => 1.00,
                'comp_off_day' => 0.00,
                'lwp_day' => 0.00,
                'remarks' => self::REMARKS,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // STEP 7: WFH REQUESTS
            $this->command->info("Creating WFH requests...");

            $wfhData = [
                [
                    'request_date' => '2026-05-06',
                    'request_type' => 'working_day_wfh',
                    'reason_category' => 'normal',
                    'reason' => 'Client work from home',
                    'status' => 'approved',
                    'counts_in_monthly_quota' => 1,
                    'payroll_impact' => 'none',
                    'lwp_reason' => null
                ],
                [
                    'request_date' => '2026-05-20',
                    'request_type' => 'working_day_wfh',
                    'reason_category' => 'internet_issue',
                    'reason' => 'Broadband down in locality',
                    'status' => 'approved',
                    'counts_in_monthly_quota' => 1,
                    'payroll_impact' => 'lwp',
                    'lwp_reason' => 'WFH marked LWP manually for test'
                ],
                [
                    'request_date' => '2026-05-21',
                    'request_type' => 'working_day_wfh',
                    'reason_category' => 'normal',
                    'reason' => 'Normal WFH quota day 2',
                    'status' => 'approved',
                    'counts_in_monthly_quota' => 1,
                    'payroll_impact' => 'none',
                    'lwp_reason' => null
                ],
                [
                    'request_date' => '2026-05-23',
                    'request_type' => 'weekoff_wfh',
                    'reason_category' => 'normal',
                    'reason' => 'Weekoff weekend deployment support',
                    'status' => 'approved',
                    'counts_in_monthly_quota' => 0,
                    'payroll_impact' => 'none',
                    'lwp_reason' => null
                ],
                [
                    'request_date' => '2026-05-27',
                    'request_type' => 'working_day_wfh',
                    'reason_category' => 'normal',
                    'reason' => 'Working day WFH over-limit test',
                    'status' => 'rejected',
                    'counts_in_monthly_quota' => 1,
                    'payroll_impact' => 'none',
                    'rejection_reason' => 'Monthly WFH limit exceeded test',
                    'lwp_reason' => null
                ],
                [
                    'request_date' => '2026-05-30',
                    'request_type' => 'weekoff_wfh',
                    'reason_category' => 'normal',
                    'reason' => 'Weekend config checks WFH',
                    'status' => 'approved',
                    'counts_in_monthly_quota' => 0,
                    'payroll_impact' => 'none',
                    'lwp_reason' => null
                ]
            ];

            foreach ($wfhData as $wfh) {
                DB::table('wfh_requests')->insert(array_merge($wfh, [
                    'employee_id' => $employeeId,
                    'manager_approved_by' => 52,
                    'manager_approved_at' => Carbon::parse($wfh['request_date'] . ' 10:00:00', self::TIMEZONE),
                    'remarks' => self::REMARKS,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]));
            }

            // STEP 10: COMP-OFF CREDITS
            $this->command->info("Creating comp-off credits...");
            $compOffId = DB::table('comp_offs')->insertGetId([
                'employee_id' => $employeeId,
                'worked_date' => '2026-05-23',
                'earned_days' => 1.00,
                'expiry_date' => '2026-06-30',
                'status' => 'available',
                'approved_by_user_id' => 52,
                'approved_at' => '2026-05-23 10:00:00',
                'remarks' => self::REMARKS,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // STEP 5: ATTENDANCE RECORDS DATE-WISE
            $this->command->info("Generating attendance records...");

            $days = [
                '2026-05-01' => [
                    'type' => 'present', 'mode' => 'wfo', 'status' => 'present',
                    'in' => '09:55:00', 'out' => '19:05:00', 'gross' => 550, 'net' => 490
                ],
                '2026-05-02' => [
                    'type' => 'present', 'mode' => 'wfo', 'status' => 'present',
                    'in' => '10:05:00', 'out' => '19:00:00', 'gross' => 535, 'net' => 475
                ],
                '2026-05-03' => [
                    'type' => 'week_off', 'mode' => null, 'status' => 'week_off',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0
                ],
                '2026-05-04' => [
                    'type' => 'late', 'mode' => 'wfo', 'status' => 'present',
                    'in' => '11:07:00', 'out' => '19:20:00', 'gross' => 493, 'net' => 433,
                    'is_late' => true, 'late_minutes' => 62
                ],
                '2026-05-05' => [
                    'type' => 'early_leave', 'mode' => 'wfo', 'status' => 'present',
                    'in' => '10:00:00', 'out' => '18:10:00', 'gross' => 490, 'net' => 430,
                    'is_early_out' => true, 'early_out_minutes' => 50
                ],
                '2026-05-06' => [
                    'type' => 'present', 'mode' => 'wfh', 'status' => 'present',
                    'in' => '10:02:00', 'out' => '19:00:00', 'gross' => 538, 'net' => 478
                ],
                '2026-05-07' => [
                    'type' => 'half_day', 'mode' => 'wfo', 'status' => 'half_day',
                    'in' => '10:10:00', 'out' => '15:00:00', 'gross' => 290, 'net' => 230,
                    'is_half_day' => true, 'half_day_reason' => 'Low working hours (230 mins)'
                ],
                '2026-05-08' => [
                    'type' => 'leave', 'mode' => null, 'status' => 'leave',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0,
                    'leave_request_id' => $paidLeaveId
                ],
                '2026-05-09' => [
                    'type' => 'week_off', 'mode' => null, 'status' => 'week_off',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0
                ],
                '2026-05-10' => [
                    'type' => 'week_off', 'mode' => null, 'status' => 'week_off',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0
                ],
                '2026-05-11' => [
                    'type' => 'absent', 'mode' => null, 'status' => 'absent',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0
                ],
                '2026-05-12' => [
                    'type' => 'present', 'mode' => 'wfo', 'status' => 'present',
                    'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480,
                    'missed_punch' => false, 'is_missed_punch' => false, 'missed_punch_reason' => null
                ],
                '2026-05-13' => [
                    'type' => 'present', 'mode' => 'wfo', 'status' => 'present',
                    'in' => '10:05:00', 'out' => '19:05:00', 'gross' => 540, 'net' => 480,
                    'missed_punch' => false, 'is_missed_punch' => false, 'missed_punch_reason' => null
                ],
                '2026-05-14' => [
                    'type' => 'lwp', 'mode' => 'wfo', 'status' => 'lwp',
                    'in' => '10:00:00', 'out' => null, 'gross' => 0, 'net' => 0,
                    'missed_punch' => false, 'is_missed_punch' => false, 'missed_punch_reason' => null,
                    'is_lwp' => true, 'lwp_reason' => 'Missed punch limit exceeded (3rd violation)'
                ],
                '2026-05-15' => [
                    'type' => 'late', 'mode' => 'wfo', 'status' => 'present',
                    'in' => '11:06:00', 'out' => '18:20:00', 'gross' => 434, 'net' => 374,
                    'is_late' => true, 'late_minutes' => 61
                ],
                '2026-05-16' => [
                    'type' => 'present', 'mode' => 'wfo', 'status' => 'present',
                    'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480
                ],
                '2026-05-17' => [
                    'type' => 'week_off', 'mode' => null, 'status' => 'week_off',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0
                ],
                '2026-05-18' => [
                    'type' => 'absent', 'mode' => null, 'status' => 'absent',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0,
                    'is_blocked' => false, 'is_punch_blocked' => false, 'block_reason' => null,
                    'auto_blocked_at' => null, 'auto_block_reason' => null
                ],
                '2026-05-19' => [
                    'type' => 'absent', 'mode' => null, 'status' => 'absent',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0
                ],
                '2026-05-20' => [
                    'type' => 'lwp', 'mode' => 'wfh', 'status' => 'lwp',
                    'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480,
                    'is_lwp' => true, 'lwp_reason' => 'WFH marked LWP manually for test'
                ],
                '2026-05-21' => [
                    'type' => 'present', 'mode' => 'wfh', 'status' => 'present',
                    'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480
                ],
                '2026-05-22' => [
                    'type' => 'half_day', 'mode' => 'wfo', 'status' => 'half_day',
                    'in' => '11:06:00', 'out' => '18:20:00', 'gross' => 434, 'net' => 374,
                    'is_late' => true, 'late_minutes' => 66,
                    'is_early_out' => true, 'early_out_minutes' => 40,
                    'is_half_day' => true, 'half_day_reason' => 'Low hours & combined violation'
                ],
                '2026-05-23' => [
                    'type' => 'present', 'mode' => 'wfh', 'status' => 'present',
                    'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480,
                    'comp_off_id' => $compOffId
                ],
                '2026-05-24' => [
                    'type' => 'week_off', 'mode' => null, 'status' => 'week_off',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0
                ],
                '2026-05-25' => [
                    'type' => 'present', 'mode' => 'wfo', 'status' => 'present',
                    'in' => '09:50:00', 'out' => '18:55:00', 'gross' => 545, 'net' => 485
                ],
                '2026-05-26' => [
                    'type' => 'leave', 'mode' => null, 'status' => 'leave',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0,
                    'leave_request_id' => $sickLeaveId
                ],
                '2026-05-27' => [
                    'type' => 'present', 'mode' => 'wfo', 'status' => 'present',
                    'in' => '09:58:00', 'out' => '19:02:00', 'gross' => 544, 'net' => 484
                ],
                '2026-05-28' => [
                    'type' => 'lwp', 'mode' => null, 'status' => 'lwp',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0,
                    'is_lwp' => true, 'lwp_reason' => 'Direct LWP marked'
                ],
                '2026-05-29' => [
                    'type' => 'absent', 'mode' => null, 'status' => 'absent',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0,
                    'is_blocked' => false, 'is_punch_blocked' => false, 'block_reason' => null,
                    'auto_blocked_at' => null, 'auto_block_reason' => null
                ],
                '2026-05-30' => [
                    'type' => 'week_off', 'mode' => null, 'status' => 'week_off',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0
                ],
                '2026-05-31' => [
                    'type' => 'week_off', 'mode' => null, 'status' => 'week_off',
                    'in' => null, 'out' => null, 'gross' => 0, 'net' => 0
                ],
            ];

            foreach ($days as $dateStr => $day) {
                $punchInTime = $day['in'] ? Carbon::parse($dateStr . ' ' . $day['in'], self::TIMEZONE) : null;
                $punchOutTime = $day['out'] ? Carbon::parse($dateStr . ' ' . $day['out'], self::TIMEZONE) : null;

                $attendanceId = DB::table('attendances')->insertGetId([
                    'user_id' => $userId,
                    'employee_id' => $employeeId,
                    'attendance_time_id' => 1,
                    'attendance_type_id' => $typeIds[$day['type']],
                    'attendance_date' => $dateStr,
                    'punch_in_time' => $punchInTime,
                    'punch_out_time' => $punchOutTime,
                    'target_punch_out_time' => $punchInTime ? $punchInTime->copy()->addMinutes(540)->format('H:i:s') : null,
                    'work_mode' => $day['mode'],
                    'gross_work_minutes' => $day['gross'],
                    'break_minutes' => $punchInTime ? 60 : 0,
                    'lunch_break_minutes' => $punchInTime ? 60 : 0,
                    'total_work_minutes' => $day['net'],
                    'is_late' => $day['is_late'] ?? false,
                    'late_minutes' => $day['late_minutes'] ?? 0,
                    'is_early_out' => $day['is_early_out'] ?? false,
                    'early_out_minutes' => $day['early_out_minutes'] ?? 0,
                    'is_half_day' => $day['is_half_day'] ?? false,
                    'is_lwp' => $day['is_lwp'] ?? false,
                    'lwp_reason' => $day['lwp_reason'] ?? null,
                    'half_day_reason' => $day['half_day_reason'] ?? null,
                    'missed_punch' => $day['missed_punch'] ?? false,
                    'is_missed_punch' => $day['is_missed_punch'] ?? false,
                    'missed_punch_reason' => $day['missed_punch_reason'] ?? null,
                    'is_punch_blocked' => $day['is_punch_blocked'] ?? false,
                    'is_blocked' => $day['is_blocked'] ?? false,
                    'block_reason' => $day['block_reason'] ?? null,
                    'auto_blocked_at' => isset($day['auto_blocked_at']) ? Carbon::parse($day['auto_blocked_at'], self::TIMEZONE) : null,
                    'auto_block_reason' => $day['auto_block_reason'] ?? null,
                    'is_profile_completed_at_punch' => true,
                    'is_locked' => false,
                    'payroll_processed' => false,
                    'attendance_status' => $day['status'],
                    'attendance_source' => 'test_seed',
                    'leave_request_id' => $day['leave_request_id'] ?? null,
                    'comp_off_id' => $day['comp_off_id'] ?? null,
                    'remarks' => self::REMARKS,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

                // Sync the attendance violation logs for the newly seeded record
                $attendanceModel = \App\Models\HRMS\Attendance\AttendanceM::find($attendanceId);
                resolve(\App\Services\HRMS\Attendance\AttendanceS::class)->syncAttendanceViolations($attendanceModel);

                // STEP 6: WORK LOGS
                if ($punchInTime && $punchOutTime) {
                    $work_summary = "Daily tasks completed successfully.\n\nDescription\nWorked on May 2026 dummy data features and verified attendance dashboards.\n\nStatus\ncompleted\n\nRequirements\n☑ seeded May 2026 test records\n☑ verified dashboards\n\nTest Status\nTested: ✅\nCompleted: ✅\n\nIssues\nNone\n\nNotes\nno";

                    $work_summary_json = json_encode([
                        'title' => 'Daily Tasks completed successfully',
                        'description' => 'Worked on May 2026 dummy data features and verified attendance dashboards.',
                        'status' => 'completed',
                        'requirements' => [
                            ['text' => 'seeded May 2026 test records', 'done' => true],
                            ['text' => 'verified dashboards', 'done' => true]
                        ],
                        'test_status' => ['tested' => true, 'completed' => true],
                        'issues' => [],
                        'notes' => 'no'
                    ]);

                    // Punch-in log
                    DB::table('attendance_work_logs')->insert([
                        'attendance_id' => $attendanceId,
                        'employee_id' => $employeeId,
                        'user_id' => $userId,
                        'work_date' => $dateStr,
                        'work_summary' => "PUNCH-IN: Checked in for shift in mode: {$day['mode']}.",
                        'work_summary_json' => json_encode(['title' => 'Punch-in log', 'description' => 'User checked in']),
                        'latitude' => $day['mode'] === 'wfo' ? 19.0760 : null,
                        'longitude' => $day['mode'] === 'wfo' ? 72.8777 : null,
                        'device_info' => 'Dart/3.11 (dart:io) mobile',
                        'ip_address' => '10.113.142.137',
                        'remarks' => self::REMARKS,
                        'created_at' => $punchInTime,
                        'updated_at' => $punchInTime
                    ]);

                    // Punch-out log / Daily report
                    DB::table('attendance_work_logs')->insert([
                        'attendance_id' => $attendanceId,
                        'employee_id' => $employeeId,
                        'user_id' => $userId,
                        'work_date' => $dateStr,
                        'work_summary' => $work_summary,
                        'work_summary_json' => $work_summary_json,
                        'latitude' => $day['mode'] === 'wfo' ? 19.0762 : null,
                        'longitude' => $day['mode'] === 'wfo' ? 72.8779 : null,
                        'device_info' => 'Dart/3.11 (dart:io) mobile',
                        'ip_address' => '10.113.142.137',
                        'remarks' => self::REMARKS,
                        'created_at' => $punchOutTime,
                        'updated_at' => $punchOutTime
                    ]);
                }

                // STEP 10: HOLIDAY/WEEKOFF WORK REQUESTS (May 23 and May 30)
                if ($dateStr === '2026-05-23') {
                    DB::table('holiday_work_requests')->insert([
                        'employee_id' => $employeeId,
                        'attendance_id' => $attendanceId,
                        'worked_date' => '2026-05-23',
                        'work_type' => 'weekoff_work',
                        'work_mode' => 'wfh',
                        'comp_off_generated' => 1,
                        'comp_off_id' => $compOffId,
                        'reason' => 'Weekend deployment support',
                        'status' => 'approved',
                        'approved_by_user_id' => 52,
                        'approved_at' => '2026-05-23 10:00:00',
                        'notes' => self::REMARKS,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }

                if ($dateStr === '2026-05-30') {
                    DB::table('holiday_work_requests')->insert([
                        'employee_id' => $employeeId,
                        'attendance_id' => $attendanceId,
                        'worked_date' => '2026-05-30',
                        'work_type' => 'weekoff_work',
                        'work_mode' => 'wfo',
                        'comp_off_generated' => 0,
                        'comp_off_id' => null,
                        'reason' => 'Weekend system verification',
                        'status' => 'pending',
                        'notes' => self::REMARKS,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }

                // STEP 8: REGULARIZATION REQUESTS (May 15 and May 12)
                if ($dateStr === '2026-05-15') {
                    DB::table('attendance_regularizations')->insert([
                        'employee_id' => $employeeId,
                        'attendance_id' => $attendanceId,
                        'request_type' => 'late_mark_exemption',
                        'existing_punch_in' => $punchInTime,
                        'existing_punch_out' => $punchOutTime,
                        'requested_punch_in' => null,
                        'requested_punch_out' => Carbon::parse('2026-05-15 19:00:00', self::TIMEZONE),
                        'reason' => 'Forgot punch-out during client work',
                        'status' => 'pending',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }

                if ($dateStr === '2026-05-12') {
                    DB::table('attendance_regularizations')->insert([
                        'employee_id' => $employeeId,
                        'attendance_id' => $attendanceId,
                        'request_type' => 'punch_out_correction',
                        'existing_punch_in' => $punchInTime,
                        'existing_punch_out' => null,
                        'requested_punch_in' => null,
                        'requested_punch_out' => Carbon::parse('2026-05-12 19:00:00', self::TIMEZONE),
                        'reason' => 'Missed punch correction due to internet issue',
                        'status' => 'approved',
                        'approved_by_user_id' => 52,
                        'approved_at' => Carbon::parse('2026-05-12 20:00:00', self::TIMEZONE),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }

                if ($dateStr === '2026-05-13') {
                    DB::table('attendance_regularizations')->insert([
                        'employee_id' => $employeeId,
                        'attendance_id' => $attendanceId,
                        'request_type' => 'punch_out_correction',
                        'existing_punch_in' => $punchInTime,
                        'existing_punch_out' => null,
                        'requested_punch_in' => null,
                        'requested_punch_out' => Carbon::parse('2026-05-13 19:05:00', self::TIMEZONE),
                        'reason' => 'Missed punch correction due to power outage',
                        'status' => 'approved',
                        'approved_by_user_id' => 52,
                        'approved_at' => Carbon::parse('2026-05-13 20:00:00', self::TIMEZONE),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }

            // Commit the transaction first so the database contains the records
            DB::commit();
            $this->command->info("Idempotent seed transactions committed successfully.");

            // Now resolve the monthly summary by calling the existing service command
            $this->command->info("Recalculating monthly summary for May 2026...");
            
            // Invoke the monthly summary calculation service using the artisan command!
            \Illuminate\Support\Facades\Artisan::call('hrms:attendance-monthly-summary', [
                '--month' => 5,
                '--year' => 2026,
                '--employee_id' => $employeeId
            ]);

            $this->command->info(\Illuminate\Support\Facades\Artisan::output());

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Seeder failed: " . $e->getMessage());
            $this->command->error($e->getTraceAsString());
            throw $e;
        }
    }
}
