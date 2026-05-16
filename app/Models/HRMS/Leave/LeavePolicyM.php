<?php

namespace App\Models\HRMS\Leave;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeavePolicyM extends Model
{
    use HasFactory;

    protected $table = 'leave_policies';

    protected $guarded = [];

    protected $casts = [
        'annual_total_leaves' => 'decimal:2',
        'annual_paid_leaves' => 'decimal:2',
        'annual_sick_leaves' => 'decimal:2',
        'monthly_leave_limit' => 'decimal:2',
        'allow_monthly_balance_accumulation' => 'boolean',
        'max_leave_at_once' => 'decimal:2',
        'carry_forward_enabled' => 'boolean',
        'leave_lapse_month' => 'integer',
        'leave_lapse_day' => 'integer',
        'sandwich_enabled' => 'boolean',
        'weekoff_included_in_sandwich' => 'boolean',
        'holiday_included_in_sandwich' => 'boolean',
        'nov_dec_half_usage_enabled' => 'boolean',
        'nov_dec_threshold_balance' => 'decimal:2',
        'nov_dec_usage_percentage' => 'decimal:2',
        'probation_leave_limit' => 'decimal:2',
        'internship_leave_limit' => 'decimal:2',
        'medical_certificate_after_days' => 'integer',
        'comp_off_expiry_same_month' => 'boolean',
        'allow_negative_balance' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function employees()
    {
        return $this->hasMany(EmployeeM::class, 'leave_policy_id');
    }

    public function allocations()
    {
        return $this->hasMany(LeaveAllocationM::class, 'policy_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(UserM::class, 'created_by_user_id');
    }
}
