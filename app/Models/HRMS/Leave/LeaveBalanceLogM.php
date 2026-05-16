<?php

namespace App\Models\HRMS\Leave;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalanceLogM extends Model
{
    use HasFactory;

    protected $table = 'leave_balance_logs';

    protected $guarded = [];

    protected $casts = [
        'credit' => 'decimal:2',
        'debit' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function allocation()
    {
        return $this->belongsTo(LeaveAllocationM::class, 'leave_allocation_id');
    }

    public function request()
    {
        return $this->belongsTo(LeaveRequestM::class, 'leave_request_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveTypeM::class, 'leave_type_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(UserM::class, 'created_by_user_id');
    }
}
