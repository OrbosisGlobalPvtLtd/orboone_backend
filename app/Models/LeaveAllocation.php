<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveAllocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id', 
        'year', 
        'total_pl', 
        'total_sl', 
        'used_pl', 
        'used_sl', 
        'lwp_days'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
