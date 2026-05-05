<?php

namespace App\Models\HRMS\Leave;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveTypeM extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'yearly_quota',
        'accrual_type',
    ];
}
