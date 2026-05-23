<?php

namespace App\Models\HRMS\EnterprisePayroll;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

class EnterprisePayrollM extends Model
{
    protected $table = 'enterprise_payrolls';

    protected $guarded = [];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'total_working_days' => 'decimal:2',
        'present_days' => 'decimal:2',
        'paid_leave_days' => 'decimal:2',
        'sick_leave_days' => 'decimal:2',
        'comp_off_days' => 'decimal:2',
        'holiday_days' => 'decimal:2',
        'week_off_days' => 'decimal:2',
        'half_days' => 'decimal:2',
        'lwp_days' => 'decimal:2',
        'absent_days' => 'decimal:2',
        'payable_days' => 'decimal:2',
        'annual_ctc' => 'decimal:2',
        'monthly_ctc' => 'decimal:2',
        'per_day_salary' => 'decimal:4',
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'special_allowance' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'professional_tax' => 'decimal:2',
        'tds' => 'decimal:2',
        'attendance_deduction' => 'decimal:2',
        'lwp_deduction' => 'decimal:2',
        'half_day_deduction' => 'decimal:2',
        'absent_deduction' => 'decimal:2',
        'other_deduction' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'incentive_amount' => 'decimal:2',
        'reimbursement_amount' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'generated_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'locked_at' => 'datetime',
        'calculation_snapshot' => 'array',
    ];

    public function run()
    {
        return $this->belongsTo(EnterprisePayrollRunM::class, 'payroll_run_id');
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function items()
    {
        return $this->hasMany(EnterprisePayrollItemM::class, 'payroll_id');
    }

    public function payslip()
    {
        return $this->hasOne(EnterprisePayslipM::class, 'payroll_id');
    }
}
