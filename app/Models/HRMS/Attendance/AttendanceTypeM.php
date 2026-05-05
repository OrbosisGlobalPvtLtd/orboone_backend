<?php

namespace App\Models\HRMS\Attendance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceTypeM extends Model
{
    use HasFactory;

    protected $table = 'attendance_types';

    protected $fillable = [
        'name',
        'code',
        'is_paid',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function attendances()
    {
        return $this->hasMany(AttendanceM::class, 'attendance_type_id');
    }
}