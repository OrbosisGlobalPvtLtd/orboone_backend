<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\AttendanceTypeM;
use App\Models\HRMS\Attendance\WfhRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\WfhRequestService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WfhPolicyRegressionTest extends TestCase
{
    use DatabaseTransactions;

    private EmployeeM $employee;
    private WfhRequestService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $user = UserM::create([
            'name' => 'WFH Policy Employee',
            'email' => 'wfh_policy_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$role->id]);

        $this->employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-WFH-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        $this->service = app(WfhRequestService::class);
    }

    public function test_quota_blocks_third_working_day_wfh_request(): void
    {
        WfhRequestM::create([
            'employee_id' => $this->employee->id,
            'request_date' => '2026-06-02',
            'request_type' => 'working_day_wfh',
            'reason_category' => 'normal',
            'reason' => 'WFH 1',
            'status' => 'approved',
            'counts_in_monthly_quota' => 1,
            'payroll_impact' => 'none',
        ]);
        WfhRequestM::create([
            'employee_id' => $this->employee->id,
            'request_date' => '2026-06-03',
            'request_type' => 'working_day_wfh',
            'reason_category' => 'normal',
            'reason' => 'WFH 2',
            'status' => 'approved',
            'counts_in_monthly_quota' => 1,
            'payroll_impact' => 'none',
        ]);

        $this->expectException(ValidationException::class);
        $this->service->apply($this->employee, [
            'request_date' => '2026-06-04',
            'reason_category' => 'normal',
            'reason' => 'WFH 3',
        ]);
    }

    public function test_internet_issue_approved_wfh_does_not_auto_convert_to_lwp(): void
    {
        DB::table('settings')->updateOrInsert(['key' => 'wfh_internet_issue_to_lwp'], [
            'value' => '1',
            'group' => 'wfh_policy',
            'type' => 'boolean',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $wfh = WfhRequestM::create([
            'employee_id' => $this->employee->id,
            'request_date' => '2026-06-06',
            'request_type' => 'working_day_wfh',
            'reason_category' => 'internet_issue',
            'reason' => 'internet down',
            'status' => 'approved',
            'counts_in_monthly_quota' => 1,
            'payroll_impact' => 'none',
        ]);

        $presentType = AttendanceTypeM::firstOrCreate(['code' => 'present'], ['name' => 'Present', 'is_active' => 1]);
        $attendance = AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->employee->user_id,
            'attendance_date' => '2026-06-06',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'work_mode' => 'wfh',
            'is_lwp' => false,
        ]);

        $this->service->applyLwpConversionIfRequired($attendance);

        $attendance->refresh();
        $wfh->refresh();
        $this->assertFalse((bool) $attendance->is_lwp);
        $this->assertSame('present', $attendance->attendance_status);
        $this->assertNull($attendance->lwp_reason);
        $this->assertSame('none', $wfh->payroll_impact);
    }

    public function test_mark_lwp_action_converts_attendance_and_payroll_impact(): void
    {
        $wfh = WfhRequestM::create([
            'employee_id' => $this->employee->id,
            'request_date' => '2026-06-07',
            'request_type' => 'working_day_wfh',
            'reason_category' => 'normal',
            'reason' => 'approved wfh',
            'status' => 'approved',
            'counts_in_monthly_quota' => 1,
            'payroll_impact' => 'none',
        ]);

        $presentType = AttendanceTypeM::firstOrCreate(['code' => 'present'], ['name' => 'Present', 'is_active' => 1]);
        $attendance = AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->employee->user_id,
            'attendance_date' => '2026-06-07',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'work_mode' => 'wfh',
            'is_lwp' => false,
        ]);

        $this->service->markAsLwp($wfh, (int) $this->employee->user_id, 'Manual review marked as LWP', 'No valid work submitted');

        $attendance->refresh();
        $wfh->refresh();
        $this->assertTrue((bool) $attendance->is_lwp);
        $this->assertSame('lwp', $attendance->attendance_status);
        $this->assertSame('Manual review marked as LWP', $attendance->lwp_reason);
        $this->assertSame('lwp', $wfh->payroll_impact);
    }
}
