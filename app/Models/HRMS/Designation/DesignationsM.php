<?php

namespace App\Models\HRMS\Designation;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HRMS\Department\DepartmentM;
use App\Models\HRMS\Employee\EmployeeM;

class DesignationM extends Model
{
    use HasFactory;

    protected $table = 'designations';

    protected $fillable = [
        'department_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(DepartmentM::class, 'department_id');
    }

    public function employees()
    {
        return $this->hasMany(EmployeeM::class, 'designation_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}