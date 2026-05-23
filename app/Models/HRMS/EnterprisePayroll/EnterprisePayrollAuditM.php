<?php

namespace App\Models\HRMS\EnterprisePayroll;

use Illuminate\Database\Eloquent\Model;

class EnterprisePayrollAuditM extends Model
{
    protected $table = 'enterprise_payroll_audits';

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}
