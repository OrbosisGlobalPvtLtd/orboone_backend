<?php

namespace App\Models\HRMS\Leave;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveTypeM extends Model
{
    use HasFactory;

    protected $table = 'leave_types';

    protected $guarded = [];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_sick' => 'boolean',
        'is_lwp' => 'boolean',
        'is_comp_off' => 'boolean',
        'requires_attachment' => 'boolean',
        'medical_certificate_after_days' => 'integer',
        'max_days_per_month' => 'decimal:2',
        'max_days_per_request' => 'decimal:2',
        'allow_half_day' => 'boolean',
        'applicable_after_confirmation' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function requests()
    {
        return $this->hasMany(LeaveRequestM::class, 'leave_type_id');
    }
}
