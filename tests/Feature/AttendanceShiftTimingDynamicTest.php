<?php

namespace Tests\Feature;

use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\AttendanceTimeM;
use App\Models\HRMS\Attendance\AttendanceTypeM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\AttendanceS;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceShiftTimingDynamicTest extends TestCase
{
    use DatabaseTransactions;

    protected UserM $user;
    protected EmployeeM $employee;
    protected AttendanceTimeM $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserM::create([
            'name' => 'Test Dynamic User',
            'email' => 'test_dyn_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $this->employee = EmployeeM::create([
            'user_id' => $this->user->id,
            'employee_code' => 'EMP-DYN-' . rand(1000, 9999),
            'employment_type' => 'full_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
            'joining_date' => '2026-01-01',
        ]);

        \App\Models\HRMS\Employee\EmployeeProfileM::create([
            'employee_id' => $this->employee->id,
            'profile_status' => 'approved',
            'is_profile_completed' => true,
        ]);

        $this->shift = AttendanceTimeM::updateOrCreate(
            ['code' => 'dyn_shift'],
            [
                'name' => 'Dynamic Fixed Shift',
                'shift_type' => 'fixed',
                'punch_allowed_from' => '09:00:00',
                'shift_start_time' => '10:00:00',
                'late_after_time' => '10:05:00',
                'warning_after_time' => '10:15:00',
                'block_after_time' => '11:15:00',
                'shift_end_time' => '19:00:00',
                'required_work_minutes' => 480,
                'lunch_break_minutes' => 60,
                'break_minutes' => 60,
                'is_active' => true,
            ]
        );

        // Assign shift timing to employee (version 1: active since 2026-01-01)
        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $this->shift->id,
            'punch_allowed_from' => '09:00:00',
            'shift_start_time' => '10:00:00',
            'late_after_time' => '10:05:00',
            'half_day_after_time' => '14:00:00',
            'block_after_time' => '11:15:00',
            'shift_end_time' => '19:00:00',
            'required_work_minutes' => 480,
            'lunch_minutes' => 60,
            'effective_from' => '2026-01-01',
            'effective_to' => '2026-05-22',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_calculations_are_resolved_dynamically_from_correct_versioned_shift(): void
    {
        // 1. Employee punches in on 2026-05-22 (under version 1 shift, shift_end_time = 19:00)
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 9, 30, 0, 'Asia/Kolkata'));

        $service = app(AttendanceS::class);
        $result = $service->processPunchIn($this->user->id, 'wfo', 'Test dynamic note', [], null, null, false);

        $this->assertTrue($result['status']);

        // Punch out at 18:00 (since version 1 required lunch + work minutes = 540 min = 9 hrs, target punch out is 18:30. Punch out at 18:00 is early)
        Carbon::setTestNow(Carbon::create(2026, 5, 22, 18, 0, 0, 'Asia/Kolkata'));
        $resultOut = $service->processPunchOut($this->user->id, 'Finished dynamic early task', null, [], null, false);

        $this->assertTrue($resultOut['status']);

        $attendance = AttendanceM::where('employee_id', $this->employee->id)
            ->whereDate('attendance_date', '2026-05-22')
            ->first();

        // Should be marked as early logout under version 1 rules
        $this->assertTrue((bool) $attendance->is_early_out);

        // 2. Insert a new shift timing assignment for the employee starting 2026-05-23 (version 2: shift ends early, required work is shorter)
        DB::table('employee_shift_timings')->insert([
            'employee_id' => $this->employee->id,
            'attendance_time_id' => $this->shift->id,
            'punch_allowed_from' => '09:00:00',
            'shift_start_time' => '10:00:00',
            'late_after_time' => '10:05:00',
            'half_day_after_time' => '14:00:00',
            'block_after_time' => '11:15:00',
            'shift_end_time' => '17:00:00',
            'required_work_minutes' => 360, // 6 hours
            'lunch_minutes' => 60,
            'effective_from' => '2026-05-23',
            'effective_to' => null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Recalculate 2026-05-22 attendance stats. It should STILL resolve version 1 shift timings, keeping is_early_out = true
        $recalculated = $service->calculateWorkingHours($attendance);
        $this->assertTrue((bool) $recalculated->is_early_out, 'Recalculation on 22nd May should use version 1 shift timing where end is 19:00.');

        // 4. Punch in on 2026-05-25 (should resolve version 2 shift timing, where target punch out is 9:30 + 7 hrs = 16:30)
        Carbon::setTestNow(Carbon::create(2026, 5, 25, 9, 30, 0, 'Asia/Kolkata'));
        $resultIn2 = $service->processPunchIn($this->user->id, 'wfo', 'Test version 2', [], null, null, false);
        $this->assertTrue($resultIn2['status']);

        // Punch out at 17:00 on 2026-05-25 (since target is 16:30, punching out at 17:00 should NOT be early out)
        Carbon::setTestNow(Carbon::create(2026, 5, 25, 17, 0, 0, 'Asia/Kolkata'));
        $resultOut2 = $service->processPunchOut($this->user->id, 'Finished version 2 task', null, [], null, false);
        $this->assertTrue($resultOut2['status']);

        $attendance2 = AttendanceM::where('employee_id', $this->employee->id)
            ->whereDate('attendance_date', '2026-05-25')
            ->first();

        $this->assertFalse((bool) $attendance2->is_early_out, 'Punching out at 17:00 under version 2 rules should not be early out.');
    }
}
