<?php

namespace App\Models\HRMS\Employee;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Core\UserM;
use App\Models\Core\RoleM;
use App\Models\HRMS\Employee\PositionM;
use App\Models\HRMS\Department\DepartmentM; // ✅ NEW
use App\Models\HRMS\Designation\DesignationM;

use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Models\HRMS\Employee\EmployeeSalaryHistoryM;
use App\Models\HRMS\Leave\EmployeeLeaveM as EmployeeLeave;
use App\Models\HRMS\Leave\EmployeeLeaveRequestM as EmployeeLeaveRequest;
use App\Models\HRMS\Payroll\PayrollM as Payroll;
use App\Models\HRMS\Payroll\PayslipM as Payslip;
use App\Models\HRMS\Payroll\ClaimM as Claim;
use App\Models\HRMS\Payroll\FnFM as FnF;
use App\Models\HRMS\Payroll\PayrollAdjustmentM as PayrollAdjustment;
use App\Models\HRMS\Payroll\SalaryStructureM as SalaryStructure;
use App\Models\HRMS\Employee\AssetAllocationM as AssetAllocation;
use App\Models\HRMS\Leave\LeaveAllocationM as LeaveAllocation;
use App\Models\HRMS\Leave\LeaveRequestM as LeaveRequest;
use App\Models\HRMS\Payroll\StatutorySettingM as StatutorySetting;
use App\Models\HRMS\Document\EmployeeDocumentM;

class EmployeeM extends Model
{
    use HasFactory;

    protected $table = 'employees_new';

    protected $guarded = [];


    protected $with = [
        'user',
        'department',
        'designation', // ✅ NEW STANDARD
        'position',
        'systemRole',
        'reportingManager',
        'profile',
    ];

    protected $appends = ['is_permanent', 'display_name'];

    protected static function newFactory()
    {
        return \Database\Factories\EmployeeFactory::new();
    }

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
        return $this->belongsTo(DepartmentM::class, 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo(DesignationM::class, 'designation_id');
    }

    public function position()
    {
        return $this->belongsTo(PositionM::class, 'designation_id');
    }



    public function getDisplayNameAttribute()
    {
        return $this->user_name
            ?? optional($this->user)->name
            ?? $this->employee_name
            ?? $this->full_name
            ?? $this->employee_code
            ?? 'N/A';
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

    public function salaryHistories()
    {
        return $this->hasMany(EmployeeSalaryHistoryM::class, 'employee_id')
            ->orderByDesc('effective_from')
            ->orderByDesc('id');
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
        $relation = $this->hasMany(EmployeeDocumentM::class, 'employee_id');
        if (\Illuminate\Support\Facades\Schema::hasColumn('employee_documents_new', 'is_active')) {
            $relation->where('is_active', 1);
        }
        return $relation;
    }

    public function allDocuments()
    {
        return $this->hasMany(EmployeeDocumentM::class, 'employee_id');
    }

    public function archivedDocuments()
    {
        return $this->hasMany(EmployeeDocumentM::class, 'employee_id')->where('is_active', 0);
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

    public function payrollAdjustments()
    {
        return $this->hasMany(PayrollAdjustment::class, 'employee_id');
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
        if ($this->employee_stage === 'permanent') {
            return true;
        }

        if (in_array($this->probation_status, ['completed', 'confirmed'], true)) {
            return true;
        }

        if (
            $this->employee_stage === 'probation'
            && ! empty($this->probation_end_date)
            && Carbon::now()->greaterThanOrEqualTo(Carbon::parse($this->probation_end_date))
        ) {
            return true;
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
        return $query->where(function ($q) {
            $q->where('employee_stage', 'internship')
                ->orWhere(function ($legacy) {
                    $legacy->whereNull('employee_stage')
                        ->where('employment_type', 'intern');
                });
        });
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
