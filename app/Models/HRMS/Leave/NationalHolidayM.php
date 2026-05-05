<?php

namespace App\Models\HRMS\Leave;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NationalHolidayM extends Model
{
    use HasFactory;
    protected $table = 'national_holidays';
    protected $fillable = ['name', 'holiday_date', 'description'];
}
