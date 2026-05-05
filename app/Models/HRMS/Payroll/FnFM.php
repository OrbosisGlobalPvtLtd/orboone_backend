<?php

namespace App\Models\HRMS\Payroll;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use Illuminate\Database\Eloquent\Model;

class FnFM extends Model
{
    protected $table = 'fnf_settlements';

    protected $fillable = [
        'employee_id',
        'last_working_day',
        'pending_salary',
        'leave_encashment',
        'reimbursements',
        'deductions',
        'net_payable',
        'status',
    ];

    protected $casts = [
        'last_working_day' => 'date',
        'pending_salary'   => 'decimal:2',
        'leave_encashment' => 'decimal:2',
        'reimbursements'   => 'decimal:2',
        'deductions'       => 'decimal:2',
        'net_payable'      => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
