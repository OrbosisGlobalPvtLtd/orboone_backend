<?php

namespace App\Models\HRMS\Payroll;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Attendance\MonthlyAttendanceSummaryM as MonthlyAttendanceSummary;
use App\Models\HRMS\Payroll\PayslipM as Payslip;
use Illuminate\Database\Eloquent\Model;

class PayrollM extends Model
{
    protected $table = 'payrolls';
    protected $fillable = [
        'employee_id',
        'salary_structure_id',
        'monthly_attendance_summary_id',
        'month',
        'year',
        'working_days',
        'paid_days',
        'payable_days',
        'present_days',
        'paid_leave_days',
        'sick_leave_days',
        'comp_off_days',
        'holiday_days',
        'week_off_days',
        'half_days',
        'lwp_days',
        'absent_days',
        'basic',
        'hra',
        'allowance',
        'monthly_gross_salary',
        'daily_gross_rate',
        'attendance_loss_days',
        'lwp_deduction',
        'absent_deduction',
        'half_day_deduction',
        'bonus',
        'incentive',
        'reimbursements',
        'gross_salary',
        'tds',
        'other_deductions',
        'pt',
        'total_deductions',
        'net_salary',
        'calculation_snapshot',
        'generated_by',
        'generated_at',
        'approved_by',
        'approved_at',
        'locked_by',
        'locked_at',
        'status' // Draft, Processed, Locked
    ];

    protected $casts = [
        'calculation_snapshot' => 'array',
        'generated_at' => 'datetime',
        'approved_at' => 'datetime',
        'locked_at' => 'datetime',
        'working_days' => 'integer',
        'paid_days' => 'integer',
        'payable_days' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payslip()
    {
        return $this->hasOne(Payslip::class);
    }

    public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructureM::class, 'salary_structure_id');
    }

    public function attendanceSummary()
    {
        return $this->belongsTo(MonthlyAttendanceSummary::class, 'monthly_attendance_summary_id');
    }

    public function adjustments()
    {
        return $this->hasMany(PayrollAdjustmentM::class, 'payroll_id');
    }
}
