<?php

namespace App\Models\HRMS\Payroll;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Payroll\PayslipM as Payslip;
use Illuminate\Database\Eloquent\Model;

class PayrollM extends Model
{
    protected $table = 'payrolls';
    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'working_days',
        'paid_days',
        'basic',
        'hra',
        'allowance',
        'gross_salary',
        'pt',
        'total_deductions',
        'net_salary',
        'status' // Draft, Processed, Locked
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payslip()
    {
        return $this->hasOne(Payslip::class);
    }
}
