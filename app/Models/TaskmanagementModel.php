<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        'updates',
    ];

    public $timestamps = true;

    // Task belongs to a User (assignee)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
