<?php

namespace App\Models\HRMS\Attendance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLocationM extends Model
{
    use HasFactory;

    protected $table = 'attendance_locations';

    protected $fillable = [
        'name',
        'code',
        'latitude',
        'longitude',
        'radius_meters',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius_meters' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
