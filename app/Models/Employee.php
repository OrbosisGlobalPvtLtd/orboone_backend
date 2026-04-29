<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // Accept all fields
    protected $guarded = [];

    // Auto-load important relations
    protected $with = ['employeeDetail', 'department', 'position', 'user'];

    /* ============================
       RELATIONSHIPS
       ============================ */

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

    /* ============================
       SCOPES / HELPERS
       ============================ */

    public function getIsPermanentAttribute()
    {
        if ($this->employment_type === 'Full-Time') {
            if ($this->probation_status === 'Permanent') {
                return true;
            }
            if ($this->probation_end_date && \Carbon\Carbon::now()->greaterThanOrEqualTo(\Carbon\Carbon::parse($this->probation_end_date))) {
                return true;
            }
        }
        return false;
    }

    public function getCount()
    {
        return self::count();
    }

    public function paginateEmployees($count = 10)
    {
        return self::with('headOfDepartment')->latest()->paginate($count);
    }

    public function getEndingContractEmployees($count = 10)
    {
        return self::orderBy('end_of_contract', 'ASC')->paginate($count);
    }

    public function getEmployeeLeaveData($count = 10)
    {
        return self::with('employeeLeave', 'employeeLeaveRequest')->latest()->paginate($count);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocumentModal::class, 'user_id', 'user_id');
    }

public function payrolls()
{
    return $this->hasMany(Payroll::class);
}

public function payslips()
{
    return $this->hasMany(Payslip::class);
}

 public function fnf()
{
    return $this->hasOne(FnF::class);
}

public function claims()
{
    return $this->hasMany(Claim::class);
}

public function statutory()
{
    return StatutorySetting::first();
}

 public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructure::class, 'salary_structure_id');
    }

public function fnfSettlement()
{
    return $this->hasOne(Fnf::class);
}

public function manager()
{
    return $this->belongsTo(Employee::class, 'manager_id');
}

public function subordinates()
{
    return $this->hasMany(Employee::class, 'manager_id');
}

public function assetAllocations()
{
    return $this->hasMany(AssetAllocation::class);
}

public function leaveAllocations()
{
    return $this->hasMany(LeaveAllocation::class);
}

public function leaveRequests()
{
    return $this->hasMany(LeaveRequest::class);
}

}
