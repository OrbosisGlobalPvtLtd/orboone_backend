<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\AttendanceTimeM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeShiftTimingM;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmployeeShiftTimingManagementTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $adminUser;
    private EmployeeM $employee;
    private AttendanceTimeM $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = RoleM::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin', 'id' => 1]);

        $this->adminUser = UserM::create([
            'name' => 'Shift Admin Test',
            'email' => 'shift_admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $adminRole->id,
            'is_active' => 1,
            'is_web_access' => 1,
        ]);
        $this->adminUser->roles()->sync([$adminRole->id]);

        $empUser = UserM::create([
            'name' => 'Emp Shift User',
            'email' => 'emp_shift_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => 7,
            'is_active' => 1,
        ]);

        $this->employee = EmployeeM::create([
            'user_id' => $empUser->id,
            'employee_code' => 'EMP-SHIFT-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        $this->shift = AttendanceTimeM::create([
            'name' => 'Morning Shift Test',
            'code' => 'morning_shift_test',
            'shift_start_time' => '09:00:00',
            'shift_end_time' => '18:00:00',
            'required_work_minutes' => 480,
            'half_day_min_minutes' => 240,
            'lunch_break_minutes' => 60,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_assign_and_update_employee_shift_timing(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('attendance.employee_shifts.store'), [
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $this->shift->id,
            'effective_from' => now()->toDateString(),
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $assignment = EmployeeShiftTimingM::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals($this->shift->id, $assignment->attendance_time_id);
        $this->assertTrue((bool) $assignment->is_active);

        $updateResponse = $this->actingAs($this->adminUser)->put(route('attendance.employee_shifts.update', $assignment->id), [
            'attendance_time_id' => $this->shift->id,
            'effective_from' => now()->toDateString(),
            'effective_to' => now()->addMonth()->toDateString(),
            'is_active' => 1,
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('status');

        $assignment->refresh();
        $this->assertNotNull($assignment->effective_to);
    }
}
