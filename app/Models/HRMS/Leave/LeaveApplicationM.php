<?php

namespace App\Models\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\Core\UserM as User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplicationM extends Model
{
    use HasFactory;
    protected $table = 'leave_applications';
    protected $fillable = [
        'employee_id', 
        'leave_type', 
        'start_date', 
        'end_date', 
        'total_days', 
        'lwp_days',
        'reason',
        'attachment',
        'status', 
        'approved_by', 
        'admin_remark'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
