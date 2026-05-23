<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class NotificationM extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'employee_id',
        'title',
        'message',
        'type',
        'category',
        'data',
        'route_name',
        'route_params',
        'role_id',
        'is_read',
        'read_at',
        'created_by',
    ];

    protected $casts = [
        'data' => 'array',
        'route_params' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId);

            // Fallback in case employee_id based notification is being used
            $q->orWhereHas('user', function ($uq) use ($userId) {
                $uq->where('id', $userId);
            });
        });
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', 0);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', 1);
    }

    public function user()
    {
        return $this->belongsTo(UserM::class, 'user_id');
    }

    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }

        $this->is_read = true;
        $this->read_at = now();

        return $this->save();
    }
}
