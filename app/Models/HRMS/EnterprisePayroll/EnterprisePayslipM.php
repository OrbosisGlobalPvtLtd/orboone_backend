<?php

namespace App\Models\HRMS\EnterprisePayroll;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

class EnterprisePayslipM extends Model
{
    protected $table = 'enterprise_payslips';

    protected $guarded = [];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'generated_at' => 'datetime',
        'is_visible_to_employee' => 'boolean',
    ];

    public function payroll()
    {
        return $this->belongsTo(EnterprisePayrollM::class, 'payroll_id');
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }
}
