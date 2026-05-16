<?php

namespace App\Models\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveAllocationM extends Model
{
    use HasFactory;

    protected $table = 'leave_allocations';

    protected $guarded = [];

    protected $casts = [
        'year' => 'integer',
        'confirmation_date' => 'date',
        'allocation_from_date' => 'date',
        'allocation_to_date' => 'date',
        'total_allocated' => 'decimal:2',
        'paid_allocated' => 'decimal:2',
        'sick_allocated' => 'decimal:2',
        'comp_off_allocated' => 'decimal:2',
        'total_used' => 'decimal:2',
        'paid_used' => 'decimal:2',
        'sick_used' => 'decimal:2',
        'comp_off_used' => 'decimal:2',
        'lwp_used' => 'decimal:2',
        'total_remaining' => 'decimal:2',
        'paid_remaining' => 'decimal:2',
        'sick_remaining' => 'decimal:2',
        'comp_off_remaining' => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function policy()
    {
        return $this->belongsTo(LeavePolicyM::class, 'policy_id');
    }

    public function logs()
    {
        return $this->hasMany(LeaveBalanceLogM::class, 'leave_allocation_id');
    }
}
