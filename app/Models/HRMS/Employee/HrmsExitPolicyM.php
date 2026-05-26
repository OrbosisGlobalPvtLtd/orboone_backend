<?php

namespace App\Models\HRMS\Employee;

use Illuminate\Database\Eloquent\Model;

class HrmsExitPolicyM extends Model
{
    protected $table = 'hrms_exit_policies';

    protected $guarded = [];

    protected $casts = [
        'notice_period_days' => 'integer',
        'allow_waiver' => 'boolean',
        'allow_buyout' => 'boolean',
        'allow_immediate_exit' => 'boolean',
        'fnf_processing_days' => 'integer',
        'is_active' => 'boolean',
        'effective_from' => 'date',
    ];
}

