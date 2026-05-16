<?php

namespace App\Models\HRMS\Attendance;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceDailyStatusLogM extends Model
{
    use HasFactory;

    protected $table = 'attendance_daily_status_logs';

    protected $guarded = [];

    protected $casts = [
        'status_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function attendance()
    {
        return $this->belongsTo(AttendanceM::class, 'attendance_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(UserM::class, 'created_by_user_id');
    }
}
