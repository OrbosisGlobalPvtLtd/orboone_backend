<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\AttendanceTypeM;
use App\Models\HRMS\Attendance\AttendanceViolationM;
use App\Models\HRMS\Attendance\WfhRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendancePolicyParityTest extends TestCase
{
    use DatabaseTransactions;

    private EmployeeM $employee;
    private UserM $user;

    protected function setUp(): void
    {
        parent::setUp();

        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $this->user = UserM::create([
            'name' => 'Policy Parity Employee',
            'email' => 'policy_parity_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->user->roles()->sync([$role->id]);

        $this->employee = EmployeeM::create([
            'user_id' => $this->user->id,
            'employee_code' => 'EMP-PP-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        AttendanceTypeM::firstOrCreate(['code' => 'present'], ['name' => 'Present', 'is_active' => 1]);
        AttendanceTypeM::firstOrCreate(['code' => 'half_day'], ['name' => 'Half Day', 'is_active' => 1]);
        AttendanceTypeM::firstOrCreate(['code' => 'lwp'], ['name' => 'LWP', 'is_active' => 1]);
        AttendanceTypeM::firstOrCreate(['code' => 'pending_hr'], ['name' => 'Pending HR', 'is_active' => 1]);
    }

    public function test_three_monthly_late_early_violations_trigger_half_day_impact(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();
        $attendance = AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->employee->user_id,
            'attendance_date' => '2026-06-20',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'is_late' => false,
            'is_half_day' => false,
            'is_lwp' => false,
        ]);

        AttendanceViolationM::create([
            'employee_id' => $this->employee->id,
            'attendance_id' => $attendance->id,
            'violation_date' => '2026-06-05',
            'type' => 'late_login',
            'source' => 'test',
        ]);
        AttendanceViolationM::create([
            'employee_id' => $this->employee->id,
            'attendance_id' => $attendance->id,
            'violation_date' => '2026-06-10',
            'type' => 'late_login',
            'source' => 'test',
        ]);
        AttendanceViolationM::create([
            'employee_id' => $this->employee->id,
            'attendance_id' => $attendance->id,
            'violation_date' => '2026-06-20',
            'type' => 'early_logout',
            'source' => 'test',
        ]);

        $service = app(AttendanceS::class);
        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('applyCombinedViolationHalfDay');
        $method->setAccessible(true);
        $method->invoke($service, $attendance, '2026-06-20');

        $attendance->refresh();
        $this->assertTrue((bool) $attendance->is_half_day);
        $this->assertSame('half_day', $attendance->attendance_status);
    }

    public function test_first_and_second_missed_punch_are_warning_only_third_is_lwp(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();
        $lwpType = AttendanceTypeM::where('code', 'lwp')->firstOrFail();

        AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->employee->user_id,
            'attendance_date' => '2026-06-01',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'punch_in_time' => '10:00:00',
        ]);
        AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->employee->user_id,
            'attendance_date' => '2026-06-02',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'punch_in_time' => '10:00:00',
        ]);
        AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->employee->user_id,
            'attendance_date' => '2026-06-03',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'punch_in_time' => '10:00:00',
        ]);

        $service = app(AttendanceS::class);
        Carbon::setTestNow(Carbon::create(2026, 6, 1, 23, 59, 0, 'Asia/Kolkata'));
        $service->processMissedPunches('2026-06-01');
        Carbon::setTestNow(Carbon::create(2026, 6, 2, 23, 59, 0, 'Asia/Kolkata'));
        $service->processMissedPunches('2026-06-02');
        Carbon::setTestNow(Carbon::create(2026, 6, 3, 23, 59, 0, 'Asia/Kolkata'));
        $service->processMissedPunches('2026-06-03');

        $d1 = AttendanceM::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-06-01')->firstOrFail();
        $d2 = AttendanceM::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-06-02')->firstOrFail();
        $d3 = AttendanceM::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-06-03')->firstOrFail();

        $this->assertSame('pending_hr', $d1->attendance_status);
        $this->assertFalse((bool) $d1->is_lwp);
        $this->assertSame('pending_hr', $d2->attendance_status);
        $this->assertFalse((bool) $d2->is_lwp);
        $this->assertSame('lwp', $d3->attendance_status);
        $this->assertTrue((bool) $d3->is_lwp);
        $this->assertSame($lwpType->id, (int) $d3->attendance_type_id);
        Carbon::setTestNow();
    }

    public function test_employee_wfh_history_is_scoped_to_own_records_only(): void
    {
        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $otherUser = UserM::create([
            'name' => 'Other WFH User',
            'email' => 'other_wfh_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $otherUser->roles()->sync([$role->id]);

        $otherEmployee = EmployeeM::create([
            'user_id' => $otherUser->id,
            'employee_code' => 'EMP-OTH-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        WfhRequestM::create([
            'employee_id' => $this->employee->id,
            'request_date' => '2026-06-11',
            'request_type' => 'working_day_wfh',
            'reason_category' => 'normal',
            'status' => 'approved',
            'counts_in_monthly_quota' => true,
            'payroll_impact' => 'none',
        ]);

        WfhRequestM::create([
            'employee_id' => $otherEmployee->id,
            'request_date' => '2026-06-12',
            'request_type' => 'working_day_wfh',
            'reason_category' => 'normal',
            'status' => 'approved',
            'counts_in_monthly_quota' => true,
            'payroll_impact' => 'none',
        ]);

        Sanctum::actingAs($this->user);
        $response = $this->getJson('/api/v1/hrms/wfh/history');
        $response->assertOk();

        $records = collect($response->json('data.records'));
        $this->assertTrue($records->contains(fn ($row) => (int) ($row['employee_id'] ?? 0) === (int) $this->employee->id));
        $this->assertFalse($records->contains(fn ($row) => (int) ($row['employee_id'] ?? 0) === (int) $otherEmployee->id));
    }
}
