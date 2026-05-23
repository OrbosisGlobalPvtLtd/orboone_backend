<?php

namespace App\Models\HRMS\EnterprisePayroll;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

class EnterpriseReimbursementM extends Model
{
    protected $table = 'enterprise_reimbursements';

    protected $guarded = [];

    protected $casts = [
        'claim_date' => 'date',
        'amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }
}
