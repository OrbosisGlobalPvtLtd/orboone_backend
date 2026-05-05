<?php

namespace App\Models\HRMS\Employee;

use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionM extends Model
{
    use HasFactory;
    protected $table = 'positions';
    protected $guarded = [];

    protected static function newFactory()
    {
        return \Database\Factories\PositionFactory::new();
    }

    public function employees() {
        return $this->hasMany(EmployeeM::class);
    }

    public function paginate($count = 10) {
        return $this->latest()->paginate($count);
    }
}
