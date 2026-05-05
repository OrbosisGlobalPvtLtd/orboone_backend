<?php

namespace App\Models\HRMS\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecruitmentM extends Model
{
    use HasFactory;

    protected $table = 'recruitments';

    protected $guarded = [];

    protected static function newFactory()
    {
        return \Database\Factories\RecruitmentFactory::new();
    }

    public function position()
    {
        return $this->belongsTo(PositionM::class);
    }

    public function recruitmentCanditate()
    {
        return $this->hasMany(RecruitmentCandidateM::class, 'recruitment_id');
    }

    public function get()
    {
        return $this->where('is_active', 1)->latest()->take(3)->get();
    }

    public function paginate($count = 10)
    {
        return $this->with('position')->latest()->paginate($count);
    }
}
