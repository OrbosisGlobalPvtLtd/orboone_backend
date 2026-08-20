<?php

namespace App\Models\HRMS\ProjectManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\Core\UserM as User;

class ProjectM extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'project_code',
        'name',
        'client_name',
        'delivery_head_employee_id',
        'delivery_head_name',
        'start_date',
        'end_date',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function deliveryHead()
    {
        return $this->belongsTo(Employee::class, 'delivery_head_employee_id');
    }

    public function getDeliveryHeadDisplayNameAttribute(): string
    {
        if ($this->deliveryHead && $this->deliveryHead->user) {
            return $this->deliveryHead->display_name;
        }
        return !empty($this->delivery_head_name) ? $this->delivery_head_name : 'Unassigned';
    }

    public function teams()
    {
        return $this->hasMany(ProjectTeamM::class, 'project_id');
    }

    public function activeTeams()
    {
        return $this->hasMany(ProjectTeamM::class, 'project_id')->where('is_active', 1);
    }

    public function assignments()
    {
        return $this->hasMany(ProjectAssignmentM::class, 'project_id');
    }

    public function activeAssignments()
    {
        return $this->hasMany(ProjectAssignmentM::class, 'project_id')->where('is_active', 1);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTaskM::class, 'project_id');
    }

    public function workReportTemplates()
    {
        return $this->hasMany(WorkReportTemplateM::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getProgressPercentageAttribute(): int
    {
        $activeTasks = $this->tasks()->whereNotIn('status', ['cancelled'])->get();
        if ($activeTasks->isEmpty()) {
            return 0;
        }

        $totalProgress = $activeTasks->sum('progress_percentage');
        return (int) round($totalProgress / $activeTasks->count());
    }
}
