<?php

namespace App\Models\HRMS\Department;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HRMS\Designation\DesignationM;
use App\Models\HRMS\Employee\EmployeeM;

class DepartmentM extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'name',
        'code',
        'address',
    ];

    public function designations()
    {
        return $this->hasMany(DesignationM::class, 'department_id');
    }

    public function activeDesignations()
    {
        return $this->hasMany(DesignationM::class, 'department_id')
            ->where('is_active', 1);
    }

    public function employees()
    {
        return $this->hasMany(EmployeeM::class, 'department_id');
    }
}