<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RoleM extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
        'status',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'status' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionM::class,
            'role_permissions',
            'role_id',
            'permission_id'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            UserM::class,
            'user_roles',
            'role_id',
            'user_id'
        );
    }

    protected static function newFactory()
    {
        return \Database\Factories\RoleFactory::new();
    }

    public function admin()
    {
        return $this->hasOne(AdminM::class, 'role_id');
    }

    public function paginate($count = 10)
    {
        return $this->latest()->paginate($count);
    }

    public function isAdmin()
    {
        return $this->admin()->whereRoleId($this->id)->count() == 1;
    }
}
