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
        'leave_request_id',
        'comp_off_id',
        'attendance_date',
        'punch_in_time',
        'punch_out_time',
        'attendance_status',
        'attendance_source',
        'target_punch_out_time',
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
        'break_minutes',
        'lunch_break_minutes',
        'total_work_minutes',
        'is_late',
        'late_minutes',
        'is_early_out',
        'early_out_minutes',
        'violation_count',
        'is_half_day',
        'is_lwp',
        'lwp_reason',
        'missed_punch',
        'is_missed_punch',
        'missed_punch_reason',
        'is_punch_blocked',
        'is_blocked',
        'blocked_reason',
        'block_reason',
        'auto_blocked_at',
        'auto_block_reason',
        'is_admin_unlocked',
        'unlock_type',
        'unlock_reason_category',
        'unlock_remarks',
        'approved_punch_in_time',
        'is_late_exempted',
        'unlocked_by',
        'unlocked_at',
        'hr_approved_by',
        'hr_approved_at',
        'hr_approval_note',
        'old_pending_hr_logic',
        'pending_hr_reason',
        'remarks',
        'is_profile_completed_at_punch',
        'is_locked',
        'payroll_processed',
        'payroll_processed_at',
        'half_day_reason',
        'punch_in_note',
        'punch_out_note',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'punch_in_time' => 'datetime',
        'punch_out_time' => 'datetime',
        'hr_approved_at' => 'datetime',
        'is_late' => 'boolean',
        'is_early_out' => 'boolean',
        'is_half_day' => 'boolean',
        'is_lwp' => 'boolean',
        'missed_punch' => 'boolean',
        'is_missed_punch' => 'boolean',
        'is_punch_blocked' => 'boolean',
        'is_blocked' => 'boolean',
        'auto_blocked_at' => 'datetime',
        'is_admin_unlocked' => 'boolean',
        'is_late_exempted' => 'boolean',
        'unlocked_at' => 'datetime',
        'is_profile_completed_at_punch' => 'boolean',
        'is_locked' => 'boolean',
        'payroll_processed' => 'boolean',
        'payroll_processed_at' => 'datetime',
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

    public function leaveRequest()
    {
        return $this->belongsTo(\App\Models\HRMS\Leave\LeaveRequestM::class, 'leave_request_id');
    }

    public function compOff()
    {
        return $this->belongsTo(\App\Models\HRMS\Leave\CompOffM::class, 'comp_off_id');
    }

    public function workLogs()
    {
        return $this->hasMany(AttendanceWorkLogM::class, 'attendance_id');
    }

    public function hrApprovedBy()
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }

    public function unlockedBy()
    {
        return $this->belongsTo(User::class, 'unlocked_by');
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
