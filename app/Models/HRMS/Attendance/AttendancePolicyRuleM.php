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
        'is_active' => 'boolean',
        'attendance_tracking_enabled' => 'boolean',
        'punch_in_required' => 'boolean',
        'punch_out_required' => 'boolean',

        'allow_web_punch' => 'boolean',
        'allow_mobile_punch' => 'boolean',
        'mobile_only_punch' => 'boolean',
        'web_punch_disabled' => 'boolean',

        'late_mark_enabled' => 'boolean',
        'late_mark_limit' => 'integer',
        'late_violation_enabled' => 'boolean',
        'late_violation_limit' => 'integer',
        'late_lwp_after' => 'integer',
        'grace_late_allowed' => 'integer',

        'early_out_enabled' => 'boolean',
        'early_out_limit' => 'integer',
        'early_violation_enabled' => 'boolean',
        'early_violation_limit' => 'integer',
        'early_out_half_day_minutes' => 'integer',

        'half_day_enabled' => 'boolean',
        'half_day_min_minutes' => 'integer',
        'absent_below_minutes' => 'integer',

        'allowed_missed_punches' => 'integer',
        'missed_punch_after_minutes' => 'integer',
        'missed_punch_action' => 'string',
        'missed_punch_lwp_after' => 'integer',
        'auto_lwp_for_missed_punch' => 'boolean',

        'combined_violation_enabled' => 'boolean',
        'combined_violation_limit' => 'integer',
        'combined_violation_action' => 'string',

        'auto_absent_enabled' => 'boolean',
        'punch_block_enabled' => 'boolean',

        'regularization_enabled' => 'boolean',
        'regularization_days' => 'integer',
        'regularization_requires_approval' => 'boolean',

        'wfh_enabled' => 'boolean',
        'monthly_wfh_limit' => 'integer',
        'manager_approval_required' => 'boolean',
        'internet_proof_required' => 'boolean',
        'speed_test_required' => 'boolean',
        'internet_issue_lwp' => 'boolean',
        'electricity_issue_lwp' => 'boolean',
    ];

    public function getAutoBlockEnabledAttribute(): bool
    {
        return (bool) ($this->attributes['punch_block_enabled'] ?? true);
    }

    public function employees()
    {
        return $this->hasMany(EmployeeM::class, 'attendance_policy_rule_id');
    }
}
