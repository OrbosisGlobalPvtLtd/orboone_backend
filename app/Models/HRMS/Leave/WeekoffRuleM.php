<?php

namespace App\Models\HRMS\Leave;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeekoffRuleM extends Model
{
    use HasFactory;

    protected $table = 'weekoff_rules';

    protected $guarded = [];

    protected $casts = [
        'weekday' => 'integer',
        'week_number' => 'integer',
        'is_working' => 'boolean',
        'is_off' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];
}
