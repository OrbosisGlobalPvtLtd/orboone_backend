<?php

namespace App\Models\HRMS\Announcement;

use App\Models\HRMS\Department\DepartmentM as Department;
use App\Models\Core\UserM as User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementM extends Model
{
    use HasFactory;
    protected $table = 'announcements';
    protected $guarded = [];

    protected static function newFactory()
    {
        return \Database\Factories\AnnouncementFactory::new();
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function get($count = 3) {
        return $this->where('department_id', null)->latest()->take($count)->get();
    }

    public function paginate($count = 10) {
        return $this->with('creator', 'department')->latest()->paginate($count);
    }

    public function getCreatedAtAttribute($value) {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
}
