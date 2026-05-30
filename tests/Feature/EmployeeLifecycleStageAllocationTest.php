<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use App\Models\HRMS\Leave\LeavePolicyM;
use App\Services\HRMS\Employee\EmployeeLifecycleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmployeeLifecycleStageAllocationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        LeavePolicyM::query()->update(['is_active' => 0]);
        LeavePolicyM::create([
            'policy_name' => 'Lifecycle Stage Policy',
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
    }

    public function test_internship_and_probation_stage_get_one_leave_allocation(): void
    {
        $intern = $this->makeEmployee('internship', '2026-01-10');
        app(EmployeeLifecycleService::class)->autoAllocateForStage($intern->id, 'internship', '2026-01-10');

        $internAlloc = LeaveAllocationM::where('employee_id', $intern->id)->where('year', 2026)->where('employment_stage', 'internship')->first();
        $this->assertNotNull($internAlloc);
        $this->assertSame(1.0, (float) $internAlloc->total_allocated);

        $probation = $this->makeEmployee('probation', '2026-02-01');
        app(EmployeeLifecycleService::class)->autoAllocateForStage($probation->id, 'probation', '2026-02-01');

        $probAlloc = LeaveAllocationM::where('employee_id', $probation->id)->where('year', 2026)->where('employment_stage', 'probation')->first();
        $this->assertNotNull($probAlloc);
        $this->assertSame(1.0, (float) $probAlloc->total_allocated);
    }

    public function test_internship_to_probation_and_probation_to_permanent_allocates_expected_values(): void
    {
        $employee = $this->makeEmployee('internship', '2026-03-01');
        $service = app(EmployeeLifecycleService::class);

        $service->autoAllocateForStage($employee->id, 'internship', '2026-03-01');
        $service->autoAllocateForStage($employee->id, 'probation', '2026-05-01');
        $service->autoAllocateForStage($employee->id, 'probation', '2026-05-01');

        $probCount = LeaveAllocationM::where('employee_id', $employee->id)->where('year', 2026)->where('employment_stage', 'probation')->count();
        $this->assertSame(1, $probCount);

        $employee->employee_stage = 'permanent';
        $employee->confirmation_date = '2026-09-05';
        $employee->save();

        $service->autoAllocateForStage($employee->id, 'permanent', '2026-09-05');

        $perm = LeaveAllocationM::where('employee_id', $employee->id)->where('year', 2026)->where('employment_stage', 'permanent')->first();
        $this->assertNotNull($perm);
        $this->assertSame(8.0, (float) $perm->total_allocated);
        $this->assertSame(6.0, (float) $perm->paid_allocated);
        $this->assertSame(2.0, (float) $perm->sick_allocated);
    }

    private function makeEmployee(string $stage, string $joiningDate): EmployeeM
    {
        $role = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $user = UserM::create([
            'name' => 'Lifecycle Stage ' . uniqid(),
            'email' => 'lifecycle_stage_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$role->id]);

        return EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-LC-' . rand(1000, 9999),
            'employment_type' => $stage === 'internship' ? 'intern' : 'full_time',
            'employee_stage' => $stage,
            'joining_date' => $joiningDate,
            'employment_status' => 'active',
            'is_active' => 1,
            'work_mode' => 'wfo',
        ]);
    }
}

