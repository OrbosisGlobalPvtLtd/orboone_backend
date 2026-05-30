<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeavePolicyM;
use App\Models\HRMS\Leave\LeaveTypeM;
use App\Services\HRMS\Leave\LeaveAllocationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LeaveAllocationStageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        LeavePolicyM::query()->update(['is_active' => 0]);
        LeavePolicyM::create([
            'policy_name' => 'Stage Test Policy',
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
            'rounding_method' => 'nearest',
            'is_active' => true,
        ]);

        LeaveTypeM::firstOrCreate(['code' => 'paid_leave'], ['name' => 'Paid Leave', 'is_paid' => 1, 'is_active' => 1]);
        LeaveTypeM::firstOrCreate(['code' => 'sick_leave'], ['name' => 'Sick Leave', 'is_paid' => 1, 'is_sick' => 1, 'is_active' => 1]);
    }

    public function test_permanent_proration_uses_fixed_integer_mapping_for_june(): void
    {
        $employee = $this->makeEmployee('permanent', '2026-06-15');
        $employee->confirmation_date = '2026-06-15';
        $employee->save();

        $allocation = app(LeaveAllocationService::class)->generateForEmployee(
            $employee,
            2026,
            null,
            'permanent',
            Carbon::parse('2026-06-15', 'Asia/Kolkata')
        );

        $this->assertSame(15.0, (float) $allocation->total_allocated);
        $this->assertSame(11.0, (float) $allocation->paid_allocated);
        $this->assertSame(4.0, (float) $allocation->sick_allocated);
    }

    public function test_permanent_proration_uses_fixed_integer_mapping_for_september_and_december(): void
    {
        $employeeSept = $this->makeEmployee('permanent', '2026-09-02');
        $employeeSept->confirmation_date = '2026-09-02';
        $employeeSept->save();

        $sept = app(LeaveAllocationService::class)->generateForEmployee(
            $employeeSept,
            2026,
            null,
            'permanent',
            Carbon::parse('2026-09-02', 'Asia/Kolkata')
        );
        $this->assertSame(8.0, (float) $sept->total_allocated);
        $this->assertSame(6.0, (float) $sept->paid_allocated);
        $this->assertSame(2.0, (float) $sept->sick_allocated);

        $employeeDec = $this->makeEmployee('permanent', '2026-12-01');
        $dec = app(LeaveAllocationService::class)->generateForEmployee(
            $employeeDec,
            2026,
            null,
            'permanent',
            Carbon::parse('2026-12-01', 'Asia/Kolkata')
        );
        $this->assertSame(2.0, (float) $dec->total_allocated);
        $this->assertSame(1.0, (float) $dec->paid_allocated);
        $this->assertSame(1.0, (float) $dec->sick_allocated);
    }

    public function test_stage_allocation_is_idempotent(): void
    {
        $employee = $this->makeEmployee('probation', '2026-01-01');
        $employee->probation_start_date = '2026-01-01';
        $employee->save();

        $service = app(LeaveAllocationService::class);
        $first = $service->generateForEmployee($employee, 2026, null, 'probation', Carbon::parse('2026-01-01', 'Asia/Kolkata'));
        $second = $service->generateForEmployee($employee, 2026, null, 'probation', Carbon::parse('2026-01-01', 'Asia/Kolkata'));

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, \App\Models\HRMS\Leave\LeaveAllocationM::query()
            ->where('employee_id', $employee->id)
            ->where('year', 2026)
            ->where('employment_stage', 'probation')
            ->count());
    }

    private function makeEmployee(string $stage, string $joiningDate): EmployeeM
    {
        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $user = UserM::create([
            'name' => 'Leave Stage ' . uniqid(),
            'email' => 'leave_stage_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$role->id]);

        return EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-STAGE-' . rand(1000, 9999),
            'employment_type' => $stage === 'internship' ? 'intern' : 'full_time',
            'employee_stage' => $stage,
            'joining_date' => $joiningDate,
            'employment_status' => 'active',
            'is_active' => 1,
            'work_mode' => 'wfo',
        ]);
    }
}

