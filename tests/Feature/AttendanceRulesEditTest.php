<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Models\HRMS\Attendance\AttendancePolicyRuleM as AttendancePolicyRule;
use App\Models\HRMS\Attendance\AttendanceTimeM as AttendanceTime;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AttendanceRulesEditTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = RoleM::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin', 'id' => 1]);
        $this->adminUser = UserM::create([
            'name' => 'Rules Admin Test',
            'email' => 'rules_admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $role->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->adminUser->roles()->sync([$role->id]);
    }

    public function test_shift_rule_can_be_updated_successfully(): void
    {
        $shift = AttendanceTime::create([
            'name' => 'General Shift Test',
            'code' => 'SHIFT_TEST_' . rand(1000, 9999),
            'punch_allowed_from' => '08:00:00',
            'shift_start_time' => '09:00:00',
            'late_after_time' => '09:15:00',
            'half_day_after_time' => '11:00:00',
            'shift_end_time' => '18:00:00',
            'required_work_minutes' => 480,
            'half_day_min_minutes' => 240,
            'lunch_break_minutes' => 60,
            'is_default' => 0,
            'is_active' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('attendance.rules.update', $shift), [
                'name' => 'Updated Shift Test',
                'punch_allowed_from' => '07:30',
                'shift_start_time' => '08:30',
                'late_after_time' => '08:45',
                'half_day_after_time' => '10:30',
                'shift_end_time' => '17:30',
                'required_work_minutes' => 500,
                'half_day_min_minutes' => 250,
                'lunch_break_minutes' => 45,
                'is_default' => 1,
                'is_active' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Shift rule updated successfully.');

        $shift->refresh();
        $this->assertSame('Updated Shift Test', $shift->name);
        $this->assertSame('07:30', substr($shift->punch_allowed_from, 0, 5));
        $this->assertSame('10:30', substr($shift->half_day_after_time, 0, 5));
        $this->assertSame(500, (int) $shift->required_work_minutes);
        $this->assertSame(45, (int) $shift->lunch_break_minutes);
        $this->assertTrue((bool) $shift->is_default);
    }

    public function test_attendance_policy_rule_can_be_created_and_updated(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('attendance.policy_rules.store'), [
                'policy_name' => 'Test Policy Rule',
                'punch_allowed_from' => '08:00',
                'shift_start_time' => '09:00',
                'late_after_time' => '09:15',
                'warning_after_time' => '09:30',
                'block_after_time' => '10:00',
                'shift_end_time' => '18:00',
                'required_work_minutes' => 480,
                'half_day_min_minutes' => 240,
                'absent_below_minutes' => 240,
                'lunch_break_minutes' => 60,
                'allowed_missed_punches' => 2,
                'combined_violation_limit' => 3,
                'late_violation_limit' => 3,
                'early_violation_limit' => 3,
                'auto_block_enabled' => 1,
                'auto_absent_enabled' => 1,
                'is_active' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Attendance policy rule created successfully.');

        $policy = AttendancePolicyRule::where('policy_name', 'Test Policy Rule')->firstOrFail();
        $this->assertSame('08:00', substr($policy->punch_allowed_from, 0, 5));

        $updateResponse = $this->actingAs($this->adminUser)
            ->put(route('attendance.policy_rules.update', $policy), [
                'policy_name' => 'Updated Test Policy Rule',
                'punch_allowed_from' => '07:45',
                'shift_start_time' => '08:45',
                'late_after_time' => '09:00',
                'warning_after_time' => '09:15',
                'block_after_time' => '09:45',
                'shift_end_time' => '17:45',
                'required_work_minutes' => 490,
                'half_day_min_minutes' => 245,
                'absent_below_minutes' => 245,
                'lunch_break_minutes' => 50,
                'allowed_missed_punches' => 3,
                'combined_violation_limit' => 4,
                'late_violation_limit' => 4,
                'early_violation_limit' => 4,
                'auto_block_enabled' => 1,
                'auto_absent_enabled' => 1,
                'is_active' => 1,
            ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('status', 'Attendance policy rule updated successfully.');

        $policy->refresh();
        $this->assertSame('Updated Test Policy Rule', $policy->policy_name);
        $this->assertSame('07:45', substr($policy->punch_allowed_from, 0, 5));
        $this->assertSame(490, (int) $policy->required_work_minutes);
    }
}
