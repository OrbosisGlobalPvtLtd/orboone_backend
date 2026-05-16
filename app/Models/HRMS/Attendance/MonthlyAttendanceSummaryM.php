<?php

namespace App\Models\HRMS\Attendance;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyAttendanceSummaryM extends Model
{
    use HasFactory;

    protected $table = 'monthly_attendance_summaries';

    protected $guarded = [];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'present_days' => 'decimal:2',
        'paid_leave_days' => 'decimal:2',
        'sick_leave_days' => 'decimal:2',
        'comp_off_days' => 'decimal:2',
        'holiday_days' => 'decimal:2',
        'week_off_days' => 'decimal:2',
        'half_days' => 'decimal:2',
        'lwp_days' => 'decimal:2',
        'absent_days' => 'decimal:2',
        'late_count' => 'integer',
        'early_out_count' => 'integer',
        'missed_punch_count' => 'integer',
        'total_work_minutes' => 'integer',
        'payable_days' => 'decimal:2',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function lockedBy()
    {
        return $this->belongsTo(UserM::class, 'locked_by_user_id');
    }
}
