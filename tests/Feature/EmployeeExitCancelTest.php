<?php

namespace Tests\Feature;

use App\Models\Core\UserM;
use App\Models\Core\RoleM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Employee\EmployeeExitProcessS;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeExitCancelTest extends TestCase
{
    use DatabaseTransactions;

    private RoleM $superAdminRole;
    private EmployeeExitProcessS $exitProcessService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = RoleM::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin']);
        $this->exitProcessService = app(EmployeeExitProcessS::class);
    }

    public function test_cancel_exit_restores_employee_and_login_if_disabled_by_exit()
    {
        // 1. Create a user and employee
        $user = UserM::create([
            'name' => 'Exit Test User',
            'email' => 'exit_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->superAdminRole->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-EXIT-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        // 2. Initiate Exit with immediate disable login
        $payload = [
            'exit_type' => 'termination',
            'resignation_date' => now()->toDateString(),
            'termination_date' => now()->toDateString(),
            'exit_initiated_date' => now()->toDateString(),
            'last_working_day' => now()->toDateString(),
            'notice_period_days' => 0,
            'notice_waived' => true,
            'immediate_exit' => true,
            'buyout_recovery' => false,
            'immediate_disable_login' => true,
            'reason' => 'Test reason',
            'remarks' => 'Test remarks',
        ];

        $exitData = $this->exitProcessService->initiate($employee->id, $payload, $user->id);

        // Verify exit is initiated, employee is terminated, user is deactivated, and flag set
        $this->assertEquals('terminated', DB::table('employees_new')->where('id', $employee->id)->value('employment_status'));
        $this->assertEquals(0, DB::table('users')->where('id', $user->id)->value('is_active'));
        $this->assertEquals(1, DB::table('employee_exit_processes')->where('id', $exitData['id'])->value('login_disabled_by_exit'));

        // 3. Cancel Exit
        $this->exitProcessService->cancel($exitData['id'], $user->id, 'Reinstated employee');

        // Verify exit is cancelled, employee is active, user is active again
        $this->assertEquals('cancelled', DB::table('employee_exit_processes')->where('id', $exitData['id'])->value('status'));
        $this->assertEquals('active', DB::table('employees_new')->where('id', $employee->id)->value('employment_status'));
        $this->assertEquals(1, DB::table('users')->where('id', $user->id)->value('is_active'));
        $this->assertNull(DB::table('employees_new')->where('id', $employee->id)->value('relieving_date'));

        // Verify lifecycle log
        $logExists = DB::table('employee_lifecycle_logs')
            ->where('employee_id', $employee->id)
            ->where('action', 'Exit Process Cancelled')
            ->exists();
        $this->assertTrue($logExists);

        // 4. Verify visibility rules: active directory vs exit list
        $activeEmployees = DB::table('employees_new')
            ->where('employment_status', 'active')
            ->where('is_active', 1)
            ->pluck('id')
            ->toArray();
        $this->assertContains($employee->id, $activeEmployees);

        $this->actingAs($user);
        $response = $this->get(route('hrms.employees.exit'));
        $response->assertStatus(200);
        
        $viewEmployees = $response->original->getData()['employees'];
        $viewEmployeeIds = $viewEmployees->pluck('id')->toArray();
        $this->assertNotContains($employee->id, $viewEmployeeIds);
    }

    public function test_cancel_exit_does_not_restore_manually_disabled_user()
    {
        // 1. Create a user manually disabled
        $user = UserM::create([
            'name' => 'Disabled Test User',
            'email' => 'disabled_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->superAdminRole->id,
            'is_active' => 0, // Manually disabled!
            'is_web_access' => 1,
        ]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-DIS-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        // 2. Initiate Exit (without immediate login disable parameter, but standard flow)
        $payload = [
            'exit_type' => 'resignation',
            'resignation_date' => now()->toDateString(),
            'exit_initiated_date' => now()->toDateString(),
            'last_working_day' => now()->toDateString(),
            'notice_period_days' => 15,
            'notice_waived' => false,
            'immediate_exit' => false,
            'buyout_recovery' => false,
            'immediate_disable_login' => false,
        ];

        $exitData = $this->exitProcessService->initiate($employee->id, $payload, $user->id);

        // Verify flag is 0 because exit process did not disable the login
        $this->assertEquals(0, DB::table('employee_exit_processes')->where('id', $exitData['id'])->value('login_disabled_by_exit'));

        // 3. Cancel Exit
        $this->exitProcessService->cancel($exitData['id'], $user->id, 'Reinstated disabled employee');

        // Verify user is STILL inactive
        $this->assertEquals(0, DB::table('users')->where('id', $user->id)->value('is_active'));
    }
}
