<?php

namespace Tests\Feature;

use App\Models\Core\UserM;
use App\Models\Core\RoleM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceLocationM;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceTodayStatusTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $user;
    private EmployeeM $employee;
    private AttendanceTime $shift;
    private AttendanceType $presentType;
    private AttendanceType $blockedType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Roles
        $employeeRole = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);

        // Create User
        $this->user = UserM::create([
            'name' => 'Test Attendance Employee',
            'email' => 'attendance_employee_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $employeeRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->user->roles()->sync([$employeeRole->id]);

        // Create Employee
        $this->employee = EmployeeM::create([
            'user_id' => $this->user->id,
            'employee_code' => 'EMP-ATT-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        // Create Employee Profile (so they are eligible for attendance)
        EmployeeProfileM::create([
            'employee_id' => $this->employee->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        // Create Attendance Types
        $this->presentType = AttendanceType::firstOrCreate(['code' => 'present'], ['name' => 'Present', 'is_active' => true]);
        $this->blockedType = AttendanceType::firstOrCreate(['code' => 'punch_blocked'], ['name' => 'Punch Blocked', 'is_active' => true]);

        AttendanceLocationM::where('code', '!=', 'mumbai_office')->update(['is_default' => false]);
        AttendanceLocationM::updateOrCreate(
            ['code' => 'mumbai_office'],
            [
                'name' => 'Mumbai Office',
                'latitude' => 19.0760000,
                'longitude' => 72.8777000,
                'radius_meters' => 100,
                'is_default' => true,
                'is_active' => true,
            ]
        );

        // Create a default Shift
        $this->shift = AttendanceTime::firstOrCreate(
            ['code' => 'default_shift'],
            [
                'name' => 'Default Shift',
                'is_default' => true,
                'is_active' => true,
                'punch_allowed_from' => '08:00:00',
                'shift_start_time' => '09:00:00',
                'late_after_time' => '09:15:00',
                'warning_after_time' => '09:30:00',
                'block_after_time' => '10:00:00',
                'shift_end_time' => '18:00:00',
            ]
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset mocked time
        parent::tearDown();
    }

    public function test_today_status_when_no_attendance_exists_before_block_time(): void
    {
        // 9:30 AM is before block time (11:15 AM default policy in database)
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 9, 30, 0, 'Asia/Kolkata'));

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/hrms/attendance/today-status');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ui.is_blocked', false)
            ->assertJsonPath('data.ui.is_punch_blocked', false)
            ->assertJsonPath('data.ui.can_punch_in', true)
            ->assertJsonPath('data.ui.status_code', 'not_punched')
            ->assertJsonPath('data.office_location.enabled', true)
            ->assertJsonPath('data.office_location.name', 'Mumbai Office')
            ->assertJsonPath('data.office_location.radius_meters', 100);
    }

    public function test_today_status_when_no_attendance_exists_after_block_time(): void
    {
        // 12:00 PM is during shift hours before shift end (18:00 PM)
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 12, 0, 0, 'Asia/Kolkata'));

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/hrms/attendance/today-status');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ui.is_blocked', false)
            ->assertJsonPath('data.ui.is_punch_blocked', false)
            ->assertJsonPath('data.ui.can_punch_in', true)
            ->assertJsonPath('data.ui.status_code', 'not_punched');
    }

    public function test_today_status_after_admin_unlock_allows_punch_in_after_block_time(): void
    {
        // 12:00 PM is after block time (11:15 AM default policy in database)
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 12, 0, 0, 'Asia/Kolkata'));

        // Admin unlocks employee's attendance
        Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-20',
            'is_admin_unlocked' => true,
            'attendance_status' => 'unlocked',
            'attendance_type_id' => $this->presentType->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/hrms/attendance/today-status');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ui.is_blocked', false)
            ->assertJsonPath('data.ui.is_punch_blocked', false)
            ->assertJsonPath('data.ui.can_punch_in', true)
            ->assertJsonPath('data.ui.status_code', 'awaiting_punch_in');
    }

    public function test_punch_in_works_on_unlocked_attendance(): void
    {
        // 12:00 PM is after block time (11:15 AM default policy in database)
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 12, 0, 0, 'Asia/Kolkata'));

        // Admin unlocks employee's attendance
        $attendanceRecord = Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-20',
            'is_admin_unlocked' => true,
            'attendance_status' => 'unlocked',
            'attendance_type_id' => $this->presentType->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/hrms/attendance/punch-in', [
            'work_mode' => 'wfo',
            'latitude' => 19.0760,
            'longitude' => 72.8777,
            'note' => 'Punching after unlock',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Assert attendance record was updated properly
        $attendanceRecord->refresh();
        $this->assertEquals('present', $attendanceRecord->attendance_status);
        $this->assertEquals('12:00:00', Carbon::parse($attendanceRecord->punch_in_time)->format('H:i:s'));
        $this->assertEquals('wfo', $attendanceRecord->work_mode);
        $this->assertEquals(19.0760, (float)$attendanceRecord->punch_in_latitude);
        $this->assertEquals(72.8777, (float)$attendanceRecord->punch_in_longitude);
        $this->assertFalse((bool)$attendanceRecord->is_blocked);
        $this->assertFalse((bool)$attendanceRecord->is_punch_blocked);
    }

    public function test_unlocked_wfo_punch_in_outside_office_radius_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 21, 12, 0, 0, 'Asia/Kolkata'));

        Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-21',
            'is_admin_unlocked' => true,
            'attendance_status' => 'unlocked',
            'attendance_type_id' => $this->presentType->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/hrms/attendance/punch-in', [
            'work_mode' => 'wfo',
            'latitude' => 12.9716,
            'longitude' => 77.5946,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You are outside the allowed office location.')
            ->assertJsonPath('data.allowed_radius_meters', 100);
    }

    public function test_wfo_punch_in_requires_latitude_and_longitude(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 9, 0, 0, 'Asia/Kolkata'));

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/hrms/attendance/punch-in', [
            'work_mode' => 'wfo',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Location is required for WFO punch in.');
    }

    public function test_unlocked_wfh_punch_in_works_without_location(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 12, 0, 0, 'Asia/Kolkata'));

        $attendanceRecord = Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-22',
            'is_admin_unlocked' => true,
            'attendance_status' => 'unlocked',
            'attendance_type_id' => $this->presentType->id,
        ]);

        $this->employee->update(['work_mode' => 'wfh']);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/hrms/attendance/punch-in', [
            'work_mode' => 'wfh',
            'note' => 'WFH after unlock',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $attendanceRecord->refresh();
        $this->assertEquals('present', $attendanceRecord->attendance_status);
        $this->assertEquals('wfh', $attendanceRecord->work_mode);
        $this->assertNull($attendanceRecord->punch_in_latitude);
        $this->assertNull($attendanceRecord->punch_in_longitude);
    }

    public function test_wfo_punch_out_without_lat_lng_fails(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 18, 0, 0, 'Asia/Kolkata'));

        // Create punch-in record first
        Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-22',
            'punch_in_time' => '09:00:00',
            'work_mode' => 'wfo',
            'punch_in_latitude' => 19.0760,
            'punch_in_longitude' => 72.8777,
            'attendance_status' => 'present',
            'attendance_type_id' => $this->presentType->id,
            'is_locked' => false,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/hrms/attendance/punch-out', [
            'task_summary' => 'Finished work today.',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Location is required for WFO punch-out.');
    }

    public function test_wfo_punch_out_outside_radius_fails(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 18, 0, 0, 'Asia/Kolkata'));

        Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-22',
            'punch_in_time' => '09:00:00',
            'work_mode' => 'wfo',
            'punch_in_latitude' => 19.0760,
            'punch_in_longitude' => 72.8777,
            'attendance_status' => 'present',
            'attendance_type_id' => $this->presentType->id,
            'is_locked' => false,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/hrms/attendance/punch-out', [
            'task_summary' => 'Finished work today.',
            'latitude' => 12.9716,
            'longitude' => 77.5946,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You are outside the allowed office location.')
            ->assertJsonPath('data.allowed_radius_meters', 100);
    }

    public function test_wfo_punch_out_within_radius_succeeds(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 18, 0, 0, 'Asia/Kolkata'));

        Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-22',
            'punch_in_time' => '09:00:00',
            'work_mode' => 'wfo',
            'punch_in_latitude' => 19.0760,
            'punch_in_longitude' => 72.8777,
            'attendance_status' => 'present',
            'attendance_type_id' => $this->presentType->id,
            'is_locked' => false,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/hrms/attendance/punch-out', [
            'task_summary' => 'Finished work today.',
            'latitude' => 19.0760,
            'longitude' => 72.8777,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_wfh_punch_out_without_location_succeeds(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 18, 0, 0, 'Asia/Kolkata'));

        $att = Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-22',
            'punch_in_time' => '09:00:00',
            'work_mode' => 'wfh',
            'attendance_status' => 'present',
            'attendance_type_id' => $this->presentType->id,
            'is_locked' => false,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/hrms/attendance/punch-out', [
            'task_summary' => 'Finished work today.',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_unlocked_attendance_lifecycle(): void
    {
        // 1. Same-day unlocked awaiting punch in
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 12, 0, 0, 'Asia/Kolkata'));
        $attendance = Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-20',
            'is_admin_unlocked' => true,
            'attendance_status' => 'unlocked',
            'attendance_type_id' => $this->presentType->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/hrms/attendance/today-status');
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ui.status_code', 'awaiting_punch_in')
            ->assertJsonPath('data.status_code', 'awaiting_punch_in')
            ->assertJsonPath('data.status_name', 'Awaiting Punch In')
            ->assertJsonPath('data.ui.attendance_state', 'unlocked_waiting_punch_in')
            ->assertJsonPath('data.ui.can_punch_in', true)
            ->assertJsonPath('data.ui.next_action', 'punch_in');

        // 2. Next day / Day-end fallback should resolve to absent
        Carbon::setTestNow(Carbon::create(2026, 5, 21, 12, 0, 0, 'Asia/Kolkata'));
        $resolved = resolve(\App\Services\HRMS\Attendance\AttendanceS::class)->resolveFinalStatus($attendance);
        $this->assertEquals('absent', $resolved['status_code']);

        // 3. Same-day unlocked via status only (scenario like Row ID 153 with present type id 1)
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 12, 0, 0, 'Asia/Kolkata'));
        $attendance->refresh();
        $attendance->is_admin_unlocked = false;
        $attendance->attendance_status = 'unlocked';
        $resolvedStatus = resolve(\App\Services\HRMS\Attendance\AttendanceS::class)->resolveFinalStatus($attendance);
        $this->assertEquals('awaiting_punch_in', $resolvedStatus['status_code']);
        $this->assertEquals('Awaiting Punch In', $resolvedStatus['status_name']);
    }

    public function test_day_end_auto_close_marks_unresolved_blocked_as_absent_not_lwp(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 21, 0, 5, 0, 'Asia/Kolkata'));
        $absentType = AttendanceType::firstOrCreate(['code' => 'absent'], ['name' => 'Absent', 'is_active' => true]);

        $attendance = Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-20',
            'attendance_type_id' => $this->blockedType->id,
            'attendance_status' => 'punch_blocked',
            'is_punch_blocked' => true,
            'is_blocked' => true,
            'is_lwp' => true,
            'lwp_reason' => 'legacy wrong conversion',
            'is_locked' => true,
        ]);

        $counts = app(\App\Services\HRMS\Attendance\AttendanceS::class)
            ->autoCloseBlockedAttendance('2026-05-20');

        $attendance->refresh();
        $this->assertEquals(1, (int) ($counts['marked_absent'] ?? 0));
        $this->assertEquals('absent', $attendance->attendance_status);
        $this->assertEquals($absentType->id, $attendance->attendance_type_id);
        $this->assertFalse((bool) $attendance->is_lwp);
        $this->assertNull($attendance->lwp_reason);
        $this->assertFalse((bool) $attendance->is_punch_blocked);
        $this->assertFalse((bool) $attendance->is_blocked);
    }

    public function test_day_end_auto_close_skips_when_pending_regularization_exists(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 21, 0, 5, 0, 'Asia/Kolkata'));

        $attendance = Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-20',
            'attendance_type_id' => $this->blockedType->id,
            'attendance_status' => 'punch_blocked',
            'is_punch_blocked' => true,
            'is_blocked' => true,
            'is_locked' => true,
        ]);

        DB::table('attendance_regularizations')->insert([
            'employee_id' => $this->employee->id,
            'attendance_id' => $attendance->id,
            'request_type' => 'missed_punch_in',
            'reason' => 'Please regularize blocked day',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $counts = app(\App\Services\HRMS\Attendance\AttendanceS::class)
            ->autoCloseBlockedAttendance('2026-05-20');

        $attendance->refresh();
        $this->assertEquals(0, (int) ($counts['marked_absent'] ?? 0));
        $this->assertEquals(1, (int) ($counts['skipped_pending_regularization'] ?? 0));
        $this->assertEquals('punch_blocked', $attendance->attendance_status);
        $this->assertTrue((bool) $attendance->is_punch_blocked);
    }

    public function test_new_flow_punch_attempt_after_block_creates_violation_and_rejects(): void
    {
        // 12:00 PM is after block time (11:15 AM default policy in database)
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 12, 0, 0, 'Asia/Kolkata'));

        Sanctum::actingAs($this->user);

        // Attempt punch in
        $response = $this->postJson('/api/v1/hrms/attendance/punch-in', [
            'work_mode' => 'wfo',
            'latitude' => 19.0760000,
            'longitude' => 72.8777000,
            'device' => 'Mobile',
        ]);

        $blockTimeStr = \Carbon\Carbon::parse(\App\Models\HRMS\Attendance\AttendancePolicyRuleM::find(1)?->block_after_time ?? '11:15:00')->format('h:i A');
        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', "Punch in is blocked after {$blockTimeStr}. Please contact HR/Admin.");

        // Assert that a blocked_punch violation was created
        $this->assertDatabaseHas('attendance_violations', [
            'employee_id' => $this->employee->id,
            'type' => 'blocked_punch',
            'violation_date' => '2026-05-20',
            'policy_action' => 'blocked',
        ]);

        // Attempt again - should not create duplicate violation
        $response2 = $this->postJson('/api/v1/hrms/attendance/punch-in', [
            'work_mode' => 'wfo',
            'latitude' => 19.0760000,
            'longitude' => 72.8777000,
            'device' => 'Mobile',
        ]);

        $response2->assertStatus(422);
        $this->assertEquals(1, DB::table('attendance_violations')->where('employee_id', $this->employee->id)->where('type', 'blocked_punch')->count());
    }

    public function test_new_flow_unlock_resolves_violation_and_allows_punch_in(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 12, 0, 0, 'Asia/Kolkata'));

        // Create unresolved violation
        $violation = DB::table('attendance_violations')->insertGetId([
            'employee_id' => $this->employee->id,
            'violation_date' => '2026-05-20',
            'type' => 'blocked_punch',
            'minutes' => 0,
            'source' => 'mobile',
            'policy_action' => 'blocked',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Admin unlocks using violation ID prefix
        $admin = UserM::create([
            'name' => 'Admin User',
            'email' => 'admin_user_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => 1,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/hrms/attendance/unlock', [
            'attendance_id' => 'violation_' . $violation,
            'unlock_type' => 'unlock_only',
            'unlock_remarks' => 'Allow punch in',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Check violation is resolved
        $this->assertDatabaseHas('attendance_violations', [
            'id' => $violation,
            'policy_action' => 'resolved',
        ]);

        // Employee punches in successfully
        Sanctum::actingAs($this->user);
        $punchResponse = $this->postJson('/api/v1/hrms/attendance/punch-in', [
            'work_mode' => 'wfo',
            'latitude' => 19.0760000,
            'longitude' => 72.8777000,
            'device' => 'Mobile',
        ]);

        $punchResponse->assertOk()
            ->assertJsonPath('success', true);

        // Check attendance is created and linked to the violation
        $attendance = Attendance::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-05-20')->first();
        $this->assertNotNull($attendance);
        $this->assertTrue((bool) $attendance->is_admin_unlocked);
        $this->assertDatabaseHas('attendance_violations', [
            'id' => $violation,
            'attendance_id' => $attendance->id,
        ]);

        // Check unlock notification was generated
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'title' => 'Attendance Unlocked',
            'message' => "Your attendance has been unlocked.\nYou can now Punch In.",
            'type' => 'attendance_unlocked',
        ]);
    }

    public function test_new_flow_never_punched_in_marked_absent_lwp_at_day_end(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 21, 0, 5, 0, 'Asia/Kolkata'));

        // Run auto close blocked/absent attendance
        $counts = app(\App\Services\HRMS\Attendance\AttendanceS::class)
            ->autoCloseBlockedAttendance('2026-05-20');

        // Check count
        $this->assertGreaterThanOrEqual(1, (int) ($counts['marked_absent_never_punched'] ?? 0));

        // Check attendance created as absent + LWP
        $attendance = Attendance::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-05-20')->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('absent', $attendance->attendance_status);
        $this->assertTrue((bool) $attendance->is_lwp);
        $this->assertEquals('Absent (Never punched in)', $attendance->lwp_reason);

        // Ensure no violations were created
        $this->assertDatabaseMissing('attendance_violations', [
            'employee_id' => $this->employee->id,
            'violation_date' => '2026-05-20',
        ]);
    }

    public function test_new_flow_never_punched_in_marked_absent_lwp_immediately_after_shift_end(): void
    {
        // 19:05 PM is after shift end time (19:00:00 default in seeded policy rule) but before calendar day end (23:59:00)
        Carbon::setTestNow(Carbon::create(2026, 5, 20, 19, 5, 0, 'Asia/Kolkata'));

        // Run auto close blocked/absent attendance
        $counts = app(\App\Services\HRMS\Attendance\AttendanceS::class)
            ->autoCloseBlockedAttendance('2026-05-20');

        // Check count
        $this->assertGreaterThanOrEqual(1, (int) ($counts['marked_absent_never_punched'] ?? 0));

        // Check attendance created as absent + LWP
        $attendance = Attendance::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-05-20')->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('absent', $attendance->attendance_status);
        $this->assertTrue((bool) $attendance->is_lwp);
    }
}
