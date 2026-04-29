<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'file',
        'category',
        'uploaded_by',
        'status',       // Pending, Approved, Rejected
        'remarks'
    ];



     public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
