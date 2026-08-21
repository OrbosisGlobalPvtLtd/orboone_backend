<?php

namespace App\Models\HRMS\Reporting;

use App\Models\EmployeeM;
use App\Models\HRMS\ProjectManagement\ProjectM;
use App\Models\HRMS\ProjectManagement\ProjectTeamM;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportingAssignmentM extends Model
{
    use HasFactory;

    protected $table = 'reporting_assignments';

    protected $fillable = [
        'supervisor_employee_id',
        'employee_id',
        'project_id',
        'team_id',
        'start_date',
        'end_date',
        'status',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Supervisor employee relationship
     */
    public function supervisor()
    {
        return $this->belongsTo(EmployeeM::class, 'supervisor_employee_id');
    }

    /**
     * Supervised employee relationship
     */
    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    /**
     * Optional assigned project relationship
     */
    public function project()
    {
        return $this->belongsTo(ProjectM::class, 'project_id');
    }

    /**
     * Optional assigned project team relationship
     */
    public function team()
    {
        return $this->belongsTo(ProjectTeamM::class, 'team_id');
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
