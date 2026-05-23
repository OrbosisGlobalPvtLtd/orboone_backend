<?php

namespace Tests\Feature;

use App\Models\Core\UserM;
use App\Models\Core\RoleM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Attendance\AttendanceTypeM as AttendanceType;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use App\Models\HRMS\Attendance\AttendanceWorkLogM;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Carbon\Carbon;

class StructuredPunchOutTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $user;
    private EmployeeM $employee;
    private AttendanceType $presentType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Roles
        $employeeRole = RoleM::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee', 'id' => 7]);

        // Create User
        $this->user = UserM::create([
            'name' => 'Test Structured Employee',
            'email' => 'structured_employee_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $employeeRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->user->roles()->sync([$employeeRole->id]);

        // Create Employee
        $this->employee = EmployeeM::create([
            'user_id' => $this->user->id,
            'employee_code' => 'EMP-STR-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfh',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        // Create Employee Profile
        EmployeeProfileM::create([
            'employee_id' => $this->employee->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        // Create Attendance Types
        $this->presentType = AttendanceType::firstOrCreate(['code' => 'present'], ['name' => 'Present', 'is_active' => true]);

        // Create a default Shift
        AttendanceTime::firstOrCreate(
            ['code' => 'default_shift'],
            [
                'name' => 'Default Shift',
                'is_default' => true,
                'is_active' => true,
                'punch_allowed_from' => '08:00:00',
                'shift_start_time' => '09:00:00',
                'late_after_time' => '09:15:00',
                'warning_after_time' => '09:30:00',
                'block_after_time' => '10:00:00',
                'shift_end_time' => '18:00:00',
            ]
        );
    }

    public function test_task_summary_validation_rules(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 23, 18, 0, 0, 'Asia/Kolkata'));

        // Create punch-in record first
        Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-23',
            'punch_in_time' => '09:00:00',
            'work_mode' => 'wfh',
            'attendance_status' => 'present',
            'attendance_type_id' => $this->presentType->id,
            'is_locked' => false,
        ]);

        Sanctum::actingAs($this->user);

        // 1. Check required validation
        $response = $this->postJson('/api/v1/hrms/attendance/punch-out', [
            'task_summary_json' => ['Task 1', 'Task 2']
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['task_summary']);

        // 2. Check min length 5 validation
        $response = $this->postJson('/api/v1/hrms/attendance/punch-out', [
            'task_summary' => 'Abcd',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['task_summary']);

        // 3. Check max length 10000 validation (over 10000 characters)
        $response = $this->postJson('/api/v1/hrms/attendance/punch-out', [
            'task_summary' => str_repeat('a', 10001),
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['task_summary']);

        // 4. Check task_summary_json must be array validation
        $response = $this->postJson('/api/v1/hrms/attendance/punch-out', [
            'task_summary' => 'Valid task summary',
            'task_summary_json' => 'not an array'
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['task_summary_json']);
    }

    public function test_punch_out_saves_both_summary_and_json(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 23, 18, 0, 0, 'Asia/Kolkata'));

        // Create punch-in record first
        $attendance = Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-23',
            'punch_in_time' => '09:00:00',
            'work_mode' => 'wfh',
            'attendance_status' => 'present',
            'attendance_type_id' => $this->presentType->id,
            'is_locked' => false,
        ]);

        Sanctum::actingAs($this->user);

        $jsonPayload = [
            ['task' => 'Implement structured punch-out', 'status' => 'completed'],
            ['task' => 'Write tests for the task', 'status' => 'completed']
        ];

        $response = $this->postJson('/api/v1/hrms/attendance/punch-out', [
            'task_summary' => 'Completed all tasks including testing structured punch-out.',
            'task_summary_json' => $jsonPayload
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Verify the saved work log
        $workLog = AttendanceWorkLogM::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($workLog);
        $this->assertEquals('Completed all tasks including testing structured punch-out.', $workLog->work_summary);
        $this->assertEquals($jsonPayload, $workLog->work_summary_json);
    }

    public function test_punch_out_saves_null_when_json_not_provided(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 23, 18, 0, 0, 'Asia/Kolkata'));

        // Create punch-in record first
        $attendance = Attendance::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_date' => '2026-05-23',
            'punch_in_time' => '09:00:00',
            'work_mode' => 'wfh',
            'attendance_status' => 'present',
            'attendance_type_id' => $this->presentType->id,
            'is_locked' => false,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/hrms/attendance/punch-out', [
            'task_summary' => 'Completed all tasks without sending json.',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Verify the saved work log
        $workLog = AttendanceWorkLogM::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($workLog);
        $this->assertEquals('Completed all tasks without sending json.', $workLog->work_summary);
        $this->assertNull($workLog->work_summary_json);
    }
}
