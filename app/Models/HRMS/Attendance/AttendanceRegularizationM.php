<?php

namespace App\Models\HRMS\Attendance;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRegularizationM extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attendance_regularizations';

    protected $guarded = [];

    protected $casts = [
        'existing_punch_in' => 'datetime',
        'existing_punch_out' => 'datetime',
        'requested_punch_in' => 'datetime',
        'requested_punch_out' => 'datetime',
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

    public function approvedBy()
    {
        return $this->belongsTo(UserM::class, 'approved_by_user_id');
    }
}
