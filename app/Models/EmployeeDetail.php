<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function documents()
{
    return $this->hasMany(EmployeeDocument::class);
}

public function payrolls()
{
    return $this->hasMany(Payroll::class);
}

public function payslips()
{
    return $this->hasMany(Payslip::class);
}

}
