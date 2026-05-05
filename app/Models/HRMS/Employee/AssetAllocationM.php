<?php

namespace App\Models\HRMS\Employee;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAllocationM extends Model
{
    use HasFactory;
    protected $table = 'asset_allocations';

    protected $fillable = [
        'employee_id',
        'asset_type',
        'asset_id_sn',
        'brand_model',
        'issue_date',
        'condition',
        'mobile_sim_number',
        'id_card_options',
        'has_charger',
        'has_bag',
        'sim_details',
        'plan_details',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeM::class);
    }
}
