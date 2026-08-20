<?php

namespace App\Models\HRMS\ProjectManagement;

use Illuminate\Database\Eloquent\Model;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\User;

class TechnicalLeadAssignmentM extends Model
{
    protected $table = 'technical_lead_assignments';

    protected $fillable = [
        'technical_lead_employee_id',
        'employee_id',
        'assigned_at',
        'relieved_at',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'relieved_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function technicalLead()
    {
        return $this->belongsTo(Employee::class, 'technical_lead_employee_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
