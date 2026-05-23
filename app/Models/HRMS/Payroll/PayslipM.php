<?php

namespace App\Models\HRMS\Payroll;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Payroll\PayrollM as Payroll;
use Illuminate\Database\Eloquent\Model;

class PayslipM extends Model
{
    protected $table = 'payslips';
    protected $fillable = [
        'employee_id',
        'payroll_id',
        'month',
        'year',
        'file_path', // pdf location
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'generated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
