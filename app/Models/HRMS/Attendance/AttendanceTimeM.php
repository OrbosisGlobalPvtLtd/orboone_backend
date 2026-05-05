<?php

namespace App\Models\HRMS\Attendance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceTimeM extends Model
{
    use HasFactory;

    protected $table = 'attendance_times';

    protected $fillable = [
        'name',
        'code',
        'punch_allowed_from',
        'shift_start_time',
        'late_after_time',
        'half_day_after_time',
        'shift_end_time',
        'required_work_minutes',
        'half_day_min_minutes',
        'lunch_break_minutes',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function attendances()
    {
        return $this->hasMany(AttendanceM::class, 'attendance_time_id');
    }
}