<?php

namespace App\Models\HRMS\Payroll;

use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\LeaveRequestM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollAttendanceImpactM extends Model
{
    use HasFactory;

    protected $table = 'payroll_attendance_impacts';

    protected $guarded = [];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'impact_days' => 'decimal:2',
        'impact_amount' => 'decimal:2',
        'is_processed_in_payroll' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function attendance()
    {
        return $this->belongsTo(AttendanceM::class, 'attendance_id');
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequestM::class, 'leave_request_id');
    }
}
