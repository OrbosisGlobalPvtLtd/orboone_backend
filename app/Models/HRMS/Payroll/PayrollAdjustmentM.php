<?php

namespace App\Models\HRMS\Payroll;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use Illuminate\Database\Eloquent\Model;

class PayrollAdjustmentM extends Model
{
    protected $table = 'payroll_adjustments';

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'type',
        'amount',
        'title',
        'remarks',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'payroll_id',
        'processed_at',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function payroll()
    {
        return $this->belongsTo(PayrollM::class, 'payroll_id');
    }
}
