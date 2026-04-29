<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id', 
        'leave_type', 
        'start_date', 
        'end_date', 
        'total_days', 
        'lwp_days',
        'reason',
        'attachment',
        'status', 
        'approved_by', 
        'admin_remark'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
