<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
protected $fillable = [
        'name',
        'basic_salary',
        'hra_percent',
        'allowance',
        'pt_amount',
        'effective_date',
        'components'
    ];

    protected $casts = [
        'components' => 'array'
    ];

      public function employees()
    {
        return $this->hasMany(Employee::class, 'salary_structure_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
