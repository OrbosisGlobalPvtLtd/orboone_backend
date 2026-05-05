<?php

namespace App\Models\HRMS\Leave;

use App\Models\HRMS\Employee\EmployeeM as Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveRequestM extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function checkedBy(){
        return $this->belongsTo(Employee::class, 'checked_by');
    }

    public function paginate($count = 10) {
        if(auth()->user()->isAdmin()) {
            return $this->with('employee', 'checkedBy')->latest()->paginate($count);
        } else {
            return $this->with('employee', 'checkedBy')->where('employee_id', auth()->user()->employee->id)->latest()->paginate($count);
        }
    }
}
