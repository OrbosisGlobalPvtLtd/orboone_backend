<?php

namespace App\Models\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Department\DepartmentM as Department;
use App\Models\HRMS\Employee\EmployeeProfileM as EmployeeDetail;
use App\Models\Core\UserM as User;
use App\Models\HRMS\Employee\PositionM as Position;
use App\Models\HRMS\Leave\LeaveTypeM as LeaveType;
use App\Models\HRMS\Leave\EmployeeLeaveM as EmployeeLeave;
use App\Models\HRMS\Leave\EmployeeLeaveRequestM as EmployeeLeaveRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveM extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return \Database\Factories\EmployeeLeaveFactory::new();
    }

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
