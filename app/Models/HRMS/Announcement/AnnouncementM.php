<?php

namespace App\Models\HRMS\Announcement;

use App\Models\Core\UserM;
use Illuminate\Database\Eloquent\Model;

class AnnouncementM extends Model
{
    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'description',
        'type',
        'priority',
        'target_type',
        'created_by_user_id',
        'start_date',
        'end_date',
        'attachment',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(UserM::class, 'created_by_user_id');
    }
}
