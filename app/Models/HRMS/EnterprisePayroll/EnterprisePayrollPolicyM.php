<?php

namespace App\Models\HRMS\EnterprisePayroll;

use Illuminate\Database\Eloquent\Model;

class EnterprisePayrollPolicyM extends Model
{
    protected $table = 'enterprise_payroll_policies';

    protected $guarded = [];

    protected $casts = [
        'professional_tax_enabled' => 'boolean',
        'pf_enabled' => 'boolean',
        'esi_enabled' => 'boolean',
        'tds_enabled' => 'boolean',
        'allow_negative_salary' => 'boolean',
        'payroll_lock_after_generation' => 'boolean',
        'include_weekoff_in_payable' => 'boolean',
        'include_holiday_in_payable' => 'boolean',
        'is_active' => 'boolean',
        'professional_tax_amount' => 'decimal:2',
        'pf_percentage' => 'decimal:2',
        'esi_percentage' => 'decimal:2',
        'tds_percentage' => 'decimal:2',
        'half_day_payable_ratio' => 'decimal:2',
        'absent_payable_ratio' => 'decimal:2',
        'lwp_payable_ratio' => 'decimal:2',
        'paid_leave_payable_ratio' => 'decimal:2',
        'weekoff_payable_ratio' => 'decimal:2',
        'holiday_payable_ratio' => 'decimal:2',
    ];
}

