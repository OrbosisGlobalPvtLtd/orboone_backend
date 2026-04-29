<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRolesM extends Model
{
    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
    ];

    public $timestamps = true;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // User relation
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserM::class, 'user_id');
    }

    // Role relation
    public function role(): BelongsTo
    {
        return $this->belongsTo(RoleM::class, 'role_id');
    }
}