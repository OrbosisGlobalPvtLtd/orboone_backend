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
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
