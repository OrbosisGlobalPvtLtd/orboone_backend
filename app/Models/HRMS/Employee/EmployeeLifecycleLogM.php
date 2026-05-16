<?php

namespace App\Models\HRMS\Employee;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\UserM;

class EmployeeLifecycleLogM extends Model
{
    protected $table = 'employee_lifecycle_logs';

    protected $fillable = [
        'employee_id',
        'action',
        'old_value',
        'new_value',
        'remarks',
        'performed_by_user_id',
        'performed_at',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'performed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(UserM::class, 'performed_by_user_id');
    }
}