<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrganizationMenuAccessTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $adminUser;
    private UserM $hrUser;
    private RoleM $hrRole;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = RoleM::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin', 'id' => 1]);
        $this->adminUser = UserM::create([
            'name' => 'Super Admin',
            'email' => 'admin_org_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $adminRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
            'must_change_password' => 0,
        ]);
        $this->adminUser->roles()->sync([$adminRole->id]);

        $this->hrRole = RoleM::firstOrCreate(['slug' => 'hr_admin'], ['name' => 'HR Admin', 'id' => 3]);
        $this->hrUser = UserM::create([
            'name' => 'HR Admin User',
            'email' => 'hr_org_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->hrRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
            'must_change_password' => 0,
        ]);
        $this->hrUser->roles()->sync([$this->hrRole->id]);
    }

    public function test_assigning_organization_menu_grants_route_access(): void
    {
        $orgMenu = DB::table('menus')->where('route', 'hrms.organization.index')->first();
        $this->assertNotNull($orgMenu);

        $this->actingAs($this->adminUser);
        $response = $this->put(route('role_menus.update', $this->hrRole->id), [
            'menu_ids' => [$orgMenu->id],
        ]);
        $response->assertRedirect(route('role_menus.index'));

        $emp = \App\Models\HRMS\Employee\EmployeeM::create([
            'user_id' => $this->hrUser->id,
            'employee_code' => 'EMP-HR-' . rand(1000, 9999),
            'employment_status' => 'active',
            'is_active' => 1,
        ]);
        \App\Models\HRMS\Employee\EmployeeProfileM::create([
            'employee_id' => $emp->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        DB::table('users')->where('id', $this->hrUser->id)->update(['must_change_password' => 0]);

        // HR Admin should now be able to access /hrms/organization without 403
        $pageResponse = $this->actingAs($this->hrUser->fresh())
            ->get('/hrms/organization');
        $pageResponse->assertStatus(200);
    }
}
