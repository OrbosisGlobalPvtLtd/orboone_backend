<?php

namespace App\Models\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\Core\UserM as User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestM extends Model
{
    use HasFactory;

    protected $table = 'leave_requests';

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'requested_days' => 'decimal:2',
        'deducted_days' => 'decimal:2',
        'is_half_day' => 'boolean',
        'approved_at' => 'datetime',
        'manager_approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'sandwich_applied' => 'boolean',
        'paid_days' => 'decimal:2',
        'sick_days' => 'decimal:2',
        'comp_off_days' => 'decimal:2',
        'lwp_days' => 'decimal:2',
        'auto_converted_to_lwp' => 'boolean',
        'payroll_processed' => 'boolean',
        'attendance_synced' => 'boolean',
        'emergency_leave' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveTypeM::class, 'leave_type_id');
    }

    public function dates()
    {
        return $this->hasMany(LeaveRequestDateM::class, 'leave_request_id')->orderBy('leave_date');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function managerApprover()
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    public function hrApprover()
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function reportingManager()
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_employee_id');
    }

    public function balanceLogs()
    {
        return $this->hasMany(LeaveBalanceLogM::class, 'leave_request_id');
    }
}
