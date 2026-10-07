<?php

namespace Tests\Feature;

use App\Models\Core\PermissionM;
use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Department\DepartmentM;
use App\Models\HRMS\Designation\DesignationM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\AccessControl\ModulePermissionS;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PersonAndPositionRbacTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_has_all_permissions(): void
    {
        $superAdminRole = RoleM::firstOrCreate(
            ['slug' => 'super_admin'],
            ['name' => 'Super Admin', 'is_system' => 1, 'status' => 1]
        );

        $user = UserM::factory()->create([
            'system_role_id' => $superAdminRole->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);

        $this->assertTrue($user->hasPermission('any.random.nonexistent.permission'));
        $this->assertTrue($user->hasPermission('employees.view'));
    }

    public function test_role_based_permissions_work(): void
    {
        $role = RoleM::create([
            'name' => 'Test Custom Role',
            'slug' => 'test_custom_role_' . uniqid(),
            'is_system' => 0,
            'status' => 1,
        ]);

        $perm = PermissionM::firstOrCreate(
            ['key' => 'test.role.permission'],
            ['module' => 'test', 'submodule' => 'test', 'action' => 'view', 'description' => 'Test Perm']
        );

        DB::table('role_permissions')->insert([
            'role_id' => $role->id,
            'permission_id' => $perm->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = UserM::factory()->create([
            'system_role_id' => $role->id,
            'is_active' => 1,
        ]);

        $this->assertTrue($user->hasPermission('test.role.permission'));
        $this->assertFalse($user->hasPermission('unassigned.permission'));
    }

    public function test_position_designation_based_permissions_work(): void
    {
        $employeeRole = RoleM::firstOrCreate(
            ['slug' => 'employee'],
            ['name' => 'Employee', 'is_system' => 1, 'status' => 1]
        );

        $department = DepartmentM::firstOrCreate(
            ['code' => 'TEST_DEPT'],
            ['name' => 'Test Department', 'is_active' => 1]
        );

        $designation = DesignationM::create([
            'department_id' => $department->id,
            'name' => 'Lead Architect ' . uniqid(),
            'code' => 'ARCH-' . rand(100, 999),
            'is_active' => 1,
        ]);

        $permKey = 'architecture.blueprints.edit';

        // Grant permission to this position/designation
        DB::table('designation_module_access')->insert([
            'designation_id' => $designation->id,
            'module_key' => 'architecture',
            'permission_key' => $permKey,
            'is_enabled' => 1,
            'is_allowed' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = UserM::factory()->create([
            'system_role_id' => $employeeRole->id,
            'is_active' => 1,
        ]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-' . rand(1000, 9999),
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'system_role_id' => $employeeRole->id,
            'is_active' => 1,
        ]);

        // User should have the position-granted permission
        $this->assertTrue($user->hasPermission($permKey));
        $this->assertFalse($user->hasPermission('unassigned.position.permission'));
    }

    public function test_person_user_override_precedence(): void
    {
        $employeeRole = RoleM::firstOrCreate(
            ['slug' => 'employee'],
            ['name' => 'Employee', 'is_system' => 1, 'status' => 1]
        );

        $department = DepartmentM::firstOrCreate(
            ['code' => 'TEST_DEPT_2'],
            ['name' => 'Test Department 2', 'is_active' => 1]
        );

        $designation = DesignationM::create([
            'department_id' => $department->id,
            'name' => 'Specialist ' . uniqid(),
            'code' => 'SPEC-' . rand(100, 999),
            'is_active' => 1,
        ]);

        $grantedPermKey = 'special.tool.access';
        $revokedPermKey = 'confidential.records.view';

        // 1. Position has confidential records view
        DB::table('designation_module_access')->insert([
            'designation_id' => $designation->id,
            'module_key' => 'confidential',
            'permission_key' => $revokedPermKey,
            'is_enabled' => 1,
            'is_allowed' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = UserM::factory()->create([
            'system_role_id' => $employeeRole->id,
            'is_active' => 1,
        ]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-' . rand(1000, 9999),
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'system_role_id' => $employeeRole->id,
            'is_active' => 1,
        ]);

        // Prior to override: position allows confidential records
        $this->assertTrue($user->hasPermission($revokedPermKey));

        // 2. User-level explicit GRANT for special.tool.access (which neither role nor position has)
        DB::table('user_module_access')->insert([
            'user_id' => $user->id,
            'module_key' => 'special',
            'permission_key' => $grantedPermKey,
            'is_enabled' => 1,
            'is_allowed' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. User-level explicit REVOKE for confidential.records.view (which position had)
        DB::table('user_module_access')->insert([
            'user_id' => $user->id,
            'module_key' => 'confidential',
            'permission_key' => $revokedPermKey,
            'is_enabled' => 0,
            'is_allowed' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // User explicit grant should succeed
        $this->assertTrue($user->hasPermission($grantedPermKey));

        // User explicit revoke must block despite position having it
        $this->assertFalse($user->hasPermission($revokedPermKey));
    }

    public function test_module_permission_service_handles_position_matrix(): void
    {
        $department = DepartmentM::firstOrCreate(
            ['code' => 'TEST_DEPT_3'],
            ['name' => 'Test Department 3', 'is_active' => 1]
        );

        $designation = DesignationM::create([
            'department_id' => $department->id,
            'name' => 'Operations Officer ' . uniqid(),
            'code' => 'OPS-' . rand(100, 999),
            'is_active' => 1,
        ]);

        $service = app(ModulePermissionS::class);

        // Save position permissions
        $service->savePositionMatrix($designation->id, ['employees.view', 'employees.edit']);

        $matrix = $service->getPositionMatrix($designation->id);

        $this->assertNotEmpty($matrix);
        $this->assertEquals($designation->id, $matrix['designation']->id);
        $this->assertContains('employees.view', $matrix['assignedKeys']);
        $this->assertContains('employees.edit', $matrix['assignedKeys']);
    }
}
