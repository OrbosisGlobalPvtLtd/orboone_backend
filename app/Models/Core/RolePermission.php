<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Core\RoleM;
use App\Models\Core\PermissionM;

class RolePermission extends Model
{
    use HasFactory;

    protected $table = 'role_permissions';

    protected $fillable = [
        'role_id',
        'permission_id',
    ];

    public function role()
    {
        return $this->belongsTo(RoleM::class, 'role_id');
    }

    public function permission()
    {
        return $this->belongsTo(PermissionM::class, 'permission_id');
    }
}