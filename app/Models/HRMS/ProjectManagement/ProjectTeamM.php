<?php

namespace App\Models\HRMS\ProjectManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\Core\UserM as User;

class ProjectTeamM extends Model
{
    use HasFactory;

    protected $table = 'project_teams';

    protected $fillable = [
        'project_id',
        'team_name',
        'team_lead_employee_id',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(ProjectM::class, 'project_id');
    }

    public function teamLead()
    {
        return $this->belongsTo(Employee::class, 'team_lead_employee_id');
    }

    public function assignments()
    {
        return $this->hasMany(ProjectAssignmentM::class, 'project_team_id');
    }

    public function activeAssignments()
    {
        return $this->hasMany(ProjectAssignmentM::class, 'project_team_id')->where('is_active', 1);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTaskM::class, 'project_team_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
