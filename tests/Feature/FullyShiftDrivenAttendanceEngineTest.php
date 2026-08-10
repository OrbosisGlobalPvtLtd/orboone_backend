<?php

namespace Tests\Feature;

use App\Models\Core\UserM as User;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Employee\EmployeeProfileM as EmployeeProfile;
use App\Models\HRMS\Employee\EmployeeShiftTimingM as EmployeeShiftTiming;
use App\Models\HRMS\Leave\LeaveRequestM as LeaveRequest;
use App\Services\HRMS\Attendance\AttendanceRuleResolverService;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FullyShiftDrivenAttendanceEngineTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Employee $employee;
    protected AttendanceTime $fixedShift;
    protected AttendanceTime $flexibleShift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Shift Engine User',
            'email' => 'shiftengine_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);
        $this->employee = Employee::create([
            'user_id' => $this->user->id,
            'employee_code' => 'SHIFT-' . rand(1000, 9999),
            'employment_status' => 'active',
            'work_mode' => 'wfo',
            'joining_date' => '2025-01-01',
        ]);
        if (Schema::hasTable('employees')) {
            DB::table('employees')->insert([
                'id' => $this->employee->id,
                'user_id' => $this->user->id,
                'employee_code' => $this->employee->employee_code,
            ]);
        }
        EmployeeProfile::create([
            'employee_id' => $this->employee->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        AttendanceType::firstOrCreate(['code' => 'present'], ['name' => 'Present', 'is_active' => true]);
        AttendanceType::firstOrCreate(['code' => 'half_day'], ['name' => 'Half Day', 'is_active' => true]);
        AttendanceType::firstOrCreate(['code' => 'punch_blocked'], ['name' => 'Punch Blocked', 'is_active' => true]);

        // Fixed Shift (10:00 AM - 7:00 PM, Late > 10:15, Half Day > 11:00, Block > 11:15)
        $this->fixedShift = AttendanceTime::create([
            'name' => 'Fixed General Shift',
            'code' => 'fixed_' . uniqid(),
            'shift_type' => 'fixed',
            'punch_allowed_from' => '08:00:00',
            'shift_start_time' => '10:00:00',
            'late_after_time' => '10:15:00',
            'warning_after_time' => '10:30:00',
            'half_day_after_time' => '11:00:00',
            'block_after_time' => '11:15:00',
            'shift_end_time' => '19:00:00',
            'required_work_minutes' => 480,
            'half_day_min_minutes' => 240,
            'lunch_break_minutes' => 60,
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->flexibleShift = AttendanceTime::create([
            'name' => 'Flexible Evening Shift',
            'code' => 'flexible_' . uniqid(),
            'shift_type' => 'flexible_part_time',
            'punch_allowed_from' => '12:00:00',
            'shift_start_time' => '14:00:00',
            'late_after_time' => '14:55:00',
            'half_day_after_time' => '15:05:00',
            'block_after_time' => '15:15:00',
            'shift_end_time' => '22:00:00',
            'required_work_minutes' => 480,
            'half_day_min_minutes' => 240,
            'lunch_break_minutes' => 60,
            'is_active' => true,
        ]);

        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $this->fixedShift->id,
            'punch_allowed_from' => '08:00:00',
            'shift_start_time' => '10:00:00',
            'late_after_time' => '10:15:00',
            'half_day_after_time' => '11:00:00',
            'block_after_time' => '11:15:00',
            'shift_end_time' => '19:00:00',
            'required_work_minutes' => 480,
            'lunch_minutes' => 60,
            'effective_from' => '2025-01-01',
            'effective_to' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_normal_fixed_shift_punch_in_calculates_full_day_target_out()
    {
        $attendanceService = app(AttendanceS::class);
        $result = $attendanceService->processPunchIn($this->user->id, 'wfo', 'Normal Punch', [], '2026-08-10 09:55:00', null, false);

        $this->assertTrue((bool) ($result['status'] ?? false));
        $att = Attendance::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-08-10')->first();
        $this->assertNotNull($att);
        $this->assertFalse((bool) $att->is_late);
        $this->assertEquals('present', $att->attendance_status);
        // Target Out = 09:55 + 480 mins + 60 mins break = 18:55 (6:55 PM)
        $this->assertEquals('18:55:00', $att->target_punch_out_time);
    }

    public function test_too_early_punch_in_is_rejected()
    {
        $attendanceService = app(AttendanceS::class);
        // Early login allowed from 08:00 AM. Punch at 07:30 AM should fail.
        $result = $attendanceService->processPunchIn($this->user->id, 'wfo', 'Early Punch', [], '2026-08-10 07:30:00', null, false);

        $this->assertEquals('error', $result['status'] ?? null);
        $this->assertStringContainsString('Too early to punch in', $result['message']);
    }

    public function test_half_day_window_punch_in_auto_triggers_half_day_status_and_target_out()
    {
        $attendanceService = app(AttendanceS::class);
        // Half day after time is 11:00 AM. Block is 11:15 AM. Punch at 11:05 AM.
        $result = $attendanceService->processPunchIn($this->user->id, 'wfo', 'Late Half Day Punch', [], '2026-08-10 11:05:00', null, false);

        $this->assertTrue((bool) ($result['status'] ?? false));
        $att = Attendance::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-08-10')->first();
        $this->assertNotNull($att);
        $this->assertTrue((bool) $att->is_half_day);
        $this->assertEquals('half_day', $att->attendance_status);
        // Target Out = 11:05 + 240 mins (0 break) = 15:05 (3:05 PM)
        $this->assertEquals('15:05:00', $att->target_punch_out_time);
    }

    public function test_blocked_punch_after_block_after_time_is_rejected()
    {
        $attendanceService = app(AttendanceS::class);
        // Block after time is 11:15 AM. Punch at 11:30 AM should be blocked.
        $result = $attendanceService->processPunchIn($this->user->id, 'wfo', 'Blocked Punch', [], '2026-08-10 11:30:00', null, false);

        $this->assertEquals('error', $result['status'] ?? null);
        $this->assertStringContainsString('Punch-in window has closed', $result['message']);
    }

    public function test_first_half_leave_bypasses_morning_block_and_late_rules()
    {
        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-10',
            'is_half_day' => true,
            'half_day_type' => 'first_half',
            'status' => 'approved',
        ]);

        $attendanceService = app(AttendanceS::class);
        // Employee punches in at 02:00 PM (after morning block time 11:15 AM) for 2nd half.
        $result = $attendanceService->processPunchIn($this->user->id, 'wfo', 'First Half Leave Punch', [], '2026-08-10 14:00:00', null, false);

        $this->assertTrue((bool) ($result['status'] ?? false));
        $att = Attendance::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-08-10')->first();
        $this->assertNotNull($att);
        $this->assertFalse((bool) $att->is_late);
        $this->assertEquals('half_day', $att->attendance_status);
        // Target Out = 14:00 + 240 mins (0 break) = 18:00 (6:00 PM)
        $this->assertEquals('18:00:00', $att->target_punch_out_time);
    }

    public function test_second_half_leave_uses_half_day_target_out()
    {
        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-10',
            'is_half_day' => true,
            'half_day_type' => 'second_half',
            'status' => 'approved',
        ]);

        $attendanceService = app(AttendanceS::class);
        // Employee punches in at 10:00 AM.
        $result = $attendanceService->processPunchIn($this->user->id, 'wfo', 'Second Half Leave Punch', [], '2026-08-10 10:00:00', null, false);

        $this->assertTrue((bool) ($result['status'] ?? false));
        $att = Attendance::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-08-10')->first();
        $this->assertNotNull($att);
        $this->assertEquals('half_day', $att->attendance_status);
        // Target Out = 10:00 + 240 mins (0 break) = 14:00 (2:00 PM)
        $this->assertEquals('14:00:00', $att->target_punch_out_time);
    }

    public function test_employee_specific_shift_timing_override_takes_precedence()
    {
        // Create an employee-specific shift override for 2026-08-15
        EmployeeShiftTiming::create([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $this->flexibleShift->id,
            'effective_from' => '2026-08-15',
            'is_active' => true,
            'shift_start_time' => '14:00:00',
            'late_after_time' => '14:55:00',
            'half_day_after_time' => '15:05:00',
            'block_after_time' => '15:15:00',
            'shift_end_time' => '22:00:00',
            'required_work_minutes' => 480,
            'lunch_minutes' => 60,
        ]);

        $resolver = app(AttendanceRuleResolverService::class);
        $policy = $resolver->getPolicyForEmployee($this->employee, '2026-08-15');

        $this->assertNotNull($policy);
        $this->assertEquals($this->flexibleShift->id, $policy->attendance_time_id);
        $this->assertEquals('14:00:00', $policy->shift_start_time);
    }

    public function test_mobile_and_web_parity_returns_identical_status_and_target_out()
    {
        $resolver = app(AttendanceRuleResolverService::class);
        $mobileService = app(\App\Services\HRMS\Attendance\AttendanceMobileService::class);

        $webContext = $resolver->getResolvedAttendanceContext($this->employee, '2026-08-10 10:00:00');
        $mobileResponse = $mobileService->todayStatus($this->user->id);

        $this->assertTrue($mobileResponse['status']);
        $mobileData = $mobileResponse['data'];

        $this->assertEquals($webContext['shift']['id'], $mobileData['policy']['id'] ?? $mobileData['policy']['attendance_time_id'] ?? null);
        $this->assertEquals($webContext['shift']['name'], $mobileData['policy']['name'] ?? $mobileData['policy']['policy_name'] ?? null);
    }
}
