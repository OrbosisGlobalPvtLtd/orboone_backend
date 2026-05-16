<?php

namespace App\Models\HRMS\Leave;

use App\Models\Core\UserM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompOffM extends Model
{
    use HasFactory;

    protected $table = 'comp_offs';

    protected $guarded = [];

    protected $casts = [
        'worked_date' => 'date',
        'earned_days' => 'decimal:2',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function usedAgainstLeaveRequest()
    {
        return $this->belongsTo(LeaveRequestM::class, 'used_against_leave_request_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(UserM::class, 'approved_by_user_id');
    }
}
