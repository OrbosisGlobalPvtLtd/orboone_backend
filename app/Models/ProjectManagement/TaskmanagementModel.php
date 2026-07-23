<?php

namespace App\Models\ProjectManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\UserM as User;

class TaskmanagementModel extends Model
{
    use HasFactory;

    const TABLE = 'taskmanagement';
    protected $table = self::TABLE;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'status',
        'user_id',
        'employee_name',
    ];

    public $timestamps = true;

    protected $casts = [
        'due_date' => 'date',
    ];

    // Task belongs to a User (assignee)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Parse metadata stored inside HTML comment in description column
     */
    private function getMetadata()
    {
        $raw = $this->attributes['description'] ?? '';
        if (preg_match('/<!--TASK_METADATA:(.*?)-->/s', $raw, $matches)) {
            $decoded = json_decode($matches[1], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [
            'extended_status' => null,
            'comments' => [],
            'timeline' => [],
            'attachments' => []
        ];
    }

    /**
     * Save metadata back into description column
     */
    private function setMetadata(array $data)
    {
        $raw = $this->attributes['description'] ?? '';
        $cleanDescription = preg_replace('/<!--TASK_METADATA:(.*?)-->/s', '', $raw);
        $cleanDescription = trim($cleanDescription);

        $json = json_encode($data);
        $this->attributes['description'] = $cleanDescription . "\n\n<!--TASK_METADATA:" . $json . "-->";
    }

    /**
     * Get clean description text without HTML metadata comment
     */
    public function getCleanDescriptionAttribute()
    {
        $raw = $this->attributes['description'] ?? '';
        $clean = preg_replace('/<!--TASK_METADATA:(.*?)-->/s', '', $raw);
        return trim($clean);
    }

    /**
     * Return effective status (extended_status or DB status)
     */
    public function getStatusAttribute($value)
    {
        $meta = $this->getMetadata();
        if (!empty($meta['extended_status'])) {
            return $meta['extended_status'];
        }
        return $value ?? 'pending';
    }

    /**
     * Set status: store extended status in metadata, and map to DB enum ('pending'/'completed')
     */
    public function setStatusAttribute($value)
    {
        $meta = $this->getMetadata();
        $meta['extended_status'] = $value;
        $this->setMetadata($meta);

        if (in_array($value, ['completed', 'verified', 'closed'])) {
            $this->attributes['status'] = 'completed';
        } else {
            $this->attributes['status'] = 'pending';
        }
    }

    /**
     * Get parsed updates structure (comments, timeline, attachments)
     */
    public function getUpdatesDataAttribute()
    {
        $meta = $this->getMetadata();
        return [
            'comments' => $meta['comments'] ?? [],
            'timeline' => $meta['timeline'] ?? [],
            'attachments' => $meta['attachments'] ?? []
        ];
    }

    /**
     * Helper to log a timeline event
     */
    public function logTimeline($event, $userId = null, $userName = 'System', $details = '', $fromStatus = null, $toStatus = null)
    {
        $meta = $this->getMetadata();
        $meta['timeline'][] = [
            'id' => 'tl_' . time() . '_' . rand(100, 999),
            'event' => $event,
            'user_id' => $userId,
            'user_name' => $userName,
            'details' => $details,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ];
        $this->setMetadata($meta);
    }

    /**
     * Helper to add a comment
     */
    public function addComment($commentText, $userId, $userName, $userRole = 'Employee', array $attachments = [])
    {
        $meta = $this->getMetadata();
        $commentItem = [
            'id' => 'c_' . time() . '_' . rand(100, 999),
            'user_id' => $userId,
            'user_name' => $userName,
            'role' => $userRole,
            'comment' => $commentText,
            'attachments' => $attachments,
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
        $meta['comments'][] = $commentItem;

        foreach ($attachments as $att) {
            $meta['attachments'][] = array_merge($att, [
                'uploaded_by' => $userName,
                'created_at' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        $this->setMetadata($meta);
        return $commentItem;
    }

    /**
     * Helper to add an attachment
     */
    public function addAttachment($fileName, $fileUrl, $fileType, $uploadedByName)
    {
        $meta = $this->getMetadata();
        $att = [
            'id' => 'att_' . time() . '_' . rand(100, 999),
            'name' => $fileName,
            'url' => $fileUrl,
            'type' => $fileType,
            'uploaded_by' => $uploadedByName,
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
        $meta['attachments'][] = $att;
        $this->setMetadata($meta);
        return $att;
    }

    /**
     * Check if task is overdue
     */
    public function getIsOverdueAttribute()
    {
        $currentStatus = $this->status;
        if (in_array($currentStatus, ['completed', 'verified', 'closed'])) {
            return false;
        }

        if (!$this->due_date) {
            return false;
        }

        $dueDate = is_string($this->due_date) ? \Carbon\Carbon::parse($this->due_date) : $this->due_date;
        return $dueDate->startOfDay()->isPast();
    }
}
