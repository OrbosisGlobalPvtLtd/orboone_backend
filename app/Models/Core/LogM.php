<?php

namespace App\Models\Core;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogM extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
}
