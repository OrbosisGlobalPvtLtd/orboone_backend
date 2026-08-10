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
        'resolved_at' => 'datetime',
        'minutes' => 'integer',
        'violation_count' => 'integer',
        'cycle_month' => 'string',
        'status' => 'string',
        'converted_to_half_day' => 'boolean',
        'converted_to_lwp' => 'boolean',
        'is_consumed' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($violation) {
            if (empty($violation->cycle_month) && ! empty($violation->violation_date)) {
                $violation->cycle_month = \Carbon\Carbon::parse($violation->violation_date)->format('Y-m');
            }
            if (empty($violation->violation_count)) {
                $violation->violation_count = 1;
            }
            if (empty($violation->status)) {
                $violation->status = 'pending';
            }
        });
    }

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
