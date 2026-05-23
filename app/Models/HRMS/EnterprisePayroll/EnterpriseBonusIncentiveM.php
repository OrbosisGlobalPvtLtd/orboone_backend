<?php

namespace App\Models\HRMS\EnterprisePayroll;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

class EnterpriseBonusIncentiveM extends Model
{
    protected $table = 'enterprise_bonus_incentives';

    protected $guarded = [];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'achievement_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'month' => 'integer',
        'year' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }
}
