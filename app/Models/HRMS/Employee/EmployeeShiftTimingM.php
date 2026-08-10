<?php

namespace App\Models\HRMS\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShiftTimingM extends Model
{
    use HasFactory;

    protected $table = 'employee_shift_timings';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function attendanceTime()
    {
        return $this->belongsTo(\App\Models\HRMS\Attendance\AttendanceTimeM::class, 'attendance_time_id');
    }

    public function attendancePolicyRule()
    {
        return $this->belongsTo(\App\Models\HRMS\Attendance\AttendancePolicyRuleM::class, 'attendance_policy_rule_id');
    }
}
