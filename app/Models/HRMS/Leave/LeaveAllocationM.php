<?php

namespace App\Models\HRMS\Leave;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveAllocationM extends Model
{
    use HasFactory;
    protected $table = 'leave_allocations';
    protected $fillable = [
        'employee_id', 
        'year', 
        'total_pl', 
        'total_sl', 
        'used_pl', 
        'used_sl', 
        'lwp_days'
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class);
    }
}
