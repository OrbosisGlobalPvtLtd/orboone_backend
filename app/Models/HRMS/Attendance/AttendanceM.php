<?php

namespace App\Models\HRMS\Attendance;

use App\Models\Core\UserM as User;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceM extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'employee_id',
        'attendance_time_id',
        'attendance_type_id',
        'attendance_date',
        'punch_in_time',
        'punch_out_time',
        'work_mode',
        'punch_in_latitude',
        'punch_in_longitude',
        'punch_in_address',
        'punch_out_latitude',
        'punch_out_longitude',
        'punch_out_address',
        'punch_in_ip',
        'punch_out_ip',
        'punch_in_device',
        'punch_out_device',
        'gross_work_minutes',
        'lunch_break_minutes',
        'total_work_minutes',
        'is_late',
        'late_minutes',
        'is_early_out',
        'early_out_minutes',
        'is_blocked',
        'block_reason',
        'hr_approved_by',
        'hr_approved_at',
        'hr_approval_note',
        'is_profile_completed_at_punch',
        'is_locked',
        'punch_in_note',
        'punch_out_note',
    ];

    protected $casts = [
        // 'attendance_date' => 'date',
        'attendance_date' => 'string',
        'hr_approved_at' => 'datetime',
        'is_late' => 'boolean',
        'is_early_out' => 'boolean',
        'is_blocked' => 'boolean',
        'is_profile_completed_at_punch' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendanceTime()
    {
        return $this->belongsTo(AttendanceTimeM::class, 'attendance_time_id');
    }

    public function attendanceType()
    {
        return $this->belongsTo(AttendanceTypeM::class, 'attendance_type_id');
    }

    public function workLogs()
    {
        return $this->hasMany(AttendanceWorkLogM::class, 'attendance_id');
    }

    public function hrApprovedBy()
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }

    public function getDurationAttribute()
    {
        if ($this->punch_in_time && $this->punch_out_time) {
            $in = Carbon::parse($this->punch_in_time);
            $out = Carbon::parse($this->punch_out_time);

            if ($out->lt($in)) {
                $out->addDay();
            }

            $diff = $in->diff($out);

            return $diff->format('%h hours %i mins');
        }

        return 'N/A';
    }

    public function getNetDurationAttribute()
    {
        if ($this->total_work_minutes > 0) {
            $hours = floor($this->total_work_minutes / 60);
            $minutes = $this->total_work_minutes % 60;

            return "{$hours} hours {$minutes} mins";
        }

        return 'N/A';
    }

    public function getGrossDurationAttribute()
    {
        if ($this->gross_work_minutes > 0) {
            $hours = floor($this->gross_work_minutes / 60);
            $minutes = $this->gross_work_minutes % 60;

            return "{$hours} hours {$minutes} mins";
        }

        return 'N/A';
    }

    public function getStatusNameAttribute()
    {
        return optional($this->attendanceType)->name ?? 'N/A';
    }

    public function getStatusCodeAttribute()
    {
        return optional($this->attendanceType)->code ?? null;
    }

    public function getWorkModeLabelAttribute()
    {
        return match ($this->work_mode) {
            'wfo' => 'Work From Office',
            'wfh' => 'Work From Home',
            default => 'N/A',
        };
    }
}