<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationM extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'is_read'
    ];
}
