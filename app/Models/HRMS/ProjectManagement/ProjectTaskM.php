<?php

namespace App\Models\HRMS\ProjectManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\Core\UserM as User;

class ProjectTaskM extends Model
{
    use HasFactory;

    protected $table = 'project_tasks';

    protected $fillable = [
        'project_id',
        'project_team_id',
        'assigned_employee_id',
        'title',
        'description',
        'task_type',
        'priority',
        'status',
        'progress_percentage',
        'start_date',
        'due_date',
        'completed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'progress_percentage' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(ProjectM::class, 'project_id');
    }

    public function team()
    {
        return $this->belongsTo(ProjectTeamM::class, 'project_team_id');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
