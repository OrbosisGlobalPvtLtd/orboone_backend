<?php

namespace App\Models\HRMS\ProjectManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\Core\UserM as User;

class ProjectAssignmentM extends Model
{
    use HasFactory;

    protected $table = 'project_assignments';

    protected $fillable = [
        'project_id',
        'project_team_id',
        'employee_id',
        'project_role',
        'assigned_at',
        'relieved_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'relieved_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(ProjectM::class, 'project_id');
    }

    public function team()
    {
        return $this->belongsTo(ProjectTeamM::class, 'project_team_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
