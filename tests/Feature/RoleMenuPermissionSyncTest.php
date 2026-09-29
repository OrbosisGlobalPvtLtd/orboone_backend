<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Services\AccessControl\PermissionSyncService;
use App\Services\Core\Menu\SidebarMenuResolverS;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoleMenuPermissionSyncTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $superAdminUser;
    private UserM $employeeUser;
    private RoleM $employeeRole;
    private RoleM $superAdminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = RoleM::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin', 'id' => 1]);
        $this->superAdminUser = UserM::create([
            'name' => 'Super Admin',
            'email' => 'admin_menu_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->superAdminRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->superAdminUser->roles()->sync([$this->superAdminRole->id]);

        $this->employeeRole = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $this->employeeUser = UserM::create([
            'name' => 'Test Employee',
            'email' => 'employee_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->employeeRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->employeeUser->roles()->sync([$this->employeeRole->id]);
    }

    /**
     * Test 1: Employee Directory unchecked for Employee role -> hidden from sidebar and denies direct URL access (403).
     */
    public function test_employee_directory_unchecked_hides_from_sidebar_and_denies_direct_url_access(): void
    {
        $syncService = app(PermissionSyncService::class);
        $empDirMenu = DB::table('menus')->where('route', 'hrms.employees.index')->first();
        $this->assertNotNull($empDirMenu);

        // Assign standard employee menus (excluding Employee Directory)
        $selfServiceMenus = [1, 20, 349, 145, 181, 30, 32, 137, 34, 80, 83];
        $validMenuIds = DB::table('menus')->whereIn('id', $selfServiceMenus)->pluck('id')->all();

        DB::table('role_menu_access')->where('role_id', $this->employeeRole->id)->delete();
        foreach ($validMenuIds as $mId) {
            DB::table('role_menu_access')->insert(['role_id' => $this->employeeRole->id, 'menu_id' => $mId]);
        }

        $syncService->syncRolePermissionsFromMenus((int) $this->employeeRole->id, $validMenuIds);

        // Assert employee does NOT have employees.view permission
        $this->assertFalse($this->employeeUser->fresh()->hasPermission('employees.view'));

        // Assert Employee Directory is NOT in sidebar
        $resolver = app(SidebarMenuResolverS::class);
        $resolver->clearCache($this->employeeUser->id);
        $menus = $resolver->resolveForUser($this->employeeUser->fresh());

        $hasEmployeeDir = false;
        foreach ($menus as $parentId => $items) {
            foreach ($items as $item) {
                if ($item->route === 'hrms.employees.index') {
                    $hasEmployeeDir = true;
                    break 2;
                }
            }
        }
        $this->assertFalse($hasEmployeeDir, 'Employee Directory should NOT appear in sidebar.');

        // Direct GET /hrms/employees should return 403 Forbidden
        $response = $this->actingAs($this->employeeUser)->get('/hrms/employees');
        $response->assertStatus(403);
    }

    /**
     * Test 2: Employee Directory checked for a role -> sidebar visible + GET /hrms/employees accessible (200).
     */
    public function test_employee_directory_checked_shows_in_sidebar_and_grants_access(): void
    {
        $customRole = RoleM::firstOrCreate(['slug' => 'custom_officer'], ['name' => 'Custom Officer', 'id' => 88]);
        $customUser = UserM::create([
            'name' => 'Test Officer',
            'email' => 'officer_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $customRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $customUser->roles()->sync([$customRole->id]);

        $syncService = app(PermissionSyncService::class);
        $empDirMenu = DB::table('menus')->where('route', 'hrms.employees.index')->first();
        $this->assertNotNull($empDirMenu);

        // Include Employee Directory (and parent 10 if present)
        $menuIdsWithDir = [1, 10, $empDirMenu->id, 20, 349, 145, 80, 83];
        $validMenuIds = DB::table('menus')->whereIn('id', $menuIdsWithDir)->pluck('id')->all();

        DB::table('role_menu_access')->where('role_id', $customRole->id)->delete();
        foreach ($validMenuIds as $mId) {
            DB::table('role_menu_access')->insert(['role_id' => $customRole->id, 'menu_id' => $mId]);
        }

        $syncService->syncRolePermissionsFromMenus((int) $customRole->id, $validMenuIds);

        // Assert user has employees.view permission
        $this->assertTrue($customUser->fresh()->hasPermission('employees.view'));

        // Assert Employee Directory is in sidebar
        $resolver = app(SidebarMenuResolverS::class);
        $resolver->clearCache($customUser->id);
        $menus = $resolver->resolveForUser($customUser->fresh());

        $hasEmployeeDir = false;
        foreach ($menus as $parentId => $items) {
            foreach ($items as $item) {
                if ($item->route === 'hrms.employees.index') {
                    $hasEmployeeDir = true;
                    break 2;
                }
            }
        }
        $this->assertTrue($hasEmployeeDir, 'Employee Directory SHOULD appear in sidebar for authorized role.');

        // Direct GET /hrms/employees should return 200 OK
        $response = $this->actingAs($customUser)->get('/hrms/employees');
        $response->assertStatus(200);
    }

    /**
     * Test 3: Unchecking Employee Directory via RoleMenu controller removes its permissions and denies access.
     */
    public function test_unchecking_employee_directory_removes_permissions_and_denies_direct_access(): void
    {
        $customRole = RoleM::firstOrCreate(['slug' => 'custom_officer_2'], ['name' => 'Custom Officer 2', 'id' => 89]);
        $customUser = UserM::create([
            'name' => 'Test Officer 2',
            'email' => 'officer_test2_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $customRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $customUser->roles()->sync([$customRole->id]);

        $empDirMenu = DB::table('menus')->where('route', 'hrms.employees.index')->first();
        $this->assertNotNull($empDirMenu);

        // 1. First grant Employee Directory via controller update
        $this->actingAs($this->superAdminUser);
        $response = $this->put(route('role_menus.update', $customRole->id), [
            'menu_ids' => [1, 10, $empDirMenu->id, 80, 83],
        ]);
        $response->assertRedirect();
        $this->assertTrue($customUser->fresh()->hasPermission('employees.view'));

        // 2. Now uncheck Employee Directory (saving without it)
        $responseUncheck = $this->put(route('role_menus.update', $customRole->id), [
            'menu_ids' => [1, 80, 83],
        ]);
        $responseUncheck->assertRedirect();

        // Permissions must be removed
        $this->assertFalse($customUser->fresh()->hasPermission('employees.view'));

        // Direct URL access must be 403 Forbidden
        $responseAccess = $this->actingAs($customUser)->get('/hrms/employees');
        $responseAccess->assertStatus(403);
    }

    /**
     * Test 4: Shared permissions are preserved when only one requiring menu is unchecked.
     */
    public function test_shared_permission_is_preserved_when_another_checked_menu_requires_it(): void
    {
        $syncService = app(PermissionSyncService::class);
        $assetsMenu = DB::table('menus')->where('route', 'hrms.assets.index')->first();
        $myAssetsMenu = DB::table('menus')->where('route', 'hrms.employee.assets.index')->first();

        $this->assertNotNull($assetsMenu);
        $this->assertNotNull($myAssetsMenu);

        // Both menus require 'asset_allocations.manage'
        $bothMenus = [$assetsMenu->id, $myAssetsMenu->id];
        $syncService->syncRolePermissionsFromMenus((int) $this->employeeRole->id, $bothMenus);
        $this->assertTrue($this->employeeUser->fresh()->hasPermission('asset_allocations.manage'));

        // Uncheck AssetsMenu ($assetsMenu->id), keep MyAssetsMenu ($myAssetsMenu->id)
        $syncService->syncRolePermissionsFromMenus((int) $this->employeeRole->id, [$myAssetsMenu->id]);
        $this->assertTrue($this->employeeUser->fresh()->hasPermission('asset_allocations.manage'), 'Shared permission MUST NOT be removed when another checked menu still needs it.');

        // Uncheck both menus
        $syncService->syncRolePermissionsFromMenus((int) $this->employeeRole->id, [1, 80]);
        $this->assertFalse($this->employeeUser->fresh()->hasPermission('asset_allocations.manage'), 'Shared permission MUST be removed when no checked menu needs it.');
    }

    /**
     * Test 5: Super Admin bypass behavior remains intact.
     */
    public function test_super_admin_bypass_behavior_remains_intact(): void
    {
        $this->assertTrue($this->superAdminUser->hasRole(['super_admin']));
        $this->assertTrue($this->superAdminUser->hasPermission('employees.view'));

        // Super Admin accessing /hrms/employees should return 200 OK
        $response = $this->actingAs($this->superAdminUser)->get('/hrms/employees');
        $response->assertStatus(200);
    }

    /**
     * Test 6: Retired legacy payroll permissions are NOT derived from any current active menu.
     */
    public function test_legacy_payroll_permissions_are_not_derived_from_any_current_menu(): void
    {
        $syncService = app(PermissionSyncService::class);
        $allActiveMenuIds = DB::table('menus')->where('is_active', 1)->pluck('id')->all();

        $derivedPermIds = $syncService->derivePermissionsForMenuIds($allActiveMenuIds);
        $derivedPermKeys = DB::table('permissions')->whereIn('id', $derivedPermIds)->pluck('key')->all();

        $retiredLegacyPayrollKeys = [
            'payroll.dashboard.view',
            'payroll.salary_structure.view',
            'payroll.salary_structure.manage',
            'payroll.structure.manage',
            'payroll.attendance_impacts.view',
            'payroll.generate.view',
            'payroll.generate.process',
            'payroll.payslips.view',
            'payroll.payslips.view_all',
            'payroll.payslip.view',
            'payroll.fnf.view',
            'payroll.fnf.manage',
            'payroll.bonus.view',
            'payroll.bonus.manage',
            'payroll.monthly_summary.view',
            'payroll.claims.view_all',
            'payroll.claims.manage',
            'payroll.adjustments.manage',
            'payroll_self.view_payslip',
            'payroll.payslips.view_own',
        ];

        foreach ($retiredLegacyPayrollKeys as $retiredKey) {
            $this->assertNotContains($retiredKey, $derivedPermKeys, "Retired legacy payroll permission {$retiredKey} MUST NOT be derived from any active menu.");
        }
    }

    /**
     * Test 7: Enterprise Payroll permissions remain intact and properly derived.
     */
    public function test_enterprise_payroll_permissions_remain_intact(): void
    {
        $customRole = RoleM::firstOrCreate(['slug' => 'payroll_specialist'], ['name' => 'Payroll Specialist', 'id' => 95]);
        $customUser = UserM::create([
            'name' => 'Test Payroll Specialist',
            'email' => 'payroll_spec_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $customRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $customUser->roles()->sync([$customRole->id]);

        $syncService = app(PermissionSyncService::class);
        $enterpriseMenuIds = DB::table('menus')
            ->where('module_key', 'enterprise_payroll')
            ->orWhere('route', 'like', 'enterprise-payroll.%')
            ->pluck('id')
            ->all();

        $this->assertNotEmpty($enterpriseMenuIds);

        $syncService->syncRolePermissionsFromMenus((int) $customRole->id, $enterpriseMenuIds);

        $this->assertTrue($customUser->fresh()->hasPermission('enterprise_payroll.dashboard.view'));
        $this->assertTrue($customUser->fresh()->hasPermission('enterprise_salary_structure.view'));
        $this->assertTrue($customUser->fresh()->hasPermission('enterprise_payroll_run.view'));
        $this->assertTrue($customUser->fresh()->hasPermission('enterprise_payslip.view'));
        $this->assertTrue($customUser->fresh()->hasPermission('enterprise_payroll.policy.view'));
    }

    /**
     * Test 8: Employee self-service menus cannot grant employees.* admin permissions.
     */
    public function test_employee_self_service_menus_cannot_grant_employees_admin_permissions(): void
    {
        $syncService = app(PermissionSyncService::class);
        $selfServiceMenus = [1, 20, 349, 145, 181, 30, 32, 137, 34, 80, 83, 300, 309, 310, 331, 332];
        $validMenuIds = DB::table('menus')->whereIn('id', $selfServiceMenus)->pluck('id')->all();

        $derivedPermIds = $syncService->derivePermissionsForMenuIds($validMenuIds);
        $derivedPermKeys = DB::table('permissions')->whereIn('id', $derivedPermIds)->pluck('key')->all();

        $forbiddenAdminKeys = [
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',
            'employees.exit.view',
            'employees.exit.manage',
            'employees.organization.manage',
            'employees.reporting_structure.manage',
        ];

        foreach ($forbiddenAdminKeys as $adminKey) {
            $this->assertNotContains($adminKey, $derivedPermKeys, "Employee self-service menus must NOT derive admin permission {$adminKey}.");
        }
    }

    /**
     * Test 9: Employee with reportees sees Team Management in sidebar, while employee without reportees does not.
     */
    public function test_reporting_manager_sees_team_management_menu_while_non_manager_does_not(): void
    {
        $role = RoleM::firstOrCreate(['slug' => 'test_emp_role_' . uniqid()], ['name' => 'Test Employee Role']);

        // Give this role all standard menus including Team Management (370 and submenus 371-376)
        $allMenuIds = DB::table('menus')->pluck('id')->all();
        foreach ($allMenuIds as $mId) {
            DB::table('role_menu_access')->insert(['role_id' => $role->id, 'menu_id' => $mId]);
        }

        // Manager user with employee having reportees
        $managerUser = UserM::create([
            'name' => 'Test Manager ' . uniqid(),
            'email' => 'manager_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'status' => 'active',
        ]);
        $managerEmpId = DB::table('employees_new')->insertGetId([
            'user_id' => $managerUser->id,
            'employee_code' => 'EMP_MGR_' . uniqid(),
            'is_active' => 1,
        ]);
        // Reportee employee
        $reporteeEmpId = DB::table('employees_new')->insertGetId([
            'employee_code' => 'EMP_REP_' . uniqid(),
            'reporting_manager_employee_id' => $managerEmpId,
            'is_active' => 1,
        ]);

        // Non-manager user with employee having NO reportees
        $nonManagerUser = UserM::create([
            'name' => 'Test Regular Emp ' . uniqid(),
            'email' => 'regular_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'status' => 'active',
        ]);
        DB::table('employees_new')->insertGetId([
            'user_id' => $nonManagerUser->id,
            'employee_code' => 'EMP_REG_' . uniqid(),
            'is_active' => 1,
        ]);

        $resolver = app(SidebarMenuResolverS::class);

        // Check manager sidebar
        $resolver->clearCache($managerUser->id);
        $managerMenus = $resolver->resolveForUser($managerUser);
        $managerHasTeam = false;
        foreach ($managerMenus as $parentId => $items) {
            foreach ($items as $item) {
                if ((int)$item->id === 370 || (int)$item->parent_id === 370) {
                    $managerHasTeam = true;
                    break 2;
                }
            }
        }
        $this->assertTrue($managerHasTeam, 'Reporting manager MUST see Team Management in sidebar.');

        // Check non-manager sidebar
        $resolver->clearCache($nonManagerUser->id);
        $nonManagerMenus = $resolver->resolveForUser($nonManagerUser);
        $nonManagerHasTeam = false;
        foreach ($nonManagerMenus as $parentId => $items) {
            foreach ($items as $item) {
                if ((int)$item->id === 370 || (int)$item->parent_id === 370) {
                    $nonManagerHasTeam = true;
                    break 2;
                }
            }
        }
        $this->assertFalse($nonManagerHasTeam, 'Non-manager employee MUST NOT see Team Management in sidebar.');
    }

    /**
     * Test 10: Employee Self-Service eligibility based on employee existence.
     */
    public function test_employee_self_service_menus_require_employee_record_existence(): void
    {
        $role = RoleM::firstOrCreate(['slug' => 'test_ess_role_' . uniqid()], ['name' => 'Test ESS Role']);

        // Assign self-service menus (e.g. Apply Leave 137, My Leave Requests 32, Leave Management Parent 30)
        // And admin menu (e.g. Employee Directory 21, Employee Management Parent 20)
        $empMgmtParent = 20;
        $empDirMenu = 21;
        $leaveParent = 30;
        $myLeaveMenu = 32;
        $applyLeaveMenu = 137;

        $assignedMenus = [$empMgmtParent, $empDirMenu, $leaveParent, $myLeaveMenu, $applyLeaveMenu];
        foreach ($assignedMenus as $mId) {
            DB::table('role_menu_access')->insert(['role_id' => $role->id, 'menu_id' => $mId]);
        }

        $syncService = app(PermissionSyncService::class);
        $syncService->syncRolePermissionsFromMenus((int) $role->id, $assignedMenus);

        // 1. User WITH employee record in employees_new
        $userWithEmp = UserM::create([
            'name' => 'User With Emp ' . uniqid(),
            'email' => 'withemp_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'status' => 'active',
        ]);
        DB::table('employees_new')->insert([
            'user_id' => $userWithEmp->id,
            'employee_code' => 'EMP_EXIST_' . uniqid(),
            'is_active' => 1,
        ]);

        // 2. User WITHOUT employee record in employees_new
        $userWithoutEmp = UserM::create([
            'name' => 'User Without Emp ' . uniqid(),
            'email' => 'withoutemp_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'status' => 'active',
        ]);

        $resolver = app(SidebarMenuResolverS::class);

        // Req 1: User with employees_new record + assigned self-service menu -> menu visible
        $resolver->clearCache($userWithEmp->id);
        $menusWithEmp = $resolver->resolveForUser($userWithEmp)->flatten(1)->pluck('id')->all();
        $this->assertContains($myLeaveMenu, $menusWithEmp, 'User with employee record MUST see assigned self-service menu.');
        $this->assertContains($applyLeaveMenu, $menusWithEmp, 'User with employee record MUST see assigned apply leave menu.');
        $this->assertContains($empDirMenu, $menusWithEmp, 'User with employee record MUST see assigned admin menu.');

        // Req 2: User without employees_new record + same assigned self-service menu -> menu hidden
        $resolver->clearCache($userWithoutEmp->id);
        $menusWithoutEmp = $resolver->resolveForUser($userWithoutEmp)->flatten(1)->pluck('id')->all();
        $this->assertNotContains($myLeaveMenu, $menusWithoutEmp, 'User without employee record MUST NOT see self-service menu.');
        $this->assertNotContains($applyLeaveMenu, $menusWithoutEmp, 'User without employee record MUST NOT see apply leave menu.');

        // Req 3: User without employees_new record + assigned admin/operational menu -> existing RBAC behavior remains unchanged
        $this->assertContains($empDirMenu, $menusWithoutEmp, 'User without employee record MUST still see assigned admin menu.');

        // Req 4: Employee existence must NOT automatically grant unassigned menus
        $unassignedMenuId = 34; // Leave Balance (not assigned in $assignedMenus)
        $this->assertNotContains($unassignedMenuId, $menusWithEmp, 'Employee existence must NOT automatically grant unassigned menus.');
    }

    /**
     * Test 11: Permission sync clears user and sidebar cache after transaction commit.
     */
    public function test_permission_sync_clears_cache_after_transaction_commit(): void
    {
        $role = RoleM::create(['name' => 'Commit Role Test', 'slug' => 'commit_role_' . uniqid()]);
        $user = UserM::create([
            'name' => 'Commit User Test',
            'email' => 'commit_user_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);

        $resolver = app(SidebarMenuResolverS::class);
        $syncService = app(PermissionSyncService::class);

        // 1. Warm cache
        $cacheKey = config('authorization.sidebar_cache_prefix', 'user_menus_v2_') . $user->id;
        $resolver->resolveForUser($user);
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has($cacheKey), 'Sidebar cache must be populated after resolve.');

        // 2. Execute sync inside a database transaction
        DB::transaction(function () use ($syncService, $role) {
            $syncService->syncRolePermissionsFromMenus((int) $role->id, [1]);
        });

        // 3. Assert cache is flushed after transaction commits
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has($cacheKey), 'Sidebar cache must be cleared after transaction commit.');
    }

    /**
     * Test 12: Matrix-assigned permissions survive role menu sync.
     */
    public function test_matrix_assigned_non_menu_permissions_survive_role_menu_sync(): void
    {
        $role = RoleM::create([
            'name' => 'Matrix Overlap Role ' . uniqid(),
            'slug' => 'matrix_overlap_' . uniqid(),
        ]);
        $user = UserM::create([
            'name' => 'Overlap User ' . uniqid(),
            'email' => 'overlap_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);

        $syncService = app(PermissionSyncService::class);

        // 1. Manually assign action permission (e.g. employees.edit) via role_permissions
        $editPermId = DB::table('permissions')->where('key', 'employees.edit')->value('id');
        if (! $editPermId) {
            $editPermId = DB::table('permissions')->insertGetId([
                'module' => 'employees',
                'action' => 'edit',
                'key' => 'employees.edit',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('role_permissions')->insert([
            'role_id' => $role->id,
            'permission_id' => $editPermId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Perform menu sync with menu ID 1 (Dashboard)
        $syncService->syncRolePermissionsFromMenus((int) $role->id, [1]);

        // 3. Assert employees.edit is preserved in role_permissions
        $this->assertDatabaseHas('role_permissions', [
            'role_id' => $role->id,
            'permission_id' => $editPermId,
        ]);
        $this->assertTrue($user->fresh()->hasPermission('employees.edit'));
    }
}



