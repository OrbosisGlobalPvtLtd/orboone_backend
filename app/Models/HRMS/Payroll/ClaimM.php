<?php

namespace App\Models\HRMS\Payroll;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use Illuminate\Database\Eloquent\Model;

class ClaimM extends Model
{
    protected $fillable = [
        'employee_id',
        'category',
        'amount',
        'reason',
        'file',
        'status', // pending, approved, rejected
        'payroll_month',
        'payroll_year',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'approval_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payroll_month' => 'integer',
        'payroll_year' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
