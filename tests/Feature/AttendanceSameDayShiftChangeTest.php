<?php

namespace Tests\Feature;

use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\AttendanceTimeM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceSameDayShiftChangeTest extends TestCase
{
    use DatabaseTransactions;

    protected UserM $user;
    protected EmployeeM $employee;
    protected AttendanceTimeM $shiftA;
    protected AttendanceTimeM $shiftB;

    protected function setUp(): void
    {
        parent::setUp();

        $roleId = DB::table('roles')->where('slug', 'super_admin')->value('id')
            ?: DB::table('roles')->insertGetId(['name' => 'Super Admin', 'slug' => 'super_admin', 'created_at' => now(), 'updated_at' => now()]);

        $this->user = UserM::create([
            'name' => 'Test SameDay User',
            'email' => 'test_sameday_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $roleId,
            'is_active' => 1,
        ]);

        DB::table('user_roles')->insert(['user_id' => $this->user->id, 'role_id' => $roleId, 'created_at' => now(), 'updated_at' => now()]);

        $departmentId = DB::table('departments')->insertGetId([
            'name' => 'Testing Dept ' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $designationId = DB::table('designations')->insertGetId([
            'name' => 'Tester ' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->employee = EmployeeM::create([
            'user_id' => $this->user->id,
            'employee_code' => 'EMP-SD-' . rand(1000, 9999),
            'department_id' => $departmentId,
            'designation_id' => $designationId,
            'system_role_id' => $roleId,
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'work_schedule_type' => 'general',
            'employment_status' => 'active',
            'is_active' => 1,
            'joining_date' => '2026-01-01',
        ]);

        \App\Models\HRMS\Employee\EmployeeProfileM::create([
            'employee_id' => $this->employee->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        $this->shiftA = AttendanceTimeM::updateOrCreate(
            ['code' => 'general_shift'],
            [
                'name' => 'General Shift (Long)',
                'shift_type' => 'fixed',
                'punch_allowed_from' => '09:00:00',
                'shift_start_time' => '10:00:00',
                'late_after_time' => '10:05:00',
                'warning_after_time' => '10:15:00',
                'block_after_time' => '11:15:00',
                'shift_end_time' => '19:00:00',
                'required_work_minutes' => 480, // 8 hours
                'lunch_break_minutes' => 60,
                'break_minutes' => 60,
                'is_active' => true,
            ]
        );

        $this->shiftB = AttendanceTimeM::updateOrCreate(
            ['code' => 'half_day_shift'],
            [
                'name' => 'Half Day Shift (Short)',
                'shift_type' => 'fixed',
                'punch_allowed_from' => '08:00:00',
                'shift_start_time' => '09:00:00',
                'late_after_time' => '09:15:00',
                'warning_after_time' => '09:30:00',
                'block_after_time' => '14:00:00',
                'shift_end_time' => '17:00:00',
                'required_work_minutes' => 360, // 6 hours
                'lunch_break_minutes' => 60,
                'break_minutes' => 60,
                'is_active' => true,
            ]
        );

        // Assign Shift A to employee initially
        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $this->shiftA->id,
            'punch_allowed_from' => $this->shiftA->punch_allowed_from,
            'shift_start_time' => $this->shiftA->shift_start_time,
            'late_after_time' => $this->shiftA->late_after_time,
            'half_day_after_time' => '14:00:00',
            'block_after_time' => $this->shiftA->block_after_time,
            'shift_end_time' => $this->shiftA->shift_end_time,
            'required_work_minutes' => $this->shiftA->required_work_minutes,
            'lunch_minutes' => $this->shiftA->lunch_break_minutes,
            'effective_from' => '2026-01-01',
            'effective_to' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_same_day_shift_change_uses_active_shift_at_punch_in(): void
    {
        // 1. Employee punches in at 09:30 under Shift A rules
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 9, 30, 0, 'Asia/Kolkata'));

        $service = app(AttendanceS::class);
        $result = $service->processPunchIn($this->user->id, 'wfo', 'Punching under Shift A', [], null, null, false);
        $this->assertTrue($result['status']);

        $attendance = AttendanceM::where('employee_id', $this->employee->id)
            ->whereDate('attendance_date', '2026-05-22')
            ->first();
        $this->assertEquals($this->shiftA->id, $attendance->attendance_time_id);

        // 2. Admin updates the employee's shift assignment to Shift B in the afternoon (effective today)
        DB::table('employee_shift_timings')->where('employee_id', $this->employee->id)->update([
            'attendance_time_id' => $this->shiftB->id,
            'shift_end_time' => $this->shiftB->shift_end_time,
            'required_work_minutes' => $this->shiftB->required_work_minutes,
        ]);

        // 3. Employee punches out at 18:00. Under Shift A rules, required = 8 hrs + 1 hr break = 9 hrs, target = 18:30.
        // Punching out at 18:00 is early. Under Shift B rules, required = 6 hrs + 1 hr break = 7 hrs, target = 16:30.
        // Punching out at 18:00 is NOT early.
        // Since we resolved Shift A at punch-in, it should evaluate as early out!
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 18, 0, 0, 'Asia/Kolkata'));
        $resultOut = $service->processPunchOut($this->user->id, 'Finished shift', null, [], null, false);
        $this->assertTrue($resultOut['status']);

        $attendance->refresh();
        $this->assertTrue((bool) $attendance->is_early_out, 'Calculation should use Shift A (active at punch-in) and mark early out as true.');
    }

    public function test_shift_changed_before_punch_in_uses_new_shift(): void
    {
        // 1. Admin updates the employee's shift assignment to Shift B BEFORE they punch in
        DB::table('employee_shift_timings')->where('employee_id', $this->employee->id)->update([
            'attendance_time_id' => $this->shiftB->id,
            'shift_end_time' => $this->shiftB->shift_end_time,
            'required_work_minutes' => $this->shiftB->required_work_minutes,
        ]);

        // 2. Employee punches in at 09:30
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 9, 30, 0, 'Asia/Kolkata'));

        $service = app(AttendanceS::class);
        $result = $service->processPunchIn($this->user->id, 'wfo', 'Punching under Shift B', [], null, null, false);
        $this->assertTrue($result['status']);

        $attendance = AttendanceM::where('employee_id', $this->employee->id)
            ->whereDate('attendance_date', '2026-05-22')
            ->first();
        $this->assertEquals($this->shiftB->id, $attendance->attendance_time_id);

        // 3. Employee punches out at 18:00 (target is 16:30 under Shift B). Should NOT be early out.
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 18, 0, 0, 'Asia/Kolkata'));
        $resultOut = $service->processPunchOut($this->user->id, 'Finished shift', null, [], null, false);
        $this->assertTrue($resultOut['status']);

        $attendance->refresh();
        $this->assertFalse((bool) $attendance->is_early_out, 'Calculation should use Shift B and mark early out as false.');
    }

    public function test_shift_update_after_punch_in_defers_effective_date_to_next_day(): void
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();
        $tomorrow = Carbon::now('Asia/Kolkata')->addDay()->toDateString();

        // 1. Employee punches in today
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d H:i:s', $today . ' 09:30:00', 'Asia/Kolkata'));
        $service = app(AttendanceS::class);
        $service->processPunchIn($this->user->id, 'wfo', 'Punching in today', [], null, null, false);

        // 2. HR updates shift via controller
        $this->actingAs($this->user);
        $response = $this->put(route('hrms.employees.manage.update', $this->employee->id), [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'department_id' => $this->employee->department_id,
            'designation_id' => $this->employee->designation_id,
            'system_role_id' => $this->employee->system_role_id,
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'work_schedule_type' => 'half_day',
            'employment_status' => 'active',
            'joining_date' => $this->employee->joining_date,
            'shift_effective_from' => $today,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('warning');

        // Check employee_shift_timings
        $newTiming = DB::table('employee_shift_timings')
            ->where('employee_id', $this->employee->id)
            ->orderByDesc('id')
            ->first();

        $effectiveFromStr = is_string($newTiming->effective_from) ? $newTiming->effective_from : $newTiming->effective_from->toDateString();
        $this->assertEquals($tomorrow, $effectiveFromStr, 'New shift assignment effective_from must automatically defer to next calendar day.');

        $oldTiming = DB::table('employee_shift_timings')
            ->where('employee_id', $this->employee->id)
            ->where('effective_from', '2026-01-01')
            ->first();

        $this->assertEquals($today, $oldTiming->effective_to, 'Old shift assignment effective_to must remain active through current day.');
    }

    public function test_shift_update_before_punch_in_applies_immediately(): void
    {
        $today = Carbon::now('Asia/Kolkata')->toDateString();

        // HR updates shift via controller before employee punches in
        $this->actingAs($this->user);
        $response = $this->put(route('hrms.employees.manage.update', $this->employee->id), [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'department_id' => $this->employee->department_id,
            'designation_id' => $this->employee->designation_id,
            'system_role_id' => $this->employee->system_role_id,
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'work_schedule_type' => 'half_day',
            'employment_status' => 'active',
            'joining_date' => $this->employee->joining_date,
            'shift_effective_from' => $today,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('warning');

        // Check employee_shift_timings
        $newTiming = DB::table('employee_shift_timings')
            ->where('employee_id', $this->employee->id)
            ->orderByDesc('id')
            ->first();

        $effectiveFromStr = is_string($newTiming->effective_from) ? $newTiming->effective_from : $newTiming->effective_from->toDateString();
        $this->assertEquals($today, $effectiveFromStr, 'Shift assignment effective_from must apply immediately when punch-in does not exist.');
    }
}
