<?php

namespace App\Models\HRMS\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoreCategoryM extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return \Database\Factories\ScoreCategoryFactory::new();
    }

    public function paginate($count = 10) 
    {
        return $this->orderBy('id', 'ASC')->paginate($count);
    }
}
