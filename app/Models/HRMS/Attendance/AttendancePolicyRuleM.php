<?php

namespace App\Models\HRMS\Attendance;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendancePolicyRuleM extends Model
{
    use HasFactory;

    protected $table = 'attendance_policy_rules';

    protected $fillable = [
        'policy_name',
        'description',
        'is_active',
        'attendance_tracking_enabled',
        'punch_in_required',
        'punch_out_required',
        'allow_web_punch',
        'allow_mobile_punch',
        'mobile_only_punch',
        'web_punch_disabled',
        'punch_block_enabled',
        'half_day_enabled',
        'required_work_minutes',
        'half_day_min_minutes',
        'absent_below_minutes',
        'lunch_break_minutes',
        'auto_absent_enabled',
        'early_out_enabled',
        'early_out_limit',
        'early_violation_enabled',
        'early_violation_limit',
        'early_out_half_day_minutes',
        'early_out_grace_minutes',
        'late_mark_enabled',
        'late_mark_limit',
        'late_violation_enabled',
        'late_violation_limit',
        'grace_late_allowed',
        'late_lwp_after',
        'combined_violation_enabled',
        'combined_violation_limit',
        'combined_violation_action',
        'allowed_missed_punches',
        'missed_punch_after_minutes',
        'missed_punch_action',
        'missed_punch_lwp_after',
        'auto_lwp_for_missed_punch',
        'wfh_enabled',
        'monthly_wfh_limit',
        'manager_approval_required',
        'internet_proof_required',
        'speed_test_required',
        'internet_issue_lwp',
        'electricity_issue_lwp',
        'regularization_enabled',
        'regularization_days',
        'regularization_requires_approval',
    ];

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
        'early_out_grace_minutes' => 'integer',

        'half_day_enabled' => 'boolean',
        'required_work_minutes' => 'integer',
        'half_day_min_minutes' => 'integer',
        'absent_below_minutes' => 'integer',
        'lunch_break_minutes' => 'integer',

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
