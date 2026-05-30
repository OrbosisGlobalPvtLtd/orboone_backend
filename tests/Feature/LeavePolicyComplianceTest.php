<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeavePolicyM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Leave\LeaveTypeM;
use App\Services\HRMS\Leave\LeaveCalculationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LeavePolicyComplianceTest extends TestCase
{
    use DatabaseTransactions;

    private EmployeeM $employee;
    private LeaveTypeM $paidType;
    private LeaveTypeM $sickType;

    protected function setUp(): void
    {
        parent::setUp();

        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $user = UserM::create([
            'name' => 'Leave Policy Employee',
            'email' => 'leave_policy_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$role->id]);

        $this->employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-LV-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'employee_stage' => 'permanent',
            'joining_date' => '2026-01-01',
            'confirmation_date' => '2026-01-01',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        LeavePolicyM::query()->update(['is_active' => 0]);
        LeavePolicyM::create([
            'policy_name' => 'Test Leave Policy',
            'annual_total_leaves' => 25,
            'annual_paid_leaves' => 18,
            'annual_sick_leaves' => 7,
            'monthly_leave_limit' => 2,
            'max_leave_at_once' => 15,
            'carry_forward_enabled' => false,
            'sandwich_enabled' => true,
            'weekoff_included_in_sandwich' => true,
            'holiday_included_in_sandwich' => true,
            'probation_leave_limit' => 1,
            'internship_leave_limit' => 1,
            'medical_certificate_after_days' => 2,
            'is_active' => true,
        ]);

        $this->paidType = LeaveTypeM::firstOrCreate(
            ['code' => 'paid_leave'],
            ['name' => 'Paid Leave', 'is_paid' => 1, 'is_active' => 1]
        );
        $this->sickType = LeaveTypeM::firstOrCreate(
            ['code' => 'sick_leave'],
            ['name' => 'Sick Leave', 'is_paid' => 1, 'is_sick' => 1, 'medical_certificate_after_days' => 2, 'is_active' => 1]
        );
    }

    public function test_monthly_cap_converts_extra_day_to_lwp(): void
    {
        $leaveRequest = LeaveRequestM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->employee->user_id,
            'leave_type_id' => $this->paidType->id,
            'start_date' => '2026-06-02',
            'end_date' => '2026-06-03',
            'status' => 'approved',
            'requested_days' => 2,
            'deducted_days' => 2,
            'paid_days' => 2,
        ]);

        DB::table('leave_request_dates')->insert([
            [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $this->employee->id,
                'leave_date' => '2026-06-02',
                'deduct_as_leave' => 1,
                'paid_day' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $this->employee->id,
                'leave_date' => '2026-06-03',
                'deduct_as_leave' => 1,
                'paid_day' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $result = app(LeaveCalculationService::class)->calculate($this->employee, $this->paidType, [
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-10',
        ]);

        $this->assertSame(1.0, (float) $result['lwp_days']);
        $this->assertSame(0.0, (float) $result['paid_days']);
    }

    public function test_sick_leave_requires_certificate_on_third_consecutive_day(): void
    {
        $leaveRequest = LeaveRequestM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->employee->user_id,
            'leave_type_id' => $this->sickType->id,
            'start_date' => '2026-06-08',
            'end_date' => '2026-06-09',
            'status' => 'approved',
            'requested_days' => 2,
            'deducted_days' => 2,
            'sick_days' => 2,
        ]);

        DB::table('leave_request_dates')->insert([
            [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $this->employee->id,
                'leave_date' => '2026-06-08',
                'deduct_as_leave' => 1,
                'sick_day' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $this->employee->id,
                'leave_date' => '2026-06-09',
                'deduct_as_leave' => 1,
                'sick_day' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->expectException(ValidationException::class);
        app(LeaveCalculationService::class)->calculate($this->employee, $this->sickType, [
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-10',
        ]);
    }

    public function test_sandwich_leave_marks_weekend_between_friday_and_monday(): void
    {
        $result = app(LeaveCalculationService::class)->calculate($this->employee, $this->paidType, [
            'start_date' => '2026-06-12',
            'end_date' => '2026-06-15',
        ]);

        $this->assertTrue((bool) $result['sandwich_applied']);
    }

    public function test_probation_limit_overflow_becomes_lwp(): void
    {
        $this->employee->update([
            'employee_stage' => 'probation',
            'probation_start_date' => '2026-01-01',
        ]);

        LeaveAllocationM::updateOrCreate(
            ['employee_id' => $this->employee->id, 'year' => 2026, 'employment_stage' => 'probation'],
            [
                'total_allocated' => 1,
                'paid_allocated' => 1,
                'sick_allocated' => 0,
                'comp_off_allocated' => 0,
                'total_used' => 1,
                'paid_used' => 1,
                'sick_used' => 0,
                'comp_off_used' => 0,
                'lwp_used' => 0,
                'total_remaining' => 0,
                'paid_remaining' => 0,
                'sick_remaining' => 0,
                'comp_off_remaining' => 0,
            ]
        );

        $result = app(LeaveCalculationService::class)->calculate($this->employee, $this->paidType, [
            'start_date' => '2026-07-02',
            'end_date' => '2026-07-02',
        ]);

        $this->assertSame(1.0, (float) $result['lwp_days']);
    }

    public function test_internship_limit_overflow_becomes_lwp(): void
    {
        $this->employee->update([
            'employee_stage' => 'internship',
            'internship_start_date' => '2026-01-01',
        ]);

        LeaveAllocationM::updateOrCreate(
            ['employee_id' => $this->employee->id, 'year' => 2026, 'employment_stage' => 'internship'],
            [
                'total_allocated' => 1,
                'paid_allocated' => 1,
                'sick_allocated' => 0,
                'comp_off_allocated' => 0,
                'total_used' => 1,
                'paid_used' => 1,
                'sick_used' => 0,
                'comp_off_used' => 0,
                'lwp_used' => 0,
                'total_remaining' => 0,
                'paid_remaining' => 0,
                'sick_remaining' => 0,
                'comp_off_remaining' => 0,
            ]
        );

        $result = app(LeaveCalculationService::class)->calculate($this->employee, $this->paidType, [
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-03',
        ]);

        $this->assertSame(1.0, (float) $result['lwp_days']);
    }
}

