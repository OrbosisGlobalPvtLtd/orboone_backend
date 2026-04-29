<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PermissionM extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'module',
        'submodule',
        'action',
        'key',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            RoleM::class,
            'role_permissions',
            'permission_id',
            'role_id'
        );
    }
}