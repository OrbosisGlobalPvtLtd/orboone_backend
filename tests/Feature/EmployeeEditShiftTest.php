<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\AttendanceTimeM;
use App\Models\HRMS\Department\DepartmentM;
use App\Models\HRMS\Designation\DesignationM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Models\HRMS\Employee\EmployeeShiftTimingM;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmployeeEditShiftTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $adminUser;
    private UserM $empUser;
    private EmployeeM $employee;
    private AttendanceTimeM $shift1;
    private AttendanceTimeM $shift2;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = RoleM::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin', 'id' => 1]);
        $this->adminUser = UserM::create([
            'name' => 'Admin User',
            'email' => 'admin_edit_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $adminRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->adminUser->roles()->sync([$adminRole->id]);

        $employeeRole = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);
        $this->empUser = UserM::create([
            'name' => 'Edit Test Employee',
            'email' => 'edit_test_emp_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $employeeRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->empUser->roles()->sync([$employeeRole->id]);

        $dept = DepartmentM::firstOrCreate(['name' => 'Engineering']);
        $desig = DesignationM::firstOrCreate(['name' => 'Software Engineer']);

        $this->shift1 = AttendanceTimeM::firstOrCreate(
            ['code' => 'general_shift'],
            [
                'name' => 'General Shift',
                'shift_type' => 'fixed',
                'punch_allowed_from' => '08:00:00',
                'shift_start_time' => '09:00:00',
                'late_after_time' => '09:15:00',
                'half_day_after_time' => '11:00:00',
                'block_after_time' => '12:00:00',
                'shift_end_time' => '18:00:00',
                'required_work_minutes' => 480,
                'lunch_break_minutes' => 60,
                'is_active' => 1,
                'is_default' => 1,
            ]
        );

        $this->shift2 = AttendanceTimeM::firstOrCreate(
            ['code' => 'part_time_morning'],
            [
                'name' => 'Part Time Morning Shift',
                'shift_type' => 'fixed',
                'punch_allowed_from' => '07:00:00',
                'shift_start_time' => '08:00:00',
                'late_after_time' => '08:15:00',
                'half_day_after_time' => '10:00:00',
                'block_after_time' => '11:00:00',
                'shift_end_time' => '13:00:00',
                'required_work_minutes' => 240,
                'lunch_break_minutes' => 30,
                'is_active' => 1,
                'is_default' => 0,
            ]
        );

        $this->employee = EmployeeM::create([
            'user_id' => $this->empUser->id,
            'employee_code' => 'EMP-EDIT-' . rand(1000, 9999),
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'employment_type' => 'full_time',
            'employee_stage' => 'permanent',
            'work_mode' => 'wfo',
            'work_schedule_type' => 'general_shift',
            'joining_date' => '2026-01-01',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        EmployeeProfileM::create([
            'employee_id' => $this->employee->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        EmployeeShiftTimingM::create([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $this->shift1->id,
            'punch_allowed_from' => $this->shift1->punch_allowed_from,
            'shift_start_time' => $this->shift1->shift_start_time,
            'late_after_time' => $this->shift1->late_after_time,
            'half_day_after_time' => $this->shift1->half_day_after_time,
            'block_after_time' => $this->shift1->block_after_time,
            'shift_end_time' => $this->shift1->shift_end_time,
            'required_work_minutes' => $this->shift1->required_work_minutes,
            'lunch_minutes' => $this->shift1->lunch_break_minutes,
            'effective_from' => '2026-01-01',
            'is_active' => 1,
        ]);
    }

    public function test_case_1_edit_employee_keeping_same_shift_succeeds_without_debug_error(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->put("/hrms/employees/{$this->employee->id}", [
            'name' => 'Edit Test Employee Updated',
            'email' => $this->empUser->email,
            'department_id' => $this->employee->department_id,
            'designation_id' => $this->employee->designation_id,
            'system_role_id' => 7,
            'employment_type' => 'full_time',
            'employee_stage' => 'permanent',
            'work_mode' => 'wfo',
            'work_schedule_type' => $this->shift1->code,
            'joining_date' => '2026-01-01',
            'employment_status' => 'active',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $activeTiming = EmployeeShiftTimingM::where('employee_id', $this->employee->id)->where('is_active', 1)->first();
        $this->assertNotNull($activeTiming);
        $this->assertEquals($this->shift1->id, $activeTiming->attendance_time_id);
    }

    public function test_case_2_edit_employee_changing_shift_saves_new_shift_successfully(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->put("/hrms/employees/{$this->employee->id}", [
            'name' => 'Edit Test Employee Updated',
            'email' => $this->empUser->email,
            'department_id' => $this->employee->department_id,
            'designation_id' => $this->employee->designation_id,
            'system_role_id' => 7,
            'employment_type' => 'full_time',
            'employee_stage' => 'permanent',
            'work_mode' => 'wfo',
            'work_schedule_type' => $this->shift2->code,
            'joining_date' => '2026-01-01',
            'employment_status' => 'active',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $activeTiming = EmployeeShiftTimingM::where('employee_id', $this->employee->id)->where('is_active', 1)->first();
        $this->assertNotNull($activeTiming);
        $this->assertEquals($this->shift2->id, $activeTiming->attendance_time_id);
    }

    public function test_case_3_edit_employee_with_empty_shift_selection_handles_gracefully(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->put("/hrms/employees/{$this->employee->id}", [
            'name' => 'Edit Test Employee Updated',
            'email' => $this->empUser->email,
            'department_id' => $this->employee->department_id,
            'designation_id' => $this->employee->designation_id,
            'system_role_id' => 7,
            'employment_type' => 'full_time',
            'employee_stage' => 'permanent',
            'work_mode' => 'wfo',
            'work_schedule_type' => '',
            'joining_date' => '2026-01-01',
            'employment_status' => 'active',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $activeTiming = EmployeeShiftTimingM::where('employee_id', $this->employee->id)->where('is_active', 1)->first();
        $this->assertNotNull($activeTiming);
    }
}
