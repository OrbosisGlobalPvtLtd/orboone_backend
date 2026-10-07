<?php

namespace App\Models\HRMS\Attendance;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

class WfhRequestM extends Model
{
    protected $table = 'wfh_requests';

    protected $guarded = [];

    protected $casts = [
        'request_date' => 'date:Y-m-d',
        'from_date' => 'date:Y-m-d',
        'to_date' => 'date:Y-m-d',
        'counts_in_monthly_quota' => 'boolean',
        'manager_approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    protected $appends = [
        'manager_approved_by_name',
        'hr_approved_by_name',
        'rejected_by_name',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function managerApprover()
    {
        return $this->belongsTo(UserM::class, 'manager_approved_by');
    }

    public function hrApprover()
    {
        return $this->belongsTo(UserM::class, 'hr_approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(UserM::class, 'rejected_by');
    }

    public function getManagerApprovedByNameAttribute(): ?string
    {
        if ($this->relationLoaded('managerApprover') && $this->managerApprover) {
            return $this->managerApprover->name;
        }
        if ($this->manager_approved_by) {
            $user = UserM::find($this->manager_approved_by);
            return $user ? $user->name : null;
        }
        return null;
    }

    public function getHrApprovedByNameAttribute(): ?string
    {
        if ($this->relationLoaded('hrApprover') && $this->hrApprover) {
            return $this->hrApprover->name;
        }
        if ($this->hr_approved_by) {
            $user = UserM::find($this->hr_approved_by);
            return $user ? $user->name : null;
        }
        return null;
    }

    public function getRejectedByNameAttribute(): ?string
    {
        if ($this->relationLoaded('rejector') && $this->rejector) {
            return $this->rejector->name;
        }
        if ($this->rejected_by) {
            $user = UserM::find($this->rejected_by);
            return $user ? $user->name : null;
        }
        return null;
    }
}
