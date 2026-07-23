<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\WfhRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\WfhRequestService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WfhMultiDayRangeTest extends TestCase
{
    use DatabaseTransactions;

    private EmployeeM $employee;
    private WfhRequestService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $user = UserM::create([
            'name' => 'Multi-Day WFH Employee',
            'email' => 'wfh_multiday_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$role->id]);

        $this->employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-RANGE-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        \App\Models\HRMS\Employee\EmployeeProfileM::create([
            'employee_id' => $this->employee->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        $this->service = app(WfhRequestService::class);
    }

    public function test_calculate_range_stats_computes_working_days_and_weekoffs(): void
    {
        // Date range from 2026-07-20 (Monday) to 2026-07-26 (Sunday) -> 7 total days, 5 working days, 2 weekoffs
        $stats = $this->service->calculateRangeStats($this->employee, '2026-07-20', '2026-07-26');

        $this->assertSame('2026-07-20', $stats['from_date']);
        $this->assertSame('2026-07-26', $stats['to_date']);
        $this->assertSame(7, $stats['total_days']);
        $this->assertSame(5, $stats['working_days']);
        $this->assertSame(2, $stats['weekoff_days']);
        $this->assertSame(0, $stats['holiday_days']);
        $this->assertSame(5, $stats['actual_wfh_days']);
    }

    public function test_apply_creates_single_wfh_application_record(): void
    {
        $payload = [
            'from_date' => '2026-07-20',
            'to_date' => '2026-07-21',
            'reason_category' => 'normal',
            'reason' => 'Need 2 days WFH for home maintenance',
        ];

        $firstRow = $this->service->apply($this->employee, $payload);

        $this->assertNotNull($firstRow->batch_id);
        $this->assertSame('2026-07-20', Carbon::parse($firstRow->from_date)->format('Y-m-d'));
        $this->assertSame('2026-07-21', Carbon::parse($firstRow->to_date)->format('Y-m-d'));
        $this->assertSame(2, (int) $firstRow->working_days);
        $this->assertSame('pending', $firstRow->status);

        $records = WfhRequestM::where('employee_id', $this->employee->id)->get();
        // Single application record per WFH application
        $this->assertCount(1, $records);
    }

    public function test_validate_range_prevents_overlap(): void
    {
        // Apply WFH for 2026-07-20 to 2026-07-21 (2 working days, within quota limit of 2)
        $this->service->apply($this->employee, [
            'from_date' => '2026-07-20',
            'to_date' => '2026-07-21',
            'reason_category' => 'normal',
            'reason' => 'First range',
        ]);

        // Attempt overlapping request for 2026-07-21 to 2026-07-21
        $this->expectException(ValidationException::class);
        $this->service->apply($this->employee, [
            'from_date' => '2026-07-21',
            'to_date' => '2026-07-21',
            'reason_category' => 'normal',
            'reason' => 'Overlapping range',
        ]);
    }

    public function test_approval_approves_application_and_generates_attendance(): void
    {
        $application = $this->service->apply($this->employee, [
            'from_date' => '2026-07-24',
            'to_date' => '2026-07-26',
            'reason_category' => 'normal',
            'reason' => 'Applying weekend border WFH',
        ]);

        $this->service->approve($application, (int) $this->employee->user_id);

        $application->refresh();
        $this->assertSame('approved', $application->status);

        // Verify approvedForDate service method works for dates in range
        $this->assertNotNull($this->service->approvedForDate($this->employee->id, '2026-07-24'));
        $this->assertNotNull($this->service->approvedForDate($this->employee->id, '2026-07-25'));
        $this->assertNotNull($this->service->approvedForDate($this->employee->id, '2026-07-26'));
    }

    public function test_hr_admin_web_approval_updates_status_and_generates_attendance(): void
    {
        $adminUser = UserM::create([
            'name' => 'HR Admin Tester',
            'email' => 'hr_admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => 1,
            'is_active' => 1,
        ]);

        $application = $this->service->apply($this->employee, [
            'from_date' => '2026-07-21',
            'to_date' => '2026-07-22',
            'reason_category' => 'normal',
            'reason' => 'Testing HR Admin approval flow',
        ]);

        $this->assertSame('pending', $application->status);

        $response = $this->actingAs($adminUser)->post(route('hrms.attendance.wfh.approve', ['id' => $application->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $application->refresh();
        $this->assertSame('approved', $application->status);
        $this->assertNotNull($application->hr_approved_at);
        $this->assertSame($adminUser->id, (int) $application->hr_approved_by);

        // Verify attendance records were NOT generated for working days upon approval
        $this->assertDatabaseMissing('attendances', [
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-07-21',
            'work_mode' => 'wfh',
        ]);
        $this->assertDatabaseMissing('attendances', [
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-07-22',
            'work_mode' => 'wfh',
        ]);

        // Punch in on 2026-07-21 - should succeed and create attendance
        $attendanceService = app(\App\Services\HRMS\Attendance\AttendanceS::class);
        $result = $attendanceService->processPunchIn((int) $this->employee->user_id, 'wfh', null, [], '2026-07-21 09:10:00', null, true);
        $this->assertTrue((bool) ($result['status'] ?? false));

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-07-21',
            'work_mode' => 'wfh',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employee->user_id,
            'title' => 'WFH Request Approved',
            'message' => "Your Work From Home request has been approved.\nYou can mark attendance remotely during the approved period.",
            'type' => 'wfh_approved',
        ]);
    }

    public function test_hr_admin_can_override_quota_with_audit_log_while_manager_is_blocked(): void
    {
        $adminUser = UserM::create([
            'name' => 'Super HR Admin',
            'email' => 'hr_super_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => 1,
            'is_active' => 1,
        ]);

        $managerRole = \App\Models\Core\RoleM::firstOrCreate(['slug' => 'manager'], ['name' => 'Manager']);
        $managerUser = UserM::create([
            'name' => 'Team Manager',
            'email' => 'manager_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $managerRole->id,
            'is_active' => 1,
        ]);

        // Submit WFH request for 5 working days (exceeds default monthly quota of 2 days)
        $application = $this->service->apply($this->employee, [
            'from_date' => '2026-07-20',
            'to_date' => '2026-07-24',
            'reason_category' => 'normal',
            'reason' => 'Requesting 5 days WFH',
        ]);

        $this->assertSame(5, (int) $application->working_days);

        // 1. Manager without override permission is blocked
        try {
            $this->service->approve($application, (int) $managerUser->id, null, false);
            $this->fail('Expected ValidationException was not thrown for manager approval');
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first();
            $this->assertStringContainsString('Monthly WFH limit exceeded', (string) $msg);
        }

        // 2. HR Admin with override authority can approve
        $approvedApp = $this->service->approve($application, (int) $adminUser->id, null, true);

        $this->assertSame('approved', $approvedApp->status);
        $this->assertStringContainsString('Approved with Quota Override', (string) $approvedApp->remarks);
        $this->assertStringContainsString('User #' . $adminUser->id, (string) $approvedApp->remarks);

        // Attendance records were NOT generated for all 5 working days upon approval
        $this->assertDatabaseMissing('attendances', [
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-07-20',
            'work_mode' => 'wfh',
        ]);
        $this->assertDatabaseMissing('attendances', [
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-07-24',
            'work_mode' => 'wfh',
        ]);

        // Punch in on 2026-07-20 - should succeed and create attendance
        $attendanceService = app(\App\Services\HRMS\Attendance\AttendanceS::class);
        $result = $attendanceService->processPunchIn((int) $this->employee->user_id, 'wfh', null, [], '2026-07-20 09:10:00', null, true);
        $this->assertTrue((bool) ($result['status'] ?? false));

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $this->employee->id,
            'attendance_date' => '2026-07-20',
            'work_mode' => 'wfh',
        ]);
    }

    public function test_wfo_employee_unapproved_wfh_attendance_is_blocked(): void
    {
        $attendanceService = app(\App\Services\HRMS\Attendance\AttendanceS::class);

        // Employee work_mode = wfo, no approved WFH request exists
        $result = $attendanceService->processPunchIn((int) $this->employee->user_id, 'wfh', null, [], null, null, true);

        $this->assertSame('error', $result['status']);
        $this->assertStringContainsString('no approved WFH request exists for today', $result['message']);
    }

    public function test_wfo_employee_approved_wfh_range_allows_attendance_and_blocks_after_expiry(): void
    {
        // Apply WFH range for 2026-07-21 to 2026-07-22 (Tue & Wed)
        $application = $this->service->apply($this->employee, [
            'from_date' => '2026-07-21',
            'to_date' => '2026-07-22',
            'reason_category' => 'normal',
            'reason' => 'Range WFH test',
        ]);

        $this->service->approve($application, 1, null, true);

        // 1. Within range (2026-07-21) -> Approved WFH request exists -> Allowed
        $approvedResult = $this->service->approvedForDate((int) $this->employee->id, '2026-07-21');
        $this->assertNotNull($approvedResult);

        // 2. Outside range (2026-08-01) -> Expired / No WFH request -> Blocked
        $expiredResult = $this->service->approvedForDate((int) $this->employee->id, '2026-08-01');
        $this->assertNull($expiredResult);
    }

    public function test_permanent_wfh_employee_allows_wfh_attendance_without_request_and_blocks_request_apply(): void
    {
        $user = UserM::create([
            'name' => 'Permanent WFH Employee',
            'email' => 'pwfh_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => 7,
            'is_active' => 1,
        ]);

        $pWfhEmployee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-PWFH-' . rand(100, 999),
            'work_mode' => 'wfh',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'is_active' => 1,
            'joining_date' => '2026-01-01',
        ]);

        \App\Models\HRMS\Employee\EmployeeProfileM::create([
            'employee_id' => $pWfhEmployee->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        $this->assertTrue($pWfhEmployee->isPermanentWfh());
        $this->assertFalse($pWfhEmployee->isWfo());

        // 1. Permanent WFH employee applying for WFH request is blocked with clear message
        try {
            $this->service->apply($pWfhEmployee, [
                'from_date' => '2026-07-21',
                'to_date' => '2026-07-22',
                'reason_category' => 'normal',
                'reason' => 'Permanent employee trying to apply',
            ]);
            $this->fail('Expected ValidationException was not thrown for Permanent WFH apply');
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first();
            $this->assertStringContainsString('Permanent Work From Home employee', (string) $msg);
        }

        // 2. Permanent WFH employee marking WFH attendance succeeds without any request
        $pWfhEmployee->refresh();
        $attendanceService = app(\App\Services\HRMS\Attendance\AttendanceS::class);
        $result = $attendanceService->processPunchIn((int) $pWfhEmployee->user_id, 'wfh', null, [], '2026-07-21 09:10:00', null, true);

        $this->assertTrue((bool) ($result['status'] ?? false));
        $this->assertSame('wfh', $result['data']->work_mode);
    }

    public function test_wfh_rejection_sends_notification(): void
    {
        $application = $this->service->apply($this->employee, [
            'from_date' => '2026-07-24',
            'to_date' => '2026-07-25',
            'reason_category' => 'normal',
            'reason' => 'Need WFH',
        ]);

        $this->service->reject($application, 1, 'Insufficient reason');

        $this->assertSame('rejected', $application->fresh()->status);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employee->user_id,
            'title' => 'WFH Request Rejected',
            'message' => "Your Work From Home request has been rejected.\nPlease contact HR for more details.",
            'type' => 'wfh_rejected',
        ]);
    }
}
