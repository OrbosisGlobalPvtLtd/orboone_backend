<?php

namespace App\Models\HRMS\EnterprisePayroll;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

class EnterprisePayrollAdjustmentM extends Model
{
    protected $table = 'enterprise_payroll_adjustments';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
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
