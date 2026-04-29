<?php

namespace App\Models\HRMS\Employee;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Core\UserM;
use App\Models\Core\RoleM;
use App\Models\Department;
use App\Models\Position; // legacy
use App\Models\Designation; // ✅ NEW
use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Models\EmployeeDocument;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveRequest;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\Claim;
use App\Models\SalaryStructure;
use App\Models\AssetAllocation;
use App\Models\LeaveAllocation;
use App\Models\LeaveRequest;
use App\Models\StatutorySetting;

class EmployeeM extends Model
{
    use HasFactory;

    protected $table = 'employees_new';

    protected $guarded = [];

    /**
     * ⚠️ DO NOT REMOVE position (backward compatibility)
     * NEW → designation added
     */
    protected $with = [
        'user',
        'department',
        'designation', // ✅ NEW STANDARD
        'position',    // ⚠️ LEGACY SUPPORT
        'systemRole',
        'reportingManager',
        'profile',
    ];

    protected $appends = ['is_permanent'];

    /* ============================
       RELATIONSHIPS
       ============================ */

    public function user()
    {
        return $this->belongsTo(UserM::class, 'user_id');
    }

    public function systemRole()
    {
        return $this->belongsTo(RoleM::class, 'system_role_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * ✅ NEW CLEAN RELATION (USE THIS)
     */
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    /**
     * ⚠️ OLD LEGACY RELATION (DO NOT REMOVE)
     * Existing code break na ho isliye rakha hai
     */
    public function position()
    {
        return $this->belongsTo(Position::class, 'designation_id');
    }

    public function reportingManager()
    {
        return $this->belongsTo(self::class, 'reporting_manager_employee_id');
    }

    public function subordinates()
    {
        return $this->hasMany(self::class, 'reporting_manager_employee_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(UserM::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(UserM::class, 'updated_by');
    }

    public function profile()
    {
        return $this->hasOne(EmployeeProfileM::class, 'employee_id');
    }

    public function employeeDetail()
    {
        return $this->hasOne(EmployeeProfileM::class, 'employee_id');
    }

    public function employeeLeave()
    {
        return $this->hasOne(EmployeeLeave::class, 'employee_id');
    }

    public function employeeLeaveRequest()
    {
        return $this->hasMany(EmployeeLeaveRequest::class, 'employee_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'employee_id');
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'employee_id');
    }

    public function fnf()
    {
        return $this->hasOne(FnF::class, 'employee_id');
    }

    public function fnfSettlement()
    {
        return $this->hasOne(FnF::class, 'employee_id');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'employee_id');
    }

    public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructure::class, 'salary_structure_id');
    }

    public function assetAllocations()
    {
        return $this->hasMany(AssetAllocation::class, 'employee_id');
    }

    public function leaveAllocations()
    {
        return $this->hasMany(LeaveAllocation::class, 'employee_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function statutory()
    {
        return StatutorySetting::query()->first();
    }

    /* ============================
       ACCESSORS / HELPERS
       ============================ */

    public function getIsPermanentAttribute()
    {
        if ($this->employment_type === 'full_time') {

            if ($this->probation_status === 'confirmed') {
                return true;
            }

            if (!empty($this->probation_end_date) &&
                Carbon::now()->greaterThanOrEqualTo(Carbon::parse($this->probation_end_date))) {
                return true;
            }
        }

        return false;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1)
            ->where('employment_status', 'active');
    }

    public function scopeInterns($query)
    {
        return $query->where('employment_type', 'intern');
    }

    public function scopeFullTime($query)
    {
        return $query->where('employment_type', 'full_time');
    }

    public function scopeContracts($query)
    {
        return $query->where('employment_type', 'contract');
    }

    public function getCount()
    {
        return self::count();
    }

    public function paginateEmployees($count = 10)
    {
        return self::with([
            'user',
            'department',
            'designation', 
            'position',    
            'systemRole',
            'reportingManager',
            'profile',
        ])->latest()->paginate($count);
    }

    public function getEndingContractEmployees($count = 10)
    {
        return self::where('employment_type', 'contract')
            ->whereNotNull('relieving_date')
            ->orderBy('relieving_date', 'asc')
            ->paginate($count);
    }

    public function getEmployeeLeaveData($count = 10)
    {
        return self::with([
            'employeeLeave',
            'employeeLeaveRequest',
            'profile',
        ])->latest()->paginate($count);
    }
}