<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\AttendanceTypeM;
use App\Models\HRMS\Attendance\AttendanceViolationM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IndependentViolationEngineTest extends TestCase
{
    use DatabaseTransactions;

    protected EmployeeM $employee;
    protected UserM $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserM::create([
            'name' => 'Test User',
            'email' => 'test_violation_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $this->employee = EmployeeM::create([
            'user_id' => $this->user->id,
            'employee_code' => 'EMP-TEST-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        AttendanceTypeM::firstOrCreate(['code' => 'present'], ['name' => 'Present', 'is_active' => 1]);
        AttendanceTypeM::firstOrCreate(['code' => 'half_day'], ['name' => 'Half Day', 'is_active' => 1]);
        AttendanceTypeM::firstOrCreate(['code' => 'lwp'], ['name' => 'LWP', 'is_active' => 1]);
        AttendanceTypeM::firstOrCreate(['code' => 'missed_punch'], ['name' => 'Missed Punch', 'is_active' => 1]);
        AttendanceTypeM::firstOrCreate(['code' => 'pending_hr'], ['name' => 'Pending HR', 'is_active' => 1]);
        AttendanceTypeM::firstOrCreate(['code' => 'absent'], ['name' => 'Absent', 'is_active' => 1]);
    }

    public function test_late_plus_late_triggers_no_penalty(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();
        $att1 = AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->employee->user_id,
            'attendance_date' => '2026-06-01',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'punch_in_time' => '10:30:00',
            'punch_out_time' => '19:00:00',
            'total_work_minutes' => 450,
            'is_late' => true,
        ]);

        $att2 = AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->employee->user_id,
            'attendance_date' => '2026-06-02',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'punch_in_time' => '10:35:00',
            'punch_out_time' => '19:00:00',
            'total_work_minutes' => 445,
            'is_late' => true,
        ]);

        AttendanceViolationM::create(['employee_id' => $this->employee->id, 'attendance_id' => $att1->id, 'violation_date' => '2026-06-01', 'type' => 'late_login']);
        AttendanceViolationM::create(['employee_id' => $this->employee->id, 'attendance_id' => $att2->id, 'violation_date' => '2026-06-02', 'type' => 'late_login']);

        $summary = app(AttendanceS::class)->getEmployeeViolationSummary($this->employee, '2026-06-01');
        $this->assertEquals(2, $summary['discipline']['count']);
        $this->assertFalse((bool) $att1->refresh()->is_half_day);
        $this->assertFalse((bool) $att2->refresh()->is_half_day);
    }

    public function test_third_discipline_violation_triggers_half_day_and_resets_cycle(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();

        $att1 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-01', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:30:00', 'punch_out_time' => '19:00:00', 'total_work_minutes' => 450, 'is_late' => true]);
        $att2 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-05', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:35:00', 'punch_out_time' => '19:00:00', 'total_work_minutes' => 445, 'is_late' => true]);
        $att3 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-10', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:00:00', 'punch_out_time' => '18:00:00', 'total_work_minutes' => 420, 'is_early_out' => true]);

        $service = app(AttendanceS::class);
        $service->syncAttendanceViolations($att1);
        $service->syncAttendanceViolations($att2);
        $service->syncAttendanceViolations($att3);

        $service->calculateWorkingHours($att3);

        $att3->refresh();
        $this->assertTrue((bool) $att3->is_half_day);
        $this->assertSame('half_day', $att3->attendance_status);
        $this->assertStringContainsString('Half Day applied because monthly Attendance Discipline violations reached policy limit', $att3->half_day_reason);

        // Verify participating violations are consumed
        $consumedCount = AttendanceViolationM::where('employee_id', $this->employee->id)->where('is_consumed', true)->count();
        $this->assertEquals(3, $consumedCount);

        // Next violation starts a new cycle
        $summary = $service->getEmployeeViolationSummary($this->employee, '2026-06-10');
        $this->assertEquals(0, $summary['discipline']['count']);

        $att4 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-15', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:40:00', 'punch_out_time' => '19:40:00', 'total_work_minutes' => 480, 'is_late' => true, 'is_early_out' => false]);
        $service->syncAttendanceViolations($att4);
        $service->calculateWorkingHours($att4);

        $summaryAfter4 = $service->getEmployeeViolationSummary($this->employee, '2026-06-15');
        $this->assertEquals(1, $summaryAfter4['discipline']['count']);
    }

    public function test_missed_punch_counter_is_independent_and_resets_on_third(): void
    {
        Carbon::setTestNow('2026-06-06 10:00:00');
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();

        // 2 Late logins
        $att1 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-01', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:30:00', 'punch_out_time' => '19:00:00', 'total_work_minutes' => 450, 'is_late' => true]);
        $att2 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-02', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:30:00', 'punch_out_time' => '19:00:00', 'total_work_minutes' => 450, 'is_late' => true]);

        $service = app(AttendanceS::class);
        $service->syncAttendanceViolations($att1);
        $service->syncAttendanceViolations($att2);

        // 1st & 2nd Missed Punch
        $attM1 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-03', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:00:00']);
        $attM2 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-04', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:00:00']);

        $service->processMissedPunches('2026-06-03');
        $service->processMissedPunches('2026-06-04');

        $summary = $service->getEmployeeViolationSummary($this->employee, '2026-06-04');
        $this->assertEquals(2, $summary['discipline']['count']);
        $this->assertEquals(2, $summary['missed_punch']['count']);

        // Neither penalty is applied yet
        $this->assertFalse((bool) $attM2->refresh()->is_lwp);

        // 3rd Missed Punch
        $attM3 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-05', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:00:00']);
        $service->processMissedPunches('2026-06-05');

        $attM3->refresh();
        $this->assertTrue((bool) $attM3->is_lwp);
        $this->assertSame('lwp', $attM3->attendance_status);
        $this->assertStringContainsString('LWP applied because monthly Missed Punch violations reached policy limit', $attM3->lwp_reason);

        // Missed punch counter resets to 0 after LWP
        $summaryAfterLwp = $service->getEmployeeViolationSummary($this->employee, '2026-06-05');
        $this->assertEquals(0, $summaryAfterLwp['missed_punch']['count']);
        // Discipline counter remains 2
        $this->assertEquals(2, $summaryAfterLwp['discipline']['count']);
    }

    public function test_rebuild_cycles_reverses_penalty_when_violation_resolved(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();

        $att1 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-01', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:30:00', 'punch_out_time' => '19:00:00', 'total_work_minutes' => 450, 'is_late' => true]);
        $att2 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-05', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:35:00', 'punch_out_time' => '19:00:00', 'total_work_minutes' => 445, 'is_late' => true]);
        $att3 = AttendanceM::create(['employee_id' => $this->employee->id, 'user_id' => $this->employee->user_id, 'attendance_date' => '2026-06-10', 'attendance_type_id' => $presentType->id, 'attendance_status' => 'present', 'punch_in_time' => '10:00:00', 'punch_out_time' => '18:00:00', 'total_work_minutes' => 420, 'is_early_out' => true]);

        $service = app(AttendanceS::class);
        $service->syncAttendanceViolations($att1);
        $service->syncAttendanceViolations($att2);
        $service->syncAttendanceViolations($att3);
        $service->calculateWorkingHours($att3);

        $this->assertTrue((bool) $att3->refresh()->is_half_day);

        // HR approves regularization for att1 (late exempted)
        $v1 = AttendanceViolationM::where('attendance_id', $att1->id)->where('type', 'late_login')->first();
        $v1->update(['policy_action' => 'resolved']);
        $att1->update(['is_late' => false, 'late_minutes' => 0, 'is_late_exempted' => true]);

        // Rebuild cycles
        $service->rebuildEmployeeViolationCycles($this->employee->id, '2026-06-01');

        // Half day penalty on att3 is now reversed!
        $att3->refresh();
        $this->assertFalse((bool) $att3->is_half_day);
        $this->assertSame('present', $att3->attendance_status);
        $this->assertNull($att3->half_day_reason);

        // Unconsumed count is now 2 (att2 + att3)
        $summary = $service->getEmployeeViolationSummary($this->employee, '2026-06-10');
        $this->assertEquals(2, $summary['discipline']['count']);
    }
}
