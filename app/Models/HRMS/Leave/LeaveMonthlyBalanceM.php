<?php

declare(strict_types=1);

namespace App\Models\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

final class LeaveMonthlyBalanceM extends Model
{
    protected $table = 'leave_monthly_balances';

    protected $guarded = [];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'is_locked' => 'boolean',
        'calculated_at' => 'datetime',
        'locked_at' => 'datetime',
        'pl_opening' => 'decimal:6',
        'pl_accrued' => 'decimal:6',
        'pl_used' => 'decimal:6',
        'pl_adjustment' => 'decimal:6',
        'pl_lapsed' => 'decimal:6',
        'pl_closing' => 'decimal:6',
        'sl_opening' => 'decimal:6',
        'sl_accrued' => 'decimal:6',
        'sl_used' => 'decimal:6',
        'sl_adjustment' => 'decimal:6',
        'sl_lapsed' => 'decimal:6',
        'sl_closing' => 'decimal:6',
        'comp_off_earned' => 'decimal:6',
        'comp_off_used' => 'decimal:6',
        'comp_off_lapsed' => 'decimal:6',
        'comp_off_closing' => 'decimal:6',
        'lwp_used' => 'decimal:6',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function allocation()
    {
        return $this->belongsTo(LeaveAllocationM::class, 'leave_allocation_id');
    }

    public function policy()
    {
        return $this->belongsTo(LeavePolicyM::class, 'policy_id');
    }
}
