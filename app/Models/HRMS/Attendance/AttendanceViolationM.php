<?php

namespace App\Models\HRMS\Attendance;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceViolationM extends Model
{
    use HasFactory;

    protected $table = 'attendance_violations';

    protected $guarded = [];

    protected $casts = [
        'violation_date' => 'date',
        'consumed_at' => 'datetime',
        'minutes' => 'integer',
        'converted_to_half_day' => 'boolean',
        'converted_to_lwp' => 'boolean',
        'is_consumed' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function attendance()
    {
        return $this->belongsTo(AttendanceM::class, 'attendance_id');
    }

    public function penaltyAttendance()
    {
        return $this->belongsTo(AttendanceM::class, 'penalty_attendance_id');
    }
}
