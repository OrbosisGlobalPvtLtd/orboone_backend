<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'working_days',
        'paid_days',
        'basic',
        'hra',
        'allowance',
        'gross_salary',
        'pt',
        'total_deductions',
        'net_salary',
        'status' // Draft, Processed, Locked
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payslip()
    {
        return $this->hasOne(Payslip::class);
    }
}
