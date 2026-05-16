<?php

namespace App\Models\HRMS\Employee;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\UserM;

class EmployeeExitProcessM extends Model
{
    protected $table = 'employee_exit_processes';

    protected $fillable = [
        'employee_id',
        'exit_type',
        'resignation_date',
        'last_working_date',
        'reason',
        'asset_handover_status',
        'fnf_status',
        'experience_letter_status',
        'relieving_letter_status',
        'final_status',
        'initiated_by_user_id',
        'completed_by_user_id',
        'completed_at',
    ];

    protected $casts = [
        'resignation_date' => 'date',
        'last_working_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function initiatedBy()
    {
        return $this->belongsTo(UserM::class, 'initiated_by_user_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(UserM::class, 'completed_by_user_id');
    }
}