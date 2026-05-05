<?php

namespace App\Models\HRMS\Employee;

use App\Models\Core\UserM;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalaryHistoryM extends Model
{
    protected $table = 'employee_salary_histories';

    protected $guarded = [];

    protected $casts = [
        'salary_amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(UserM::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(UserM::class, 'updated_by');
    }
}
