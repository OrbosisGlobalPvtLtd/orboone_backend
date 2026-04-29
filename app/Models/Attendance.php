<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'is_late',
        'is_early_out',
        'working_hours',
        'status',
        'leave_marking',
        'is_blocked',
        'total_break_time',
        'manual_unlock_by',
        'note',
        'punch_in_note',
        'punch_out_note',
        'work_type',
        'latitude',
        'longitude',
    ];

    public function employee()
    {
        // Many attendances belong to one user, and each user has one employee record.
        // We can access employee via user or directly if we use user_id consistently.
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Human-readable gross duration (Total Stay)
    public function getDurationAttribute()
    {
        if ($this->clock_in && $this->clock_out) {
            $in = \Carbon\Carbon::parse($this->clock_in);
            $out = \Carbon\Carbon::parse($this->clock_out);
            
            // Handle PM ambiguity for formatting if needed
            if ($out->lt($in) && $out->hour < 12) {
                $out->addHours(12);
            }
            
            $diff = $in->diff($out);
            return $diff->format('%h hours %i mins');
        }
        return 'N/A';
    }

    // Human-readable net working hours
    public function getNetDurationAttribute()
    {
        if ($this->working_hours) {
            $h = floor((float)$this->working_hours);
            $m = round(((float)$this->working_hours - $h) * 60);
            return "{$h} hours {$m} mins";
        }
        return 'N/A';
    }
}



