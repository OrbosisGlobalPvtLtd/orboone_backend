<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAllocation extends Model
{
    use HasFactory;

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
        return $this->belongsTo(Employee::class);
    }
}
