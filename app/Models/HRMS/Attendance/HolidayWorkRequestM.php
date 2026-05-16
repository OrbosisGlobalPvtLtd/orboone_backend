<?php

namespace App\Models\HRMS\Attendance;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Leave\CompOffM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HolidayWorkRequestM extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'holiday_work_requests';

    protected $guarded = [];

    protected $casts = [
        'worked_date' => 'date',
        'comp_off_generated' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function attendance()
    {
        return $this->belongsTo(AttendanceM::class, 'attendance_id');
    }

    public function compOff()
    {
        return $this->belongsTo(CompOffM::class, 'comp_off_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(UserM::class, 'approved_by_user_id');
    }
}
