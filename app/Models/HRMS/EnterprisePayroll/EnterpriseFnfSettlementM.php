<?php

namespace App\Models\HRMS\EnterprisePayroll;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Model;

class EnterpriseFnfSettlementM extends Model
{
    protected $table = 'enterprise_fnf_settlements';

    protected $guarded = [];

    protected $casts = [
        'settlement_month' => 'integer',
        'settlement_year' => 'integer',
        'pending_salary' => 'decimal:2',
        'leave_encashment' => 'decimal:2',
        'reimbursement_amount' => 'decimal:2',
        'deductions' => 'decimal:2',
        'final_payable' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class, 'employee_id');
    }
}
