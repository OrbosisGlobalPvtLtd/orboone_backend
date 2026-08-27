<?php

namespace App\Models\Core;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Core\RoleM;
use App\Models\Core\PermissionM;
use App\Models\HRMS\Employee\EmployeeM;

class UserM extends Authenticatable
{
    use HasApiTokens, HasFactory;
    protected $table = 'users';
     

    public function role()
    {
        return $this->belongsTo(RoleM::class, 'system_role_id', 'id');
    }

    protected $fillable = [
        'system_role_id',
        'name',
        'email',
        'phone',
        'username',
        'password',
        'fcm_token',
        'device_token',
        'is_active',
        'is_web_access',
        'is_app_access',
        'last_login_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'is_web_access' => 'boolean',
        'is_app_access' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function primaryRole(): BelongsTo
    {
        return $this->belongsTo(RoleM::class, 'system_role_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            RoleM::class,
            'user_roles',
            'user_id',
            'role_id'
        );
    }

    public function employee(): HasOne
    {
        return $this->hasOne(EmployeeM::class, 'user_id');
    }

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    public function paginate($count = 10)
    {
        return $this->with('role')->latest()->paginate($count);
    }

    public function getProfile()
    {
        return $this->with('employee')->where('id', auth()->id())->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function hasRole($roles): bool
    {
        $roles = (array) $roles;

        // ✅ check primary role
        if ($this->system_role_id) {
            $primary = $this->primaryRole()->first();
            if ($primary && in_array($primary->slug, $roles)) {
                return true;
            }
        }

        // ✅ check multiple roles
        return $this->roles()
            ->whereIn('slug', $roles)
            ->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole([
            'super_admin',
            'admin',
            'hr_admin',
            'finance_admin',
            'project_admin',
            'operations_admin',
            'custom_admin',
        ]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    public function hasWebAdminAccess(): bool
    {
        if (!$this->is_active || !$this->is_web_access) {
            return false;
        }

        return $this->isAdmin();
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Helpers
    |--------------------------------------------------------------------------
    */

    public function hasPermission(string $permissionKey): bool
    {
        // ✅ Super Admin = full access
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Fallback mapping: treat employees.update as employees.edit
        if ($permissionKey === 'employees.update') {
            $permissionKey = 'employees.edit';
        }

        // collect role ids
        $roleIds = $this->roles()->pluck('roles.id')->toArray();

        if ($this->system_role_id) {
            $roleIds[] = $this->system_role_id;
        }

        $roleIds = array_unique($roleIds);

        if (empty($roleIds)) {
            return false;
        }

        return PermissionM::query()
            ->where('key', $permissionKey)
            ->whereHas('roles', function ($query) use ($roleIds) {
                $query->whereIn('roles.id', $roleIds);
            })
            ->exists();
    }

    public function hasModuleAccess(string $moduleKey): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return DB::table('user_module_access')
            ->where('user_id', $this->id)
            ->where('module_key', $moduleKey)
            ->where('is_enabled', 1)
            ->exists();
    }
}
