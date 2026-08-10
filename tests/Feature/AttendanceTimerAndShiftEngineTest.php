<?php

namespace Tests\Feature;

use App\Models\Core\UserM as User;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Employee\EmployeeProfileM as EmployeeProfile;
use App\Services\HRMS\Attendance\AttendanceRuleResolverService;
use App\Services\HRMS\Attendance\AttendanceS;
use App\Services\HRMS\Attendance\AttendanceMobileService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceTimerAndShiftEngineTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Employee $employee;
    protected AttendanceRuleResolverService $resolver;
    protected AttendanceS $attendanceService;
    protected AttendanceMobileService $mobileService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Timer Test User',
            'email' => 'timertest_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->user->id,
            'employee_code' => 'TIMER-' . rand(1000, 9999),
            'employment_status' => 'active',
            'work_mode' => 'wfh',
            'joining_date' => '2025-01-01',
        ]);

        if (Schema::hasTable('employees')) {
            DB::table('employees')->updateOrInsert(
                ['id' => $this->employee->id],
                ['user_id' => $this->user->id, 'employee_code' => $this->employee->employee_code]
            );
        }

        EmployeeProfile::create([
            'employee_id' => $this->employee->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        AttendanceType::firstOrCreate(['code' => 'present'], ['name' => 'Present', 'is_active' => true]);
        AttendanceType::firstOrCreate(['code' => 'half_day'], ['name' => 'Half Day', 'is_active' => true]);
        AttendanceType::firstOrCreate(['code' => 'punch_blocked'], ['name' => 'Punch Blocked', 'is_active' => true]);

        $this->resolver = new AttendanceRuleResolverService();
        $this->attendanceService = app(AttendanceS::class);
        $this->mobileService = app(AttendanceMobileService::class);
    }

    /**
     * Test 1: Employee with required_work_minutes = 300, punch_in = 13:42 -> target_punch_out = 18:42
     */
    public function test_target_punch_out_for_300_min_shift_punched_in_at_13_42(): void
    {
        $shift = AttendanceTime::create([
            'name' => '5 Hour Afternoon Shift',
            'code' => 'shift_5h_' . uniqid(),
            'shift_type' => 'fixed',
            'punch_allowed_from' => '12:00:00',
            'shift_start_time' => '13:00:00',
            'late_after_time' => '14:05:00',
            'block_after_time' => '14:15:00',
            'shift_end_time' => '18:00:00',
            'required_work_minutes' => 300,
            'lunch_break_minutes' => 0,
            'is_active' => true,
        ]);

        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $shift->id,
            'punch_allowed_from' => '12:00:00',
            'shift_start_time' => '13:00:00',
            'late_after_time' => '14:05:00',
            'block_after_time' => '14:15:00',
            'shift_end_time' => '18:00:00',
            'required_work_minutes' => 300,
            'lunch_minutes' => 0,
            'effective_from' => '2026-01-01',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $punchIn = Carbon::parse('2026-08-10 13:42:00', 'Asia/Kolkata');
        $resolvedPolicy = $this->resolver->resolveShiftPolicy($this->employee, '2026-08-10');
        
        $this->assertNotNull($resolvedPolicy);
        $this->assertEquals(300, $resolvedPolicy->required_work_minutes);

        $targetOut = $this->resolver->targetPunchOut($punchIn, $resolvedPolicy);
        $this->assertEquals('18:42:00', $targetOut->format('H:i:s'));
    }

    /**
     * Test 2: punch_in = 14:00, required_work_minutes = 300 -> target_punch_out = 19:00
     */
    public function test_target_punch_out_for_300_min_shift_punched_in_at_14_00(): void
    {
        $shift = AttendanceTime::create([
            'name' => '5 Hour Shift 2',
            'code' => 'shift_5h_b_' . uniqid(),
            'shift_type' => 'fixed',
            'punch_allowed_from' => '12:00:00',
            'shift_start_time' => '13:00:00',
            'required_work_minutes' => 300,
            'lunch_break_minutes' => 0,
            'is_active' => true,
        ]);

        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $shift->id,
            'required_work_minutes' => 300,
            'effective_from' => '2026-01-01',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $punchIn = Carbon::parse('2026-08-10 14:00:00', 'Asia/Kolkata');
        $policy = $this->resolver->resolveShiftPolicy($this->employee, '2026-08-10');
        $targetOut = $this->resolver->targetPunchOut($punchIn, $policy);

        $this->assertEquals('19:00:00', $targetOut->format('H:i:s'));
    }

    /**
     * Test 3: Different employee with required_work_minutes = 480 (8 hours)
     */
    public function test_different_employee_uses_own_required_work_minutes(): void
    {
        $user2 = User::create([
            'name' => 'User 8 Hours',
            'email' => 'user8h_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);
        $emp2 = Employee::create([
            'user_id' => $user2->id,
            'employee_code' => 'EMP8H-' . rand(1000, 9999),
            'employment_status' => 'active',
            'work_mode' => 'wfo',
            'joining_date' => '2025-01-01',
        ]);
        EmployeeProfile::create([
            'employee_id' => $emp2->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        $shift8h = AttendanceTime::create([
            'name' => '8 Hour General Shift',
            'code' => 'shift_8h_' . uniqid(),
            'shift_type' => 'fixed',
            'punch_allowed_from' => '08:00:00',
            'shift_start_time' => '09:00:00',
            'shift_end_time' => '17:00:00',
            'required_work_minutes' => 480,
            'is_active' => true,
        ]);

        DB::table('employee_shift_timings')->insert([
            'employee_id' => $emp2->id,
            'attendance_time_id' => $shift8h->id,
            'required_work_minutes' => 480,
            'effective_from' => '2026-01-01',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $policy = $this->resolver->resolveShiftPolicy($emp2, '2026-08-10');
        $this->assertEquals(480, $policy->required_work_minutes);
    }

    /**
     * Test 4: Different effective_from / effective_to records resolve correct shift timing for attendance_date
     */
    public function test_shift_resolution_by_effective_date_range(): void
    {
        $shiftA = AttendanceTime::create([
            'name' => 'Old Shift 6h',
            'code' => 'old_shift_' . uniqid(),
            'required_work_minutes' => 360,
            'is_active' => true,
        ]);

        $shiftB = AttendanceTime::create([
            'name' => 'New Shift 5h',
            'code' => 'new_shift_' . uniqid(),
            'required_work_minutes' => 300,
            'is_active' => true,
        ]);

        // Old shift: effective 2026-01-01 to 2026-05-31
        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $shiftA->id,
            'required_work_minutes' => 360,
            'effective_from' => '2026-01-01',
            'effective_to' => '2026-05-31',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // New shift: effective 2026-06-01 onwards
        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $shiftB->id,
            'required_work_minutes' => 300,
            'effective_from' => '2026-06-01',
            'effective_to' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $policyOld = $this->resolver->resolveShiftPolicy($this->employee, '2026-03-15');
        $this->assertEquals(360, $policyOld->required_work_minutes);

        $policyNew = $this->resolver->resolveShiftPolicy($this->employee, '2026-08-10');
        $this->assertEquals(300, $policyNew->required_work_minutes);
    }

    /**
     * Test 5: block_after_time crossed does not automatically set UI/mobile state to blocked prior to punch attempt
     */
    public function test_block_after_time_crossed_does_not_auto_block_ui_state_before_punch_attempt(): void
    {
        $shift = AttendanceTime::create([
            'name' => 'Block Test Shift',
            'code' => 'block_shift_' . uniqid(),
            'shift_type' => 'fixed',
            'punch_allowed_from' => '12:00:00',
            'shift_start_time' => '13:00:00',
            'late_after_time' => '14:05:00',
            'block_after_time' => '14:15:00',
            'shift_end_time' => '18:00:00',
            'required_work_minutes' => 300,
            'is_active' => true,
        ]);

        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $shift->id,
            'effective_from' => '2026-01-01',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Evaluate at 14:30:00 (after block_after_time 14:15:00) before any punch in
        $state = $this->resolver->resolveMobileState($this->employee, '2026-08-10 14:30:00', null);

        $this->assertFalse($state['is_blocked']);
        $this->assertFalse($state['is_punch_blocked']);
        $this->assertEquals('not_punched', $state['status_code']);
    }

    /**
     * Test 6: block_after_time punch in attempt is rejected by backend
     */
    public function test_punch_in_after_block_after_time_is_rejected_by_backend(): void
    {
        $shift = AttendanceTime::create([
            'name' => 'Block Reject Shift',
            'code' => 'block_rej_' . uniqid(),
            'shift_type' => 'fixed',
            'punch_allowed_from' => '12:00:00',
            'shift_start_time' => '13:00:00',
            'late_after_time' => '14:05:00',
            'block_after_time' => '14:15:00',
            'shift_end_time' => '18:00:00',
            'required_work_minutes' => 300,
            'is_active' => true,
        ]);

        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $shift->id,
            'block_after_time' => '14:15:00',
            'effective_from' => '2026-01-01',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Carbon::setTestNow(Carbon::create(2026, 8, 10, 14, 30, 0, 'Asia/Kolkata'));

        $res = $this->attendanceService->processPunchIn($this->user->id, 'wfh', 'Testing late block punch');

        $this->assertEquals('error', $res['status']);
        $this->assertEquals('PUNCH_BLOCKED', $res['code'] ?? null);
    }

    /**
     * Test 7: Punch-out before required work completes -> work_completed = false
     */
    public function test_punch_out_before_required_work_results_in_work_completed_false(): void
    {
        $shift = AttendanceTime::create([
            'name' => '5 Hour Shift',
            'code' => 'shift_5h_c_' . uniqid(),
            'shift_type' => 'fixed',
            'punch_allowed_from' => '12:00:00',
            'shift_start_time' => '13:00:00',
            'shift_end_time' => '18:00:00',
            'required_work_minutes' => 300,
            'is_active' => true,
        ]);

        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $shift->id,
            'required_work_minutes' => 300,
            'effective_from' => '2026-01-01',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Punch in at 13:42
        Carbon::setTestNow(Carbon::create(2026, 8, 10, 13, 42, 0, 'Asia/Kolkata'));
        $this->attendanceService->processPunchIn($this->user->id, 'wfh', 'Punch in');

        // Punch out at 16:42 (worked 180 minutes out of 300)
        Carbon::setTestNow(Carbon::create(2026, 8, 10, 16, 42, 0, 'Asia/Kolkata'));
        $res = $this->attendanceService->processPunchOut($this->user->id, 'Done partial tasks', 'Punch out');

        $this->assertEquals('success', $res['status']);
        $attRecord = Attendance::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-08-10')->first();
        $formatted = $this->mobileService->formatAttendanceForApi($attRecord);

        $this->assertEquals(300, $formatted['required_work_minutes']);
        $this->assertEquals(180, $formatted['worked_minutes']);
        $this->assertEquals(120, $formatted['remaining_work_minutes']);
        $this->assertEquals(60, $formatted['work_progress_percent']);
        $this->assertFalse($formatted['work_completed']);
    }

    /**
     * Test 8: Punch-out after required work completes -> remaining_work_minutes = 0, work_completed = true, work_progress_percent = 100
     */
    public function test_punch_out_after_required_work_results_in_work_completed_true(): void
    {
        $shift = AttendanceTime::create([
            'name' => '5 Hour Shift',
            'code' => 'shift_5h_d_' . uniqid(),
            'shift_type' => 'fixed',
            'punch_allowed_from' => '12:00:00',
            'shift_start_time' => '13:00:00',
            'shift_end_time' => '18:00:00',
            'required_work_minutes' => 300,
            'is_active' => true,
        ]);

        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $shift->id,
            'required_work_minutes' => 300,
            'effective_from' => '2026-01-01',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Punch in at 13:42
        Carbon::setTestNow(Carbon::create(2026, 8, 10, 13, 42, 0, 'Asia/Kolkata'));
        $this->attendanceService->processPunchIn($this->user->id, 'wfh', 'Punch in');

        // Punch out at 18:42 (worked exactly 300 minutes out of 300)
        Carbon::setTestNow(Carbon::create(2026, 8, 10, 18, 42, 0, 'Asia/Kolkata'));
        $res = $this->attendanceService->processPunchOut($this->user->id, 'Done all tasks', 'Punch out');

        $this->assertEquals('success', $res['status']);
        $attRecord = Attendance::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-08-10')->first();
        $formatted = $this->mobileService->formatAttendanceForApi($attRecord);

        $this->assertEquals(300, $formatted['required_work_minutes']);
        $this->assertEquals(300, $formatted['worked_minutes']);
        $this->assertEquals(0, $formatted['remaining_work_minutes']);
        $this->assertEquals(100, $formatted['work_progress_percent']);
        $this->assertTrue($formatted['work_completed']);
    }
}
