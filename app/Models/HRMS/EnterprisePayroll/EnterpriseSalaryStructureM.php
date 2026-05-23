<?php

namespace App\Models\HRMS\EnterprisePayroll;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

class EnterpriseSalaryStructureM extends Model
{
    protected $table = 'enterprise_salary_structures';

    protected $guarded = [];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'annual_ctc' => 'decimal:2',
        'monthly_ctc' => 'decimal:2',
        'basic_annual' => 'decimal:2',
        'basic_monthly' => 'decimal:2',
        'hra_annual' => 'decimal:2',
        'hra_monthly' => 'decimal:2',
        'special_allowance_annual' => 'decimal:2',
        'special_allowance_monthly' => 'decimal:2',
        'professional_tax_monthly' => 'decimal:2',
        'tds_annual' => 'decimal:2',
        'tds_monthly' => 'decimal:2',
        'other_deduction_monthly' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function histories()
    {
        return $this->hasMany(EnterpriseSalaryStructureHistoryM::class, 'salary_structure_id');
    }
}
