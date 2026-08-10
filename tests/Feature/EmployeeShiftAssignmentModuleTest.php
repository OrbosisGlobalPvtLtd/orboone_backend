<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\AttendanceTimeM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeShiftTimingM;
use App\Services\Core\Menu\SidebarMenuResolverS;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmployeeShiftAssignmentModuleTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $adminUser;
    private UserM $employeeUser;
    private EmployeeM $employee;
    private AttendanceTimeM $morningShift;
    private AttendanceTimeM $eveningShift;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = RoleM::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin', 'id' => 1]);
        $empRole = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);

        $this->adminUser = UserM::create([
            'name' => 'Shift Assign Admin',
            'email' => 'shift_assign_admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $adminRole->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);
        $this->adminUser->roles()->sync([$adminRole->id]);

        $this->employeeUser = UserM::create([
            'name' => 'Regular Employee User',
            'email' => 'reg_emp_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $empRole->id,
            'is_active' => 1,
        ]);
        $this->employeeUser->roles()->sync([$empRole->id]);

        $this->employee = EmployeeM::create([
            'user_id' => $this->employeeUser->id,
            'employee_code' => 'EMP-SA-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        $this->morningShift = AttendanceTimeM::create([
            'name' => 'Morning Shift Test SA',
            'code' => 'morning_shift_test_sa',
            'shift_start_time' => '09:00:00',
            'shift_end_time' => '18:00:00',
            'required_work_minutes' => 480,
            'half_day_min_minutes' => 240,
            'lunch_break_minutes' => 60,
            'is_active' => true,
        ]);

        $this->eveningShift = AttendanceTimeM::create([
            'name' => 'Evening Shift Test SA',
            'code' => 'evening_shift_test_sa',
            'shift_start_time' => '14:00:00',
            'shift_end_time' => '22:00:00',
            'required_work_minutes' => 480,
            'half_day_min_minutes' => 240,
            'lunch_break_minutes' => 60,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_shift_assignment_page_and_resolve_sidebar_menu(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('employee.shift-assignment.index'));
        $response->assertOk();
        $response->assertSee('Employee Shift Assignment');

        $resolver = new SidebarMenuResolverS();
        $resolver->clearCache($this->adminUser->id);
        $resolvedMenus = $resolver->resolveForUser($this->adminUser);

        $hasShiftAssignmentMenu = false;
        foreach ($resolvedMenus as $parentId => $children) {
            foreach ($children as $child) {
                if (($child->route ?? '') === 'employee.shift-assignment.index') {
                    $hasShiftAssignmentMenu = true;
                    break 2;
                }
            }
        }
        $this->assertTrue($hasShiftAssignmentMenu, 'Shift Assignment menu should resolve dynamically for Admin user with permission.');
    }

    public function test_unauthorized_user_cannot_access_shift_assignment_page(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('employee.shift-assignment.index'));
        $response->assertRedirect();
    }

    public function test_admin_can_assign_and_update_employee_shift(): void
    {
        $storeResponse = $this->actingAs($this->adminUser)->post(route('employee.shift-assignment.store'), [
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $this->morningShift->id,
            'effective_from' => now()->toDateString(),
            'is_active' => 1,
        ]);

        $storeResponse->assertRedirect();
        $storeResponse->assertSessionHas('status');

        $assignment = EmployeeShiftTimingM::where('employee_id', $this->employee->id)->where('is_active', 1)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals($this->morningShift->id, $assignment->attendance_time_id);

        $updateResponse = $this->actingAs($this->adminUser)->put(route('employee.shift-assignment.update', $assignment->id), [
            'attendance_time_id' => $this->eveningShift->id,
            'effective_from' => now()->toDateString(),
            'effective_to' => now()->addMonth()->toDateString(),
            'is_active' => 1,
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('status');

        $assignment->refresh();
        $this->assertEquals($this->eveningShift->id, $assignment->attendance_time_id);
        $this->assertNotNull($assignment->effective_to);
    }
}
