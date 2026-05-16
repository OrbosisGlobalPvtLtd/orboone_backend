<?php

namespace App\Models\HRMS\Policy;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePolicyAssignmentM extends Model
{
    use HasFactory;

    protected $table = 'employee_policy_assignments';

    protected $guarded = [];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(UserM::class, 'assigned_by_user_id');
    }
}
