<?php

namespace App\Models\HRMS\Employee;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\UserM;

class EmployeeInternshipExtensionM extends Model
{
    protected $table = 'employee_internship_extensions';

    protected $fillable = [
        'employee_id',
        'old_end_date',
        'new_end_date',
        'reason',
        'extended_by_user_id',
        'extended_at',
    ];

    protected $casts = [
        'old_end_date' => 'date',
        'new_end_date' => 'date',
        'extended_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }

    public function extendedBy()
    {
        return $this->belongsTo(UserM::class, 'extended_by_user_id');
    }
}