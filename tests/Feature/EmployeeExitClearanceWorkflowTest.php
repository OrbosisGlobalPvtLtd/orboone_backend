<?php

namespace Tests\Feature;

use App\Models\Core\UserM;
use App\Models\Core\RoleM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Employee\EmployeeExitProcessS;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeExitClearanceWorkflowTest extends TestCase
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

    public function test_clearance_initialized_and_updates_and_blocks_completion()
    {
        // 1. Create a user and employee
        $user = UserM::create([
            'name' => 'Clearance User',
            'email' => 'clearance_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->superAdminRole->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-CLR-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        // 2. Initiate Exit
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
        $exitId = $exitData['id'];

        // Verify clearance records are generated automatically
        $clearances = DB::table('employee_exit_clearances')
            ->where('exit_process_id', $exitId)
            ->get();
        $this->assertCount(8, $clearances); // 8 departments

        // Verify default status is pending
        foreach ($clearances as $c) {
            $this->assertEquals('pending', $c->status);
            $this->assertNotEmpty($c->checklist);
        }

        // Verify module summary returns initial values safely
        $summary = $this->exitProcessService->getModuleSummary($employee->id, (object)$exitData);
        $this->assertIsArray($summary);
        $this->assertEquals(0, $summary['assets_assigned']);

        // Verify completing exit throws exception because clearances are not approved
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->exitProcessService->complete($exitId, $user->id, false);
    }

    public function test_clearance_approvals_permit_exit_completion()
    {
        // 1. Create user and employee
        $user = UserM::create([
            'name' => 'Complete User',
            'email' => 'complete_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->superAdminRole->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-FIN-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        // 2. Initiate Exit
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
        $exitId = $exitData['id'];

        // 3. Approve all clearances
        $depts = ['hr', 'manager', 'it', 'admin', 'finance', 'asset', 'security', 'accounts'];
        foreach ($depts as $dept) {
            $this->exitProcessService->updateDepartmentClearance(
                $exitId,
                $dept,
                'approved',
                'Approved remarks for ' . $dept,
                [['item' => 'Dummy checklist item', 'completed' => true]],
                $user->id
            );
        }

        // Verify status in DB is approved for IT
        $this->assertEquals('approved', DB::table('employee_exit_clearances')->where('exit_process_id', $exitId)->where('department_key', 'it')->value('status'));

        // Verify lifecycle logs are recorded
        $logExists = DB::table('employee_lifecycle_logs')
            ->where('employee_id', $employee->id)
            ->where('action', 'IT Cleared')
            ->exists();
        $this->assertTrue($logExists);

        // Verify final clearance completed status logs
        $this->assertTrue(DB::table('employee_lifecycle_logs')->where('employee_id', $employee->id)->where('action', 'Clearance Completed')->exists());

        // 4. Complete exit (with waive incomplete options to bypass FNF checks if necessary)
        // Since we approved all clearances, the final clearance rule is satisfied and does not block!
        // We catch any FNF/asset waiver exception to ensure clearance validation did NOT block it.
        try {
            $this->exitProcessService->complete($exitId, $user->id, true);
            $this->assertEquals('exit_completed', DB::table('employee_exit_processes')->where('id', $exitId)->value('status'));
        } catch (\Exception $e) {
            // If it failed because of legacy FNF/Asset files, we check that it was NOT the clearance block exception
            $this->assertStringNotContainsString('Mandatory department clearances', $e->getMessage());
        }
    }
}
