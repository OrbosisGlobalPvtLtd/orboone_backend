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
        'termination_date',
        'exit_initiated_date',
        'last_working_day',
        'last_working_date',
        'notice_period_days',
        'notice_waived',
        'immediate_exit',
        'buyout_recovery',
        'fnf_due_date',
        'reason',
        'status',
        'asset_status',
        'asset_handover_status',
        'fnf_status',
        'document_status',
        'handover_status',
        'experience_letter_status',
        'relieving_letter_status',
        'final_status',
        'initiated_by_user_id',
        'approved_by_user_id',
        'completed_by_user_id',
        'completed_at',
        'remarks',
    ];

    protected $casts = [
        'resignation_date' => 'date',
        'termination_date' => 'date',
        'exit_initiated_date' => 'date',
        'last_working_day' => 'date',
        'last_working_date' => 'date',
        'fnf_due_date' => 'date',
        'notice_waived' => 'boolean',
        'immediate_exit' => 'boolean',
        'buyout_recovery' => 'boolean',
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
