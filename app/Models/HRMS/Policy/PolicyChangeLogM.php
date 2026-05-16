<?php

namespace App\Models\HRMS\Policy;

use App\Models\Core\UserM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyChangeLogM extends Model
{
    use HasFactory;

    protected $table = 'policy_change_logs';

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function changedBy()
    {
        return $this->belongsTo(UserM::class, 'changed_by_user_id');
    }
}
