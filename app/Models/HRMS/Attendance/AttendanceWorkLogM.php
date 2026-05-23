<?php

namespace App\Models\HRMS\Attendance;

use App\Models\Core\UserM as User;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceWorkLogM extends Model
{
    use HasFactory;

    protected $table = 'attendance_work_logs';

    protected $fillable = [
        'attendance_id',
        'employee_id',
        'user_id',
        'work_date',
        'task_type',
        'duration_minutes',
        'work_summary',
        'work_summary_json',
        'latitude',
        'longitude',
        'device_info',
        'ip_address',
        'remarks',
        'project_id',
        'project_task_id',
    ];

    protected $casts = [
        'work_date' => 'date',
        'duration_minutes' => 'integer',
        'work_summary_json' => 'array',
    ];

    public function attendance()
    {
        return $this->belongsTo(AttendanceM::class, 'attendance_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
