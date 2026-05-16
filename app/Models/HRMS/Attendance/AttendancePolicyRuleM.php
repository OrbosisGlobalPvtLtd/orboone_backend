<?php

namespace App\Models\HRMS\Attendance;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendancePolicyRuleM extends Model
{
    use HasFactory;

    protected $table = 'attendance_policy_rules';

    protected $guarded = [];

    protected $casts = [
        'required_work_minutes' => 'integer',
        'half_day_min_minutes' => 'integer',
        'absent_below_minutes' => 'integer',
        'lunch_break_minutes' => 'integer',
        'allowed_missed_punches' => 'integer',
        'combined_violation_limit' => 'integer',
        'late_violation_limit' => 'integer',
        'early_violation_limit' => 'integer',
        'auto_block_enabled' => 'boolean',
        'auto_absent_enabled' => 'boolean',
        'mobile_only_punch' => 'boolean',
        'web_punch_disabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function employees()
    {
        return $this->hasMany(EmployeeM::class, 'attendance_policy_rule_id');
    }
}
