<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class May2026MultiEmployeeAttendanceSeeder extends Seeder
{
    private const TIMEZONE = 'Asia/Kolkata';
    private const REMARKS = 'May 2026 multi-employee HRMS test seed';

    // Target Employees
    private const EMP_44 = 44;   // Clean Payroll-Ready Mixed Data
    private const EMP_76 = 76;   // Blocker / HR Approval Test Data
    private const EMP_440 = 440; // Missing Summary / Incomplete Data Test

    private const USER_44 = 52;
    private const USER_76 = 91;
    private const USER_440 = 477;

    public function run()
    {
        $this->command->info("Starting May 2026 Multi-Employee HRMS Seeder...");

        $targetEmployees = [self::EMP_44, self::EMP_76, self::EMP_440];
        $startDateStr = '2026-05-01';
        $endDateStr = '2026-05-31';

        DB::beginTransaction();
        try {
            // ==========================================
            // PHASE 4 — CLEANUP SCOPE
            // ==========================================
            $this->command->info("Cleaning up May 2026 seeded data for target employees [44, 76, 440]...");

            // 1. Get attendance IDs for target employees in May 2026
            $attendanceIds = DB::table('attendances')
                ->whereIn('employee_id', $targetEmployees)
                ->whereBetween('attendance_date', [$startDateStr, $endDateStr])
                ->pluck('id')
                ->toArray();

            // 2. Delete attendance_work_logs
            if (!empty($attendanceIds)) {
                DB::table('attendance_work_logs')->whereIn('attendance_id', $attendanceIds)->delete();
            }
            DB::table('attendance_work_logs')
                ->whereIn('employee_id', $targetEmployees)
                ->whereBetween('work_date', [$startDateStr, $endDateStr])
                ->delete();

            // 3. Delete attendance_regularizations
            if (!empty($attendanceIds)) {
                DB::table('attendance_regularizations')->whereIn('attendance_id', $attendanceIds)->delete();
            }
            DB::table('attendance_regularizations')
                ->whereIn('employee_id', $targetEmployees)
                ->where(function ($q) use ($startDateStr, $endDateStr) {
                    $q->whereBetween('existing_punch_in', [$startDateStr . ' 00:00:00', $endDateStr . ' 23:59:59'])
                      ->orWhereBetween('requested_punch_in', [$startDateStr . ' 00:00:00', $endDateStr . ' 23:59:59']);
                })
                ->delete();

            // 4. Delete attendance_violations
            if (Schema::hasTable('attendance_violations')) {
                DB::table('attendance_violations')
                    ->whereIn('employee_id', $targetEmployees)
                    ->whereBetween('violation_date', [$startDateStr, $endDateStr])
                    ->delete();
            }

            // 5. Delete wfh_requests
            DB::table('wfh_requests')
                ->whereIn('employee_id', $targetEmployees)
                ->whereBetween('request_date', [$startDateStr, $endDateStr])
                ->delete();

            // 6. Delete holiday_work_requests
            DB::table('holiday_work_requests')
                ->whereIn('employee_id', $targetEmployees)
                ->whereBetween('worked_date', [$startDateStr, $endDateStr])
                ->delete();

            // 7. Delete leave requests and leave request dates
            $leaveRequestIds = DB::table('leave_requests')
                ->whereIn('employee_id', $targetEmployees)
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
                ->whereIn('employee_id', $targetEmployees)
                ->whereBetween('worked_date', [$startDateStr, $endDateStr])
                ->delete();

            // 9. Delete monthly_attendance_summaries
            DB::table('monthly_attendance_summaries')
                ->whereIn('employee_id', $targetEmployees)
                ->where('month', 5)
                ->where('year', 2026)
                ->delete();

            // 10. Delete payroll_attendance_impacts
            if (Schema::hasTable('payroll_attendance_impacts')) {
                DB::table('payroll_attendance_impacts')
                    ->whereIn('employee_id', $targetEmployees)
                    ->where('month', 5)
                    ->where('year', 2026)
                    ->delete();
            }

            // 11. Delete attendances
            DB::table('attendances')
                ->whereIn('employee_id', $targetEmployees)
                ->whereBetween('attendance_date', [$startDateStr, $endDateStr])
                ->delete();

            $this->command->info("Cleanup completed.");

            // ==========================================
            // PHASE 12 — SALARY STRUCTURES & LEAVES
            // ==========================================
            $this->command->info("Configuring Leave Allocations and Salary Structures...");

            // 1. Leave Allocation for Employee 44
            DB::table('leave_allocations')->updateOrInsert(
                ['employee_id' => self::EMP_44, 'year' => 2026],
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
                    'allocation_reason' => 'May 2026 test seed allocation',
                    'created_by_user_id' => 1,
                    'is_locked' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );

            // 2. Leave Allocation for Employee 76
            DB::table('leave_allocations')->updateOrInsert(
                ['employee_id' => self::EMP_76, 'year' => 2026],
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
                    'allocation_reason' => 'May 2026 test seed allocation',
                    'created_by_user_id' => 1,
                    'is_locked' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );

            // 3. Leave Allocation for Employee 440
            DB::table('leave_allocations')->updateOrInsert(
                ['employee_id' => self::EMP_440, 'year' => 2026],
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
                    'allocation_reason' => 'May 2026 test seed allocation',
                    'created_by_user_id' => 1,
                    'is_locked' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );

            // 4. Salary structure for Employee 44 (18,000)
            DB::table('enterprise_salary_structures')->where('employee_id', self::EMP_44)->delete();
            DB::table('enterprise_salary_structures')->insert([
                'employee_id' => self::EMP_44,
                'status' => 'active',
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
                'sync_reference_id' => self::EMP_44,
                'revision_reason' => 'May 2026 HRMS test seed structure',
                'created_by_user_id' => 1,
                'approved_by_user_id' => 1,
                'approved_at' => Carbon::now(),
                'remarks' => self::REMARKS,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // 5. Salary structure for Employee 76 (22,000)
            DB::table('enterprise_salary_structures')->where('employee_id', self::EMP_76)->delete();
            DB::table('enterprise_salary_structures')->insert([
                'employee_id' => self::EMP_76,
                'status' => 'active',
                'effective_from' => '2026-03-01',
                'annual_ctc' => 264000.00,
                'monthly_ctc' => 22000.00,
                'basic_annual' => 132000.00,
                'basic_monthly' => 11000.00,
                'hra_annual' => 66000.00,
                'hra_monthly' => 5500.00,
                'special_allowance_annual' => 66000.00,
                'special_allowance_monthly' => 5500.00,
                'professional_tax_monthly' => 200.00,
                'tds_annual' => 0.00,
                'tds_monthly' => 0.00,
                'other_deduction_monthly' => 0.00,
                'source' => 'test_seed',
                'stage' => 'internship',
                'sync_reference_type' => 'employee_onboarding',
                'sync_reference_id' => self::EMP_76,
                'revision_reason' => 'May 2026 HRMS test seed structure',
                'created_by_user_id' => 1,
                'approved_by_user_id' => 1,
                'approved_at' => Carbon::now(),
                'remarks' => self::REMARKS,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // 6. Salary structure for Employee 440 (26,000)
            DB::table('enterprise_salary_structures')->where('employee_id', self::EMP_440)->delete();
            DB::table('enterprise_salary_structures')->insert([
                'employee_id' => self::EMP_440,
                'status' => 'active',
                'effective_from' => '2026-03-01',
                'annual_ctc' => 312000.00,
                'monthly_ctc' => 26000.00,
                'basic_annual' => 156000.00,
                'basic_monthly' => 13000.00,
                'hra_annual' => 78000.00,
                'hra_monthly' => 6500.00,
                'special_allowance_annual' => 78000.00,
                'special_allowance_monthly' => 6500.00,
                'professional_tax_monthly' => 200.00,
                'tds_annual' => 0.00,
                'tds_monthly' => 0.00,
                'other_deduction_monthly' => 0.00,
                'source' => 'test_seed',
                'stage' => 'internship',
                'sync_reference_type' => 'employee_onboarding',
                'sync_reference_id' => self::EMP_440,
                'revision_reason' => 'May 2026 HRMS test seed structure',
                'created_by_user_id' => 1,
                'approved_by_user_id' => 1,
                'approved_at' => Carbon::now(),
                'remarks' => self::REMARKS,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $typeIds = DB::table('attendance_types')->pluck('id', 'code')->toArray();

            // ==========================================
            // EMPLOYEE 44 — SEED SCENARIOS
            // ==========================================
            $this->command->info("Seeding Employee 44 (Ramdev Lodhi)...");

            // Leave Requests for Emp 44
            $emp44PaidLeaveId = DB::table('leave_requests')->insertGetId([
                'employee_id' => self::EMP_44, 'user_id' => self::USER_44, 'leave_type_id' => 1,
                'start_date' => '2026-05-08', 'end_date' => '2026-05-08', 'requested_days' => 1.00,
                'deducted_days' => 1.00, 'is_half_day' => 0, 'reason' => 'Family event paid leave',
                'status' => 'approved', 'manager_approved_by' => self::USER_44,
                'manager_approved_at' => '2026-05-07 10:00:00', 'approved_by_user_id' => self::USER_44,
                'approved_at' => '2026-05-07 10:00:00', 'paid_days' => 1.00, 'sick_days' => 0.00,
                'comp_off_days' => 0.00, 'lwp_days' => 0.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'attendance_synced' => 1
            ]);

            DB::table('leave_request_dates')->insert([
                'leave_request_id' => $emp44PaidLeaveId, 'employee_id' => self::EMP_44, 'leave_date' => '2026-05-08',
                'day_name' => 'Friday', 'is_working_day' => 1, 'is_weekoff' => 0, 'is_holiday' => 0, 'is_sandwich_day' => 0,
                'deduct_as_leave' => 1, 'leave_type_code' => 'paid_leave', 'paid_day' => 1.00, 'sick_day' => 0.00,
                'comp_off_day' => 0.00, 'lwp_day' => 0.00, 'remarks' => self::REMARKS, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
            ]);

            $emp44SickLeaveId = DB::table('leave_requests')->insertGetId([
                'employee_id' => self::EMP_44, 'user_id' => self::USER_44, 'leave_type_id' => 2,
                'start_date' => '2026-05-26', 'end_date' => '2026-05-26', 'requested_days' => 1.00,
                'deducted_days' => 1.00, 'is_half_day' => 0, 'reason' => 'Sick leave due to viral fever',
                'status' => 'approved', 'manager_approved_by' => self::USER_44,
                'manager_approved_at' => '2026-05-25 10:00:00', 'approved_by_user_id' => self::USER_44,
                'approved_at' => '2026-05-25 10:00:00', 'paid_days' => 0.00, 'sick_days' => 1.00,
                'comp_off_days' => 0.00, 'lwp_days' => 0.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'attendance_synced' => 1
            ]);

            DB::table('leave_request_dates')->insert([
                'leave_request_id' => $emp44SickLeaveId, 'employee_id' => self::EMP_44, 'leave_date' => '2026-05-26',
                'day_name' => 'Tuesday', 'is_working_day' => 1, 'is_weekoff' => 0, 'is_holiday' => 0, 'is_sandwich_day' => 0,
                'deduct_as_leave' => 1, 'leave_type_code' => 'sick_leave', 'paid_day' => 0.00, 'sick_day' => 1.00,
                'comp_off_day' => 0.00, 'lwp_day' => 0.00, 'remarks' => self::REMARKS, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
            ]);

            // WFH Requests for Emp 44
            $emp44WfhData = [
                ['request_date' => '2026-05-06', 'request_type' => 'working_day_wfh', 'reason_category' => 'normal', 'reason' => 'Client WFH', 'status' => 'approved', 'counts_in_monthly_quota' => 1, 'payroll_impact' => 'none', 'lwp_reason' => null],
                ['request_date' => '2026-05-20', 'request_type' => 'working_day_wfh', 'reason_category' => 'internet_issue', 'reason' => 'Broadband issue', 'status' => 'approved', 'counts_in_monthly_quota' => 1, 'payroll_impact' => 'lwp', 'lwp_reason' => 'WFH LWP manual'],
                ['request_date' => '2026-05-21', 'request_type' => 'working_day_wfh', 'reason_category' => 'normal', 'reason' => 'Normal WFH', 'status' => 'approved', 'counts_in_monthly_quota' => 1, 'payroll_impact' => 'none', 'lwp_reason' => null],
                ['request_date' => '2026-05-23', 'request_type' => 'weekoff_wfh', 'reason_category' => 'normal', 'reason' => 'Weekend deployment', 'status' => 'approved', 'counts_in_monthly_quota' => 0, 'payroll_impact' => 'none', 'lwp_reason' => null],
                ['request_date' => '2026-05-27', 'request_type' => 'working_day_wfh', 'reason_category' => 'normal', 'reason' => 'Over limit WFH', 'status' => 'rejected', 'counts_in_monthly_quota' => 1, 'payroll_impact' => 'none', 'lwp_reason' => null],
                ['request_date' => '2026-05-30', 'request_type' => 'weekoff_wfh', 'reason_category' => 'normal', 'reason' => 'Config checks', 'status' => 'approved', 'counts_in_monthly_quota' => 0, 'payroll_impact' => 'none', 'lwp_reason' => null],
            ];
            foreach ($emp44WfhData as $wfh) {
                DB::table('wfh_requests')->insert(array_merge($wfh, [
                    'employee_id' => self::EMP_44, 'manager_approved_by' => self::USER_44,
                    'manager_approved_at' => $wfh['request_date'] . ' 10:00:00', 'remarks' => self::REMARKS,
                    'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                ]));
            }

            // Comp-off for Emp 44
            $emp44CompOffId = DB::table('comp_offs')->insertGetId([
                'employee_id' => self::EMP_44, 'worked_date' => '2026-05-23', 'earned_days' => 1.00,
                'expiry_date' => '2026-06-30', 'status' => 'available', 'approved_by_user_id' => self::USER_44,
                'approved_at' => '2026-05-23 10:00:00', 'remarks' => self::REMARKS, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
            ]);

            // Date-wise attendance for Emp 44
            $emp44Days = [
                '2026-05-01' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:55:00', 'out' => '19:05:00', 'gross' => 550, 'net' => 490],
                '2026-05-02' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:05:00', 'out' => '19:00:00', 'gross' => 535, 'net' => 475],
                '2026-05-03' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-04' => ['type' => 'late', 'mode' => 'wfo', 'status' => 'present', 'in' => '11:07:00', 'out' => '19:20:00', 'gross' => 493, 'net' => 433, 'is_late' => true, 'late_minutes' => 62],
                '2026-05-05' => ['type' => 'early_leave', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '18:10:00', 'gross' => 490, 'net' => 430, 'is_early_out' => true, 'early_out_minutes' => 50],
                '2026-05-06' => ['type' => 'present', 'mode' => 'wfh', 'status' => 'present', 'in' => '10:02:00', 'out' => '19:00:00', 'gross' => 538, 'net' => 478],
                '2026-05-07' => ['type' => 'half_day', 'mode' => 'wfo', 'status' => 'half_day', 'in' => '10:10:00', 'out' => '15:00:00', 'gross' => 290, 'net' => 230, 'is_half_day' => true, 'half_day_reason' => 'Low hours'],
                '2026-05-08' => ['type' => 'leave', 'mode' => null, 'status' => 'leave', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0, 'leave_request_id' => $emp44PaidLeaveId],
                '2026-05-09' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-10' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-11' => ['type' => 'absent', 'mode' => null, 'status' => 'absent', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-12' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-13' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:05:00', 'out' => '19:05:00', 'gross' => 540, 'net' => 480],
                '2026-05-14' => ['type' => 'lwp', 'mode' => 'wfo', 'status' => 'lwp', 'in' => '10:00:00', 'out' => null, 'gross' => 0, 'net' => 0, 'is_lwp' => true, 'lwp_reason' => 'Missed punch threshold exceeded'],
                '2026-05-15' => ['type' => 'late', 'mode' => 'wfo', 'status' => 'present', 'in' => '11:06:00', 'out' => '18:20:00', 'gross' => 434, 'net' => 374, 'is_late' => true, 'late_minutes' => 61],
                '2026-05-16' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-17' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-18' => ['type' => 'absent', 'mode' => null, 'status' => 'absent', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-19' => ['type' => 'absent', 'mode' => null, 'status' => 'absent', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-20' => ['type' => 'lwp', 'mode' => 'wfh', 'status' => 'lwp', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480, 'is_lwp' => true, 'lwp_reason' => 'WFH LWP manual'],
                '2026-05-21' => ['type' => 'present', 'mode' => 'wfh', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-22' => ['type' => 'half_day', 'mode' => 'wfo', 'status' => 'half_day', 'in' => '11:06:00', 'out' => '18:20:00', 'gross' => 434, 'net' => 374, 'is_late' => true, 'late_minutes' => 66, 'is_early_out' => true, 'early_out_minutes' => 40, 'is_half_day' => true, 'half_day_reason' => 'Combined violations'],
                '2026-05-23' => ['type' => 'present', 'mode' => 'wfh', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480, 'comp_off_id' => $emp44CompOffId],
                '2026-05-24' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-25' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:50:00', 'out' => '18:55:00', 'gross' => 545, 'net' => 485],
                '2026-05-26' => ['type' => 'leave', 'mode' => null, 'status' => 'leave', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0, 'leave_request_id' => $emp44SickLeaveId],
                '2026-05-27' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:58:00', 'out' => '19:02:00', 'gross' => 544, 'net' => 484],
                '2026-05-28' => ['type' => 'lwp', 'mode' => null, 'status' => 'lwp', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0, 'is_lwp' => true, 'lwp_reason' => 'Direct LWP marked'],
                '2026-05-29' => ['type' => 'absent', 'mode' => null, 'status' => 'absent', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-30' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-31' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
            ];
            foreach ($emp44Days as $dateStr => $day) {
                $punchInTime = $day['in'] ? Carbon::parse($dateStr . ' ' . $day['in'], self::TIMEZONE) : null;
                $punchOutTime = $day['out'] ? Carbon::parse($dateStr . ' ' . $day['out'], self::TIMEZONE) : null;

                $attendanceId = DB::table('attendances')->insertGetId([
                    'user_id' => self::USER_44, 'employee_id' => self::EMP_44, 'attendance_time_id' => 1,
                    'attendance_type_id' => $typeIds[$day['type']], 'attendance_date' => $dateStr,
                    'punch_in_time' => $punchInTime, 'punch_out_time' => $punchOutTime,
                    'target_punch_out_time' => $punchInTime ? $punchInTime->copy()->addMinutes(540)->format('H:i:s') : null,
                    'work_mode' => $day['mode'], 'gross_work_minutes' => $day['gross'],
                    'break_minutes' => $punchInTime ? 60 : 0, 'lunch_break_minutes' => $punchInTime ? 60 : 0,
                    'total_work_minutes' => $day['net'], 'is_late' => $day['is_late'] ?? false,
                    'late_minutes' => $day['late_minutes'] ?? 0, 'is_early_out' => $day['is_early_out'] ?? false,
                    'early_out_minutes' => $day['early_out_minutes'] ?? 0, 'is_half_day' => $day['is_half_day'] ?? false,
                    'is_lwp' => $day['is_lwp'] ?? false, 'lwp_reason' => $day['lwp_reason'] ?? null,
                    'half_day_reason' => $day['half_day_reason'] ?? null, 'missed_punch' => $day['missed_punch'] ?? false,
                    'is_missed_punch' => $day['is_missed_punch'] ?? false, 'missed_punch_reason' => $day['missed_punch_reason'] ?? null,
                    'is_punch_blocked' => $day['is_punch_blocked'] ?? false, 'is_blocked' => $day['is_blocked'] ?? false,
                    'block_reason' => $day['block_reason'] ?? null, 'is_profile_completed_at_punch' => true,
                    'is_locked' => false, 'payroll_processed' => false, 'attendance_status' => $day['status'],
                    'attendance_source' => 'test_seed', 'leave_request_id' => $day['leave_request_id'] ?? null,
                    'comp_off_id' => $day['comp_off_id'] ?? null, 'remarks' => self::REMARKS,
                    'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                ]);

                // Sync attendance violations using resolving helper
                $attendanceModel = \App\Models\HRMS\Attendance\AttendanceM::find($attendanceId);
                resolve(\App\Services\HRMS\Attendance\AttendanceS::class)->syncAttendanceViolations($attendanceModel);

                // Work logs for Employee 44
                if ($punchInTime && $punchOutTime) {
                    DB::table('attendance_work_logs')->insert([
                        'attendance_id' => $attendanceId, 'employee_id' => self::EMP_44, 'user_id' => self::USER_44,
                        'work_date' => $dateStr, 'work_summary' => "PUNCH-IN: Checked in for shift in mode: {$day['mode']}.",
                        'work_summary_json' => json_encode(['title' => 'Punch-in log', 'description' => 'User checked in']),
                        'latitude' => $day['mode'] === 'wfo' ? 19.0760 : null, 'longitude' => $day['mode'] === 'wfo' ? 72.8777 : null,
                        'device_info' => 'Dart/3.11 (dart:io) mobile', 'ip_address' => '10.113.142.137',
                        'remarks' => self::REMARKS, 'created_at' => $punchInTime, 'updated_at' => $punchInTime
                    ]);

                    DB::table('attendance_work_logs')->insert([
                        'attendance_id' => $attendanceId, 'employee_id' => self::EMP_44, 'user_id' => self::USER_44,
                        'work_date' => $dateStr, 'work_summary' => "Daily tasks completed successfully in mode: {$day['mode']}.",
                        'work_summary_json' => json_encode(['title' => 'Work Completed', 'description' => 'User punched out']),
                        'latitude' => $day['mode'] === 'wfo' ? 19.0762 : null, 'longitude' => $day['mode'] === 'wfo' ? 72.8779 : null,
                        'device_info' => 'Dart/3.11 (dart:io) mobile', 'ip_address' => '10.113.142.137',
                        'remarks' => self::REMARKS, 'created_at' => $punchOutTime, 'updated_at' => $punchOutTime
                    ]);
                }

                // Regularizations for Emp 44 (May 12 correction and May 15 exemption)
                if ($dateStr === '2026-05-12') {
                    DB::table('attendance_regularizations')->insert([
                        'employee_id' => self::EMP_44, 'attendance_id' => $attendanceId, 'request_type' => 'punch_out_correction',
                        'existing_punch_in' => $punchInTime, 'existing_punch_out' => null, 'requested_punch_in' => null,
                        'requested_punch_out' => Carbon::parse('2026-05-12 19:00:00', self::TIMEZONE), 'reason' => 'Power failure',
                        'status' => 'approved', 'approved_by_user_id' => self::USER_44, 'approved_at' => Carbon::now(),
                        'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                    ]);
                }
                if ($dateStr === '2026-05-13') {
                    DB::table('attendance_regularizations')->insert([
                        'employee_id' => self::EMP_44, 'attendance_id' => $attendanceId, 'request_type' => 'punch_out_correction',
                        'existing_punch_in' => $punchInTime, 'existing_punch_out' => null, 'requested_punch_in' => null,
                        'requested_punch_out' => Carbon::parse('2026-05-13 19:05:00', self::TIMEZONE), 'reason' => 'Missed swipe',
                        'status' => 'approved', 'approved_by_user_id' => self::USER_44, 'approved_at' => Carbon::now(),
                        'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                    ]);
                }
                if ($dateStr === '2026-05-15') {
                    DB::table('attendance_regularizations')->insert([
                        'employee_id' => self::EMP_44, 'attendance_id' => $attendanceId, 'request_type' => 'late_mark_exemption',
                        'existing_punch_in' => $punchInTime, 'existing_punch_out' => $punchOutTime, 'requested_punch_in' => null,
                        'requested_punch_out' => null, 'reason' => 'Heavy traffic congestion', 'status' => 'pending',
                        'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                    ]);
                }

                // Holiday / Weekoff Work requests for Emp 44
                if ($dateStr === '2026-05-23') {
                    DB::table('holiday_work_requests')->insert([
                        'employee_id' => self::EMP_44, 'attendance_id' => $attendanceId, 'worked_date' => '2026-05-23',
                        'work_type' => 'weekoff_work', 'work_mode' => 'wfh', 'comp_off_generated' => 1, 'comp_off_id' => $emp44CompOffId,
                        'reason' => 'Deployment checks', 'status' => 'approved', 'approved_by_user_id' => self::USER_44,
                        'approved_at' => '2026-05-23 10:00:00', 'notes' => self::REMARKS, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                    ]);
                }
                if ($dateStr === '2026-05-30') {
                    DB::table('holiday_work_requests')->insert([
                        'employee_id' => self::EMP_44, 'attendance_id' => $attendanceId, 'worked_date' => '2026-05-30',
                        'work_type' => 'weekoff_work', 'work_mode' => 'wfo', 'comp_off_generated' => 0, 'comp_off_id' => null,
                        'reason' => 'Server check', 'status' => 'pending', 'notes' => self::REMARKS,
                        'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                    ]);
                }
            }


            // ==========================================
            // EMPLOYEE 76 — SEED SCENARIOS (BLOCKERS)
            // ==========================================
            $this->command->info("Seeding Employee 76 (Ramdev Lodhi Blocker)...");

            // Pending WFH Request
            DB::table('wfh_requests')->insert([
                'employee_id' => self::EMP_76, 'request_date' => '2026-05-18', 'request_type' => 'working_day_wfh',
                'reason_category' => 'normal', 'reason' => 'Family visit WFH', 'status' => 'pending',
                'counts_in_monthly_quota' => 1, 'payroll_impact' => 'none', 'remarks' => self::REMARKS,
                'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
            ]);

            // Pending Holiday work request on May 16 Saturday
            DB::table('holiday_work_requests')->insert([
                'employee_id' => self::EMP_76, 'attendance_id' => null, 'worked_date' => '2026-05-16',
                'work_type' => 'weekoff_work', 'work_mode' => 'wfo', 'comp_off_generated' => 0,
                'reason' => 'Client emergency weekend support', 'status' => 'pending', 'notes' => self::REMARKS,
                'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
            ]);

            // Date-wise attendance for Emp 76
            $emp76Days = [
                '2026-05-01' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:55:00', 'out' => '19:00:00', 'gross' => 545, 'net' => 485],
                '2026-05-02' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-03' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-04' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:58:00', 'out' => '19:02:00', 'gross' => 544, 'net' => 484],
                '2026-05-05' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-06' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:59:00', 'out' => '19:01:00', 'gross' => 542, 'net' => 482],
                '2026-05-07' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-08' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:55:00', 'out' => '19:00:00', 'gross' => 545, 'net' => 485],
                '2026-05-09' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-10' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                
                // Missed punch warning on May 11 (Punched in but no punch out)
                '2026-05-11' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:55:00', 'out' => null, 'gross' => 0, 'net' => 0, 'missed_punch' => true, 'is_missed_punch' => true, 'missed_punch_reason' => 'Forgot to swipe out'],
                
                '2026-05-12' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:50:00', 'out' => '19:00:00', 'gross' => 550, 'net' => 490],
                '2026-05-13' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:55:00', 'out' => '19:05:00', 'gross' => 550, 'net' => 490],
                '2026-05-14' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-15' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-16' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-17' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-18' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-19' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:50:00', 'out' => '18:55:00', 'gross' => 545, 'net' => 485],
                '2026-05-20' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-21' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-22' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-23' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-24' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-25' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:50:00', 'out' => '18:55:00', 'gross' => 545, 'net' => 485],
                '2026-05-26' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                
                // blocker 1: May 27 - punch_blocked
                '2026-05-27' => ['type' => 'punch_blocked', 'mode' => 'wfo', 'status' => 'punch_blocked', 'in' => '09:58:00', 'out' => null, 'gross' => 0, 'net' => 0, 'is_blocked' => true, 'is_punch_blocked' => true, 'block_reason' => 'Face mismatch during checkin'],
                
                // blocker 2: May 28 - pending_hr
                '2026-05-28' => ['type' => 'pending_hr', 'mode' => 'wfo', 'status' => 'pending_hr', 'in' => '10:01:00', 'out' => '19:00:00', 'gross' => 539, 'net' => 479, 'is_blocked' => true, 'block_reason' => 'Out of office geofence punch'],
                
                '2026-05-29' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:55:00', 'out' => '19:00:00', 'gross' => 545, 'net' => 485],
                
                // blocker 3: May 30 - punch_blocked
                '2026-05-30' => ['type' => 'punch_blocked', 'mode' => 'wfo', 'status' => 'punch_blocked', 'in' => '10:00:00', 'out' => null, 'gross' => 0, 'net' => 0, 'is_blocked' => true, 'is_punch_blocked' => true, 'block_reason' => 'Blocked by Admin for pending policy acknowledgement'],
                
                '2026-05-31' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
            ];
            foreach ($emp76Days as $dateStr => $day) {
                $punchInTime = $day['in'] ? Carbon::parse($dateStr . ' ' . $day['in'], self::TIMEZONE) : null;
                $punchOutTime = $day['out'] ? Carbon::parse($dateStr . ' ' . $day['out'], self::TIMEZONE) : null;

                $attendanceId = DB::table('attendances')->insertGetId([
                    'user_id' => self::USER_76, 'employee_id' => self::EMP_76, 'attendance_time_id' => 1,
                    'attendance_type_id' => $typeIds[$day['type']], 'attendance_date' => $dateStr,
                    'punch_in_time' => $punchInTime, 'punch_out_time' => $punchOutTime,
                    'target_punch_out_time' => $punchInTime ? $punchInTime->copy()->addMinutes(540)->format('H:i:s') : null,
                    'work_mode' => $day['mode'], 'gross_work_minutes' => $day['gross'],
                    'break_minutes' => $punchInTime ? 60 : 0, 'lunch_break_minutes' => $punchInTime ? 60 : 0,
                    'total_work_minutes' => $day['net'], 'is_late' => $day['is_late'] ?? false,
                    'late_minutes' => $day['late_minutes'] ?? 0, 'is_early_out' => $day['is_early_out'] ?? false,
                    'early_out_minutes' => $day['early_out_minutes'] ?? 0, 'is_half_day' => $day['is_half_day'] ?? false,
                    'is_lwp' => $day['is_lwp'] ?? false, 'lwp_reason' => $day['lwp_reason'] ?? null,
                    'half_day_reason' => $day['half_day_reason'] ?? null, 'missed_punch' => $day['missed_punch'] ?? false,
                    'is_missed_punch' => $day['is_missed_punch'] ?? false, 'missed_punch_reason' => $day['missed_punch_reason'] ?? null,
                    'is_punch_blocked' => $day['is_punch_blocked'] ?? false, 'is_blocked' => $day['is_blocked'] ?? false,
                    'block_reason' => $day['block_reason'] ?? null, 'is_profile_completed_at_punch' => true,
                    'is_locked' => false, 'payroll_processed' => false, 'attendance_status' => $day['status'],
                    'attendance_source' => 'test_seed', 'leave_request_id' => null,
                    'comp_off_id' => null, 'remarks' => self::REMARKS,
                    'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                ]);

                // Sync attendance violations using resolving helper
                $attendanceModel = \App\Models\HRMS\Attendance\AttendanceM::find($attendanceId);
                resolve(\App\Services\HRMS\Attendance\AttendanceS::class)->syncAttendanceViolations($attendanceModel);

                // Pending Regularization for Emp 76 on May 12 (punch_out_correction)
                if ($dateStr === '2026-05-12') {
                    DB::table('attendance_regularizations')->insert([
                        'employee_id' => self::EMP_76, 'attendance_id' => $attendanceId, 'request_type' => 'punch_out_correction',
                        'existing_punch_in' => $punchInTime, 'existing_punch_out' => null, 'requested_punch_in' => null,
                        'requested_punch_out' => Carbon::parse('2026-05-12 19:00:00', self::TIMEZONE), 'reason' => 'Internet issue correction',
                        'status' => 'pending', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                    ]);
                }
            }


            // ==========================================
            // EMPLOYEE 440 — SEED SCENARIOS (MISSING SUMMARY)
            // ==========================================
            $this->command->info("Seeding Employee 440 (RamThakur)...");

            // Date-wise attendance for Emp 440 (perfect attendance but we do NOT seed summary)
            $emp440Days = [
                '2026-05-01' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:55:00', 'out' => '19:00:00', 'gross' => 545, 'net' => 485],
                '2026-05-02' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-03' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-04' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:58:00', 'out' => '19:02:00', 'gross' => 544, 'net' => 484],
                '2026-05-05' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-06' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:59:00', 'out' => '19:01:00', 'gross' => 542, 'net' => 482],
                '2026-05-07' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-08' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:55:00', 'out' => '19:00:00', 'gross' => 545, 'net' => 485],
                '2026-05-09' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-10' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-11' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:55:00', 'out' => '19:00:00', 'gross' => 545, 'net' => 485],
                '2026-05-12' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:50:00', 'out' => '19:00:00', 'gross' => 550, 'net' => 490],
                '2026-05-13' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:55:00', 'out' => '19:05:00', 'gross' => 550, 'net' => 490],
                '2026-05-14' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-15' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-16' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-17' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-18' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-19' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:50:00', 'out' => '18:55:00', 'gross' => 545, 'net' => 485],
                '2026-05-20' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-21' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-22' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-23' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-24' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-25' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:50:00', 'out' => '18:55:00', 'gross' => 545, 'net' => 485],
                '2026-05-26' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-27' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:58:00', 'out' => '19:02:00', 'gross' => 544, 'net' => 484],
                '2026-05-28' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '10:00:00', 'out' => '19:00:00', 'gross' => 540, 'net' => 480],
                '2026-05-29' => ['type' => 'present', 'mode' => 'wfo', 'status' => 'present', 'in' => '09:55:00', 'out' => '19:00:00', 'gross' => 545, 'net' => 485],
                '2026-05-30' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
                '2026-05-31' => ['type' => 'week_off', 'mode' => null, 'status' => 'week_off', 'in' => null, 'out' => null, 'gross' => 0, 'net' => 0],
            ];
            foreach ($emp440Days as $dateStr => $day) {
                $punchInTime = $day['in'] ? Carbon::parse($dateStr . ' ' . $day['in'], self::TIMEZONE) : null;
                $punchOutTime = $day['out'] ? Carbon::parse($dateStr . ' ' . $day['out'], self::TIMEZONE) : null;

                $attendanceId = DB::table('attendances')->insertGetId([
                    'user_id' => self::USER_440, 'employee_id' => self::EMP_440, 'attendance_time_id' => 1,
                    'attendance_type_id' => $typeIds[$day['type']], 'attendance_date' => $dateStr,
                    'punch_in_time' => $punchInTime, 'punch_out_time' => $punchOutTime,
                    'target_punch_out_time' => $punchInTime ? $punchInTime->copy()->addMinutes(540)->format('H:i:s') : null,
                    'work_mode' => $day['mode'], 'gross_work_minutes' => $day['gross'],
                    'break_minutes' => $punchInTime ? 60 : 0, 'lunch_break_minutes' => $punchInTime ? 60 : 0,
                    'total_work_minutes' => $day['net'], 'is_late' => false,
                    'late_minutes' => 0, 'is_early_out' => false,
                    'early_out_minutes' => 0, 'is_half_day' => false,
                    'is_lwp' => false, 'lwp_reason' => null,
                    'half_day_reason' => null, 'missed_punch' => false,
                    'is_missed_punch' => false, 'missed_punch_reason' => null,
                    'is_punch_blocked' => false, 'is_blocked' => false,
                    'block_reason' => null, 'is_profile_completed_at_punch' => true,
                    'is_locked' => false, 'payroll_processed' => false, 'attendance_status' => $day['status'],
                    'attendance_source' => 'test_seed', 'leave_request_id' => null,
                    'comp_off_id' => null, 'remarks' => self::REMARKS,
                    'created_at' => Carbon::now(), 'updated_at' => Carbon::now()
                ]);

                // Sync attendance violations using resolving helper
                $attendanceModel = \App\Models\HRMS\Attendance\AttendanceM::find($attendanceId);
                resolve(\App\Services\HRMS\Attendance\AttendanceS::class)->syncAttendanceViolations($attendanceModel);
            }

            // Commit the transaction first so the database contains the records
            DB::commit();
            $this->command->info("Seeder transactions committed successfully.");

            // Recalculate monthly summary for May 2026 for Employee 44
            $this->command->info("Recalculating monthly summary for May 2026 for Employee 44...");
            \Illuminate\Support\Facades\Artisan::call('hrms:attendance-monthly-summary', [
                '--month' => 5,
                '--year' => 2026,
                '--employee_id' => self::EMP_44
            ]);
            $this->command->info(\Illuminate\Support\Facades\Artisan::output());

            // Recalculate monthly summary for May 2026 for Employee 76 (Blocker Summary)
            $this->command->info("Recalculating monthly summary for May 2026 for Employee 76...");
            \Illuminate\Support\Facades\Artisan::call('hrms:attendance-monthly-summary', [
                '--month' => 5,
                '--year' => 2026,
                '--employee_id' => self::EMP_76
            ]);
            $this->command->info(\Illuminate\Support\Facades\Artisan::output());

            // NOTE: We DO NOT compile summary for Employee 440 to satisfy the missing summary blocker test case!
            $this->command->info("Employee 440 summary left uncompiled to test missing summary blockers.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Multi-Employee Seeder failed: " . $e->getMessage());
            $this->command->error($e->getTraceAsString());
            throw $e;
        }
    }
}
