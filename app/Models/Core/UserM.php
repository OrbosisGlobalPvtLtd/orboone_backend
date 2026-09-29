<?php

namespace App\Models\Core;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Core\RoleM;
use App\Models\Core\PermissionM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\AccessControl\PermissionMapS;

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
        return $this->with('employee')->where('id', Auth::id())->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * In-memory cache of role slugs and names for the user instance.
     * @var array<string>|null
     */
    protected ?array $cachedRoleTokens = null;

    /**
     * Get all role tokens (slugs and names in lowercase) for this user instance.
     *
     * @return array<string>
     */
    public function roleTokens(): array
    {
        if ($this->cachedRoleTokens !== null) {
            return $this->cachedRoleTokens;
        }

        $tokens = [];

        // 1. Primary role (system_role_id or role_id)
        $primaryRoleId = $this->system_role_id ?: ($this->role_id ?? null);
        if ($primaryRoleId) {
            $primary = $this->primaryRole()->first();
            if ($primary) {
                if (! empty($primary->slug)) {
                    $tokens[] = strtolower(trim((string) $primary->slug));
                }
                if (! empty($primary->name)) {
                    $tokens[] = strtolower(trim((string) $primary->name));
                }
            }
        }

        // 2. Multiple roles from user_roles
        $secondaryRoles = $this->roles()->get(['roles.slug', 'roles.name']);
        foreach ($secondaryRoles as $r) {
            if (! empty($r->slug)) {
                $tokens[] = strtolower(trim((string) $r->slug));
            }
            if (! empty($r->name)) {
                $tokens[] = strtolower(trim((string) $r->name));
            }
        }

        $this->cachedRoleTokens = array_values(array_unique(array_filter($tokens)));

        return $this->cachedRoleTokens;
    }

    /**
     * Clear in-memory cached role tokens on this user instance.
     */
    public function forgetRoleCache(): self
    {
        $this->cachedRoleTokens = null;

        return $this;
    }

    public function hasRole($roles): bool
    {
        $targetRoles = array_map(fn ($r) => strtolower(trim((string) $r)), (array) $roles);
        $userTokens = $this->roleTokens();

        foreach ($targetRoles as $target) {
            if (in_array($target, $userTokens, true)) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->hasRole(config('authorization.admin_role_slugs', [
            'super_admin',
            'admin',
            'hr_admin',
            'finance_admin',
            'project_admin',
            'operations_admin',
            'custom_admin',
            'manager',
        ]));
    }

    public function isHrAdmin(): bool
    {
        return $this->hasRole(config('authorization.hr_admin_slugs', [
            'super_admin',
            'admin',
            'hr_admin',
            'hr admin',
            'hr',
            'human resources',
        ]));
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(config('authorization.super_admin_slugs', [
            'super_admin',
            'super admin',
        ]));
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
        // 1. Super Admin = full access
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Support pipe-separated permission keys (e.g. 'perm.a|perm.b')
        if (str_contains($permissionKey, '|')) {
            $keys = array_filter(array_map('trim', explode('|', $permissionKey)));
            foreach ($keys as $k) {
                if ($this->hasPermission($k)) {
                    return true;
                }
            }
            return false;
        }

        // 2. HR Admin = full access to all HRMS operations, Employee Management, Attendance & Leave
        if ($this->isHrAdmin()) {
            foreach (PermissionMapS::getHrAdminPrefixes() as $prefix) {
                if (str_starts_with($permissionKey, $prefix)) {
                    return true;
                }
            }
        }

        // Fallback / Alias mappings
        $aliases = PermissionMapS::getPermissionAliases();
        if (isset($aliases[$permissionKey])) {
            $permissionKey = $aliases[$permissionKey];
        }

        // 2. User Level Direct Override Check
        $userOverride = DB::table('user_module_access')
            ->where('user_id', $this->id)
            ->where('permission_key', $permissionKey)
            ->first(['is_allowed', 'is_enabled']);

        if ($userOverride) {
            return (bool) ($userOverride->is_allowed ?? $userOverride->is_enabled);
        }

        $attendanceExpansions = PermissionMapS::getAttendanceAliasExpansions();
        if (isset($attendanceExpansions[$permissionKey])) {
            foreach ($attendanceExpansions[$permissionKey] as $expandedKey) {
                if ($this->hasPermission($expandedKey)) {
                    return true;
                }
            }
        }

        // 3. Role Level Permission Check
        $roleIds = [];

        if ($this->system_role_id) {
            $roleIds[] = (int) $this->system_role_id;
        }

        $userRoleIds = DB::table('user_roles')->where('user_id', $this->id)->pluck('role_id')->map(fn ($id) => (int) $id)->all();
        if (! empty($userRoleIds)) {
            $roleIds = array_merge($roleIds, $userRoleIds);
        }

        $roleIds = array_unique(array_filter($roleIds));

        if (! empty($roleIds)) {
            $hasRolePerm = PermissionM::query()
                ->where('key', $permissionKey)
                ->whereHas('roles', function ($query) use ($roleIds) {
                    $query->whereIn('roles.id', $roleIds);
                })
                ->exists();

            if ($hasRolePerm) {
                return true;
            }
        }

        // 4. Employee Position / Designation & Department Check
        $employee = DB::table('employees_new')->where('user_id', $this->id)->first(['designation_id', 'department_id']);
        if ($employee) {
            if (! empty($employee->designation_id) && $this->checkBaselineAccess('designation_module_access', 'designation_id', (int) $employee->designation_id, $permissionKey)) {
                return true;
            }

            // 5. Employee Profile / Department Baseline Check
            if (! empty($employee->department_id) && $this->checkBaselineAccess('department_module_access', 'department_id', (int) $employee->department_id, $permissionKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a baseline access table (designation or department) grants permission.
     */
    private function checkBaselineAccess(string $table, string $foreignKey, int $foreignId, string $permissionKey): bool
    {
        return DB::table($table)
            ->where($foreignKey, $foreignId)
            ->where('permission_key', $permissionKey)
            ->where(function ($q) {
                $q->where('is_allowed', 1)->orWhere('is_enabled', 1);
            })
            ->exists();
    }

    public function hasModuleAccess(string $moduleKey): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        $userMod = DB::table('user_module_access')
            ->where('user_id', $this->id)
            ->where('module_key', $moduleKey)
            ->whereNull('permission_key')
            ->first(['is_allowed', 'is_enabled']);

        if ($userMod) {
            return (bool) ($userMod->is_allowed ?? $userMod->is_enabled);
        }

        return true;
    }
}
