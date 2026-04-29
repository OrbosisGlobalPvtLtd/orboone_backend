<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeave extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'employee_id',
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'leaves_quota',
        'used_leaves',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

        public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function headOfDepartment()
    {
        return $this->belongsTo(Department::class, 'head_of');
    }

    public function employeeDetail()
    {
        return $this->hasOne(EmployeeDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employeeLeave()
    {
        return $this->hasOne(EmployeeLeave::class, 'employee_id');
    }

    public function employeeLeaveRequest()
    {
        return $this->hasMany(EmployeeLeaveRequest::class, 'employee_id');
    }
}
