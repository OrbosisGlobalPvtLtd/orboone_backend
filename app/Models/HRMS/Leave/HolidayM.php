<?php

namespace App\Models\HRMS\Leave;

use App\Models\Core\UserM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HolidayM extends Model
{
    use HasFactory;

    protected $table = 'holidays';

    protected $guarded = [];

    protected $casts = [
        'holiday_date' => 'date',
        'is_national' => 'boolean',
        'is_optional' => 'boolean',
        'is_working_day_override' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(UserM::class, 'created_by_user_id');
    }
}
