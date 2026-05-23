<?php

namespace App\Models\HRMS\EnterprisePayroll;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

class EnterpriseSalaryStructureHistoryM extends Model
{
    protected $table = 'enterprise_salary_structure_histories';

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function salaryStructure()
    {
        return $this->belongsTo(EnterpriseSalaryStructureM::class, 'salary_structure_id');
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }
}
