<?php

namespace App\Models\HRMS\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecruitmentCandidateM extends Model
{
    use HasFactory;

    protected $table = 'recruitment_candidates';

    protected $guarded = [];

    public function getCount()
    {
        return $this->count();
    }
}
