<?php

namespace App\Models\HRMS\Notification;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\UserM;

class NotificationRetentionSettingM extends Model
{
    protected $table = 'notification_retention_settings';

    protected $fillable = [
        'notification_type',
        'display_name',
        'retention_days',
        'delete_only_read',
        'is_active',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'retention_days' => 'integer',
        'delete_only_read' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(UserM::class, 'created_by_user_id');
    }

    public function updater()
    {
        return $this->belongsTo(UserM::class, 'updated_by_user_id');
    }
}
