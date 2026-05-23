<?php

namespace App\Models\HRMS\EnterprisePayroll;

use Illuminate\Database\Eloquent\Model;

class EnterprisePayrollItemM extends Model
{
    protected $table = 'enterprise_payroll_items';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];

    public function payroll()
    {
        return $this->belongsTo(EnterprisePayrollM::class, 'payroll_id');
    }
}
