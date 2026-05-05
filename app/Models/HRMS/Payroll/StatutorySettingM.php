<?php

namespace App\Models\HRMS\Payroll;

use Illuminate\Database\Eloquent\Model;

class StatutorySettingM extends Model
{
    protected $fillable = [
        'pf_percent',
        'esi_percent',
        'pt_percent',
        'tds_slabs',   // stored as JSON
    ];

    protected $casts = [
        'pf_percent'  => 'decimal:2',
        'esi_percent' => 'decimal:2',
        'pt_percent'  => 'decimal:2',
        'tds_slabs'   => 'array',
    ];
}
