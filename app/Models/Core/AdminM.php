<?php

namespace App\Models\Core;

use App\Models\Core\RoleM as Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminM extends Model
{
    use HasFactory;

    protected $table = 'admins';
    protected $guarded = [];

    public function role() 
    {
        return $this->belongsTo(Role::class);
    }
}
