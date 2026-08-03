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
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FlexiblePartTimeShiftTest extends TestCase
{
    use DatabaseTransactions;

    protected UserM $user;
    protected EmployeeM $employee;
    protected AttendanceTimeM $flexibleShift;
    protected AttendanceTimeM $generalShift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserM::create([
            'name' => 'Test Flexible User',
            'email' => 'test_flexible_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);

        $this->employee = EmployeeM::create([
            'user_id' => $this->user->id,
            'employee_code' => 'EMP-FLEX-' . rand(1000, 9999),
            'employment_type' => 'part_time',
            'work_mode' => 'wfo',
            'employment_status' => 'active',
            'is_active' => 1,
        ]);

        $this->flexibleShift = AttendanceTimeM::updateOrCreate(
            ['code' => 'flexible_part_time'],
            [
                'name' => 'Flexible Part Time',
                'shift_type' => 'flexible_part_time',
                'punch_allowed_from' => '00:00:00',
                'shift_start_time' => '00:00:00',
                'late_after_time' => '23:59:59',
                'shift_end_time' => '23:59:59',
                'required_work_minutes' => 300,
                'half_day_min_minutes' => 180,
                'absent_below_minutes' => 90,
                'lunch_break_minutes' => 0,
                'break_minutes' => 0,
                'is_active' => true,
                'is_default' => false,
            ]
        );

        $policyRuleId = DB::table('attendance_policy_rules')->insertGetId([
            'policy_name' => 'Flexible Part Time Policy',
            'shift_type' => 'flexible_part_time',
            'required_work_minutes' => 300,
            'half_day_min_minutes' => 180,
            'absent_below_minutes' => 90,
            'lunch_break_minutes' => 0,
            'allowed_missed_punches' => 2,
            'combined_violation_limit' => 3,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (Schema::hasTable('employee_policy_assignments')) {
            DB::table('employee_policy_assignments')->updateOrInsert(
                ['employee_id' => $this->employee->id],
                ['policy_id' => $policyRuleId, 'policy_type' => 'attendance', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function test_flexible_shift_target_punch_out_generated_dynamically(): void
    {
        Carbon::setTestNow('2026-06-01 13:20:00');
        $service = app(AttendanceS::class);

        $result = $service->processPunchIn($this->user->id, 'wfo', null, [
            'latitude' => '28.6139',
            'longitude' => '77.2090',
        ], '2026-06-01 13:20:00', null, false);

        $this->assertEquals('success', $result['status'] ?? null);
        $att = AttendanceM::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-06-01')->first();

        $this->assertNotNull($att);
        $this->assertFalse((bool) $att->is_late);
        $this->assertEquals(0, (int) $att->late_minutes);
        $this->assertEquals('18:20:00', $att->target_punch_out_time);
    }

    public function test_flexible_shift_punch_in_at_any_time_is_never_marked_late(): void
    {
        Carbon::setTestNow('2026-06-01 14:30:00');
        $service = app(AttendanceS::class);

        $result = $service->processPunchIn($this->user->id, 'wfo', null, [
            'latitude' => '28.6139',
            'longitude' => '77.2090',
        ], '2026-06-01 14:30:00', null, false);

        $this->assertEquals('success', $result['status'] ?? null);
        $att = AttendanceM::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-06-01')->first();

        $this->assertNotNull($att);
        $this->assertFalse((bool) $att->is_late);
        $this->assertEquals(0, (int) $att->late_minutes);
        $this->assertEquals('19:30:00', $att->target_punch_out_time);
    }

    public function test_flexible_shift_early_logout_creates_violation(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();
        $service = app(AttendanceS::class);

        // Target out is 18:00:00 (13:00 + 300 mins), punch out at 17:00:00 (240 mins worked, 60 mins early out)
        $att = AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_time_id' => $this->flexibleShift->id,
            'attendance_date' => '2026-06-02',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'punch_in_time' => '13:00:00',
            'target_punch_out_time' => '18:00:00',
            'punch_out_time' => '17:00:00',
        ]);

        $service->calculateWorkingHours($att);
        $att->refresh();

        $this->assertTrue((bool) $att->is_early_out);
        $this->assertEquals(60, (int) $att->early_out_minutes);

        $violationExists = DB::table('attendance_violations')
            ->where('employee_id', $this->employee->id)
            ->where('type', 'early_logout')
            ->whereDate('violation_date', '2026-06-02')
            ->exists();

        $this->assertTrue($violationExists);
    }

    public function test_flexible_shift_3_early_logout_violations_trigger_half_day(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();
        $service = app(AttendanceS::class);

        // 3 days of early logouts (worked 240 mins out of 300 required)
        for ($i = 1; $i <= 3; $i++) {
            $att = AttendanceM::create([
                'employee_id' => $this->employee->id,
                'user_id' => $this->user->id,
                'attendance_time_id' => $this->flexibleShift->id,
                'attendance_date' => "2026-06-0{$i}",
                'attendance_type_id' => $presentType->id,
                'attendance_status' => 'present',
                'punch_in_time' => '13:00:00',
                'target_punch_out_time' => '18:00:00',
                'punch_out_time' => '17:00:00',
            ]);
            $service->calculateWorkingHours($att);
        }

        $lastAtt = AttendanceM::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-06-03')->first();
        $this->assertNotNull($lastAtt);
        $this->assertEquals('half_day', $lastAtt->attendance_status);
        $this->assertTrue((bool) $lastAtt->is_half_day);
        $this->assertStringContainsString('Attendance Discipline', (string) $lastAtt->half_day_reason);
    }

    public function test_flexible_shift_full_working_hours_results_in_present(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();
        $service = app(AttendanceS::class);

        // Employee works 315 minutes (5.25 hrs), required is 300 mins
        $att = AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_time_id' => $this->flexibleShift->id,
            'attendance_date' => '2026-06-02',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'punch_in_time' => '11:00:00',
            'punch_out_time' => '16:15:00',
        ]);

        $service->calculateWorkingHours($att);
        $att->refresh();

        $this->assertEquals(315, (int) $att->total_work_minutes);
        $this->assertEquals('present', $att->attendance_status);
        $this->assertFalse((bool) $att->is_late);
        $this->assertFalse((bool) $att->is_early_out);
        $this->assertFalse((bool) $att->is_half_day);
        $this->assertFalse((bool) $att->is_lwp);
    }

    public function test_flexible_shift_partial_working_hours_results_in_half_day(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();
        $service = app(AttendanceS::class);

        // Employee works 210 minutes (3.5 hrs), half_day_min is 180, required is 300
        $att = AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_time_id' => $this->flexibleShift->id,
            'attendance_date' => '2026-06-03',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'punch_in_time' => '13:00:00',
            'punch_out_time' => '16:30:00',
        ]);

        $service->calculateWorkingHours($att);
        $att->refresh();

        $this->assertEquals(210, (int) $att->total_work_minutes);
        $this->assertEquals('half_day', $att->attendance_status);
        $this->assertTrue((bool) $att->is_half_day);
        $this->assertFalse((bool) $att->is_lwp);
    }

    public function test_flexible_shift_insufficient_working_hours_results_in_lwp(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();
        $service = app(AttendanceS::class);

        // Employee works 60 minutes (1 hr), absent_below is 90 mins
        $att = AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_time_id' => $this->flexibleShift->id,
            'attendance_date' => '2026-06-04',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'punch_in_time' => '15:00:00',
            'punch_out_time' => '16:00:00',
        ]);

        $service->calculateWorkingHours($att);
        $att->refresh();

        $this->assertEquals(60, (int) $att->total_work_minutes);
        $this->assertEquals('lwp', $att->attendance_status);
        $this->assertTrue((bool) $att->is_lwp);
        $this->assertFalse((bool) $att->is_half_day);
    }

    public function test_flexible_shift_does_not_accumulate_late_or_early_violations(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();
        $service = app(AttendanceS::class);

        // 3 days with odd punch times
        for ($i = 1; $i <= 3; $i++) {
            $att = AttendanceM::create([
                'employee_id' => $this->employee->id,
                'user_id' => $this->user->id,
                'attendance_time_id' => $this->flexibleShift->id,
                'attendance_date' => "2026-06-0{$i}",
                'attendance_type_id' => $presentType->id,
                'attendance_status' => 'present',
                'punch_in_time' => '16:00:00',
                'punch_out_time' => '21:30:00', // 330 mins
            ]);
            $service->calculateWorkingHours($att);
        }

        $summary = $service->getEmployeeViolationSummary($this->employee, '2026-06-03');
        $this->assertEquals(0, $summary['discipline']['count']);
    }

    public function test_shift_change_applies_immediately_to_future_punches_without_affecting_historical_attendance(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();
        $service = app(AttendanceS::class);

        $generalShift = AttendanceTimeM::where('code', 'general_shift')->first() ?: AttendanceTimeM::first();

        // Past record under Fixed shift on 2026-05-01
        $pastAtt = AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_time_id' => $generalShift?->id,
            'attendance_date' => '2026-05-01',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'punch_in_time' => '10:00:00',
            'punch_out_time' => '18:00:00',
            'total_work_minutes' => 480,
            'is_late' => false,
            'is_early_out' => false,
        ]);

        // Reassign policy assignment starting 2026-06-01 to flexible_part_time
        if (Schema::hasTable('employee_policy_assignments')) {
            DB::table('employee_policy_assignments')->where('employee_id', $this->employee->id)->update(['is_active' => 0, 'effective_to' => '2026-05-31']);
            DB::table('employee_policy_assignments')->insert([
                'employee_id' => $this->employee->id,
                'policy_type' => 'attendance',
                'policy_id' => DB::table('attendance_policy_rules')->where('shift_type', 'flexible_part_time')->value('id'),
                'effective_from' => '2026-06-01',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Punch in on 2026-06-01 at 14:00 (under flexible shift)
        $result = $service->processPunchIn($this->user->id, 'wfo', null, [
            'latitude' => '28.6139',
            'longitude' => '77.2090',
        ], '2026-06-01 14:00:00', null, false);

        $this->assertEquals('success', $result['status'] ?? null);
        $newAtt = AttendanceM::where('employee_id', $this->employee->id)->whereDate('attendance_date', '2026-06-01')->first();
        $this->assertNotNull($newAtt);
        $this->assertFalse((bool) $newAtt->is_late);

        // Verify past record remains unchanged
        $pastAtt->refresh();
        $this->assertEquals(480, (int) $pastAtt->total_work_minutes);
        $this->assertEquals('2026-05-01', $pastAtt->attendance_date->toDateString());
    }

    public function test_same_day_shift_change_preserves_policy_used_at_punch_in(): void
    {
        $presentType = AttendanceTypeM::where('code', 'present')->firstOrFail();
        $service = app(AttendanceS::class);
        $generalShift = AttendanceTimeM::where('code', 'general_shift')->first() ?: AttendanceTimeM::first();

        // 10:00 AM: Employee Punches In under General Shift
        $att = AttendanceM::create([
            'employee_id' => $this->employee->id,
            'user_id' => $this->user->id,
            'attendance_time_id' => $generalShift?->id,
            'attendance_date' => '2026-06-15',
            'attendance_type_id' => $presentType->id,
            'attendance_status' => 'present',
            'punch_in_time' => '10:00:00',
        ]);

        // 2:00 PM: Admin changes employee shift to flexible_part_time
        if (Schema::hasTable('employee_policy_assignments')) {
            DB::table('employee_policy_assignments')->where('employee_id', $this->employee->id)->update(['is_active' => 0, 'effective_to' => '2026-06-14']);
            DB::table('employee_policy_assignments')->insert([
                'employee_id' => $this->employee->id,
                'policy_type' => 'attendance',
                'policy_id' => DB::table('attendance_policy_rules')->where('shift_type', 'flexible_part_time')->value('id'),
                'effective_from' => '2026-06-15',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 6:00 PM: Employee Punches Out
        $att->punch_out_time = '18:00:00';
        $service->calculateWorkingHours($att);
        $att->refresh();

        // Must preserve attendance_time_id set at punch in
        $this->assertEquals($generalShift?->id, $att->attendance_time_id);
        $this->assertEquals(480, (int) $att->total_work_minutes);
    }
}
