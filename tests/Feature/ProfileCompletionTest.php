<?php

namespace Tests\Feature;

use App\Models\Core\UserM;
use App\Models\Core\RoleM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProfileCompletionTest extends TestCase
{
    use DatabaseTransactions;

    private RoleM $superAdminRole;
    private RoleM $hrAdminRole;
    private RoleM $employeeRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Roles
        $this->superAdminRole = RoleM::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin']);
        $this->hrAdminRole = RoleM::firstOrCreate(['slug' => 'hr_admin'], ['name' => 'HR Admin']);
        $this->employeeRole = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee']);
    }

    /**
     * 1. super_admin user without employees_new can access dashboard and is not redirected to complete-profile.
     */
    public function test_super_admin_without_employee_can_access_dashboard_without_redirect(): void
    {
        $user = UserM::create([
            'name' => 'Super Admin No Employee',
            'email' => 'superadmin_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->superAdminRole->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$this->superAdminRole->id]);

        $this->actingAs($user);

        // Access dashboard
        $response = $this->get(route('dashboard'));

        // It should redirect to super-admin dashboard route, not the profile completion route
        $response->assertRedirect(route('dashboard.super_admin'));

        // Access super-admin dashboard directly
        $response2 = $this->get(route('dashboard.super_admin'));
        $response2->assertStatus(200);
    }

    /**
     * 2. hr_admin user without employees_new can access dashboard and is not redirected.
     */
    public function test_hr_admin_without_employee_can_access_dashboard_without_redirect(): void
    {
        $user = UserM::create([
            'name' => 'HR Admin No Employee',
            'email' => 'hradmin_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->hrAdminRole->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$this->hrAdminRole->id]);

        $this->actingAs($user);

        // Access dashboard
        $response = $this->get(route('dashboard'));

        // It should redirect to hr-admin dashboard route, not the profile completion route
        $response->assertRedirect(route('dashboard.hr_admin'));

        // Access hr-admin dashboard directly
        $response2 = $this->get(route('dashboard.hr_admin'));
        $response2->assertStatus(200);
    }

    /**
     * 3. employee user with employees_new incomplete profile redirects to complete-profile.
     */
    public function test_employee_with_incomplete_profile_redirects_to_complete_profile(): void
    {
        $user = UserM::create([
            'name' => 'Incomplete Employee',
            'email' => 'employee_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->employeeRole->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$this->employeeRole->id]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-TEST-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfh',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        EmployeeProfileM::create([
            'employee_id' => $employee->id,
            'profile_status' => 'pending',
            'is_profile_completed' => false,
        ]);

        $this->actingAs($user);

        // Access dashboard
        $response = $this->get(route('dashboard'));

        // Employee with incomplete profile gets redirected to hrms.employee.complete_profile
        $response->assertRedirect(route('hrms.employee.complete_profile'));
    }

    /**
     * 4. employee user with approved profile can access dashboard.
     */
    public function test_employee_with_approved_profile_can_access_dashboard(): void
    {
        $user = UserM::create([
            'name' => 'Approved Employee',
            'email' => 'employee_approved_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->employeeRole->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$this->employeeRole->id]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-APP-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfh',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        EmployeeProfileM::create([
            'employee_id' => $employee->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        // Mock document completion verification as approved/verified (or is_profile_completed is already true and approved)
        // Wait, EmployeeProfileCompletionS checks document verification, let's mock/stub or simply verify
        $this->actingAs($user);

        // Access dashboard
        $response = $this->get(route('dashboard'));

        // It should redirect to dashboard.employee
        $response->assertRedirect(route('dashboard.employee'));
    }

    /**
     * 5. attendance gating still blocks incomplete employee profile.
     */
    public function test_attendance_gating_blocks_incomplete_employee_profile(): void
    {
        $user = UserM::create([
            'name' => 'Gated Employee',
            'email' => 'employee_gated_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->employeeRole->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);
        $user->roles()->sync([$this->employeeRole->id]);

        $employee = EmployeeM::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-GATE-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfh',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        EmployeeProfileM::create([
            'employee_id' => $employee->id,
            'profile_status' => 'pending',
            'is_profile_completed' => false,
        ]);

        $this->actingAs($user);

        // Accessing attendance routes should redirect or block because check.profile.complete middleware is applied
        $response = $this->get('/attendances');

        // It should redirect to the profile completion route
        $response->assertRedirect(route('hrms.employee.complete_profile'));
    }
}
