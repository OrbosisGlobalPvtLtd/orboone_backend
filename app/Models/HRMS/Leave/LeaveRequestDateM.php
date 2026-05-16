<?php

namespace App\Models\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestDateM extends Model
{
    use HasFactory;

    protected $table = 'leave_request_dates';

    protected $guarded = [];

    protected $casts = [
        'leave_date' => 'date',
        'is_working_day' => 'boolean',
        'is_weekoff' => 'boolean',
        'is_holiday' => 'boolean',
        'is_sandwich_day' => 'boolean',
        'deduct_as_leave' => 'boolean',
        'paid_day' => 'decimal:2',
        'sick_day' => 'decimal:2',
        'comp_off_day' => 'decimal:2',
        'lwp_day' => 'decimal:2',
    ];

    public function request()
    {
        return $this->belongsTo(LeaveRequestM::class, 'leave_request_id');
    }

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }
}
