<?php

namespace App\Models\HRMS\EnterprisePayroll;

use Illuminate\Database\Eloquent\Model;

class EnterprisePayrollRunM extends Model
{
    protected $table = 'enterprise_payroll_runs';

    protected $guarded = [];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'total_employees' => 'integer',
        'total_gross' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
        'processed_at' => 'datetime',
        'approved_at' => 'datetime',
        'locked_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    public function payrolls()
    {
        return $this->hasMany(EnterprisePayrollM::class, 'payroll_run_id');
    }

    public function audits()
    {
        return $this->hasMany(EnterprisePayrollAuditM::class, 'payroll_run_id');
    }
}
