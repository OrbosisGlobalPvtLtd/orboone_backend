<?php

namespace App\Models\HRMS\Attendance;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

class WfhRequestM extends Model
{
    protected $table = 'wfh_requests';

    protected $guarded = [];

    protected $casts = [
        'request_date' => 'date',
        'counts_in_monthly_quota' => 'boolean',
        'manager_approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }
}
