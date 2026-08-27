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
        // 1. Super Admin = full access
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // Fallback mapping: treat employees.update as employees.edit
        if ($permissionKey === 'employees.update') {
            $permissionKey = 'employees.edit';
        }

        // 2. User Level Direct Override Check
        if (Schema::hasTable('user_module_access')) {
            $userOverride = DB::table('user_module_access')
                ->where('user_id', $this->id)
                ->where('permission_key', $permissionKey)
                ->first(['is_allowed', 'is_enabled']);

            if ($userOverride) {
                return (bool) ($userOverride->is_allowed ?? $userOverride->is_enabled);
            }
        }

        // 3. Role Level Permission Check
        $roleIds = [];

        if ($this->system_role_id) {
            $roleIds[] = (int) $this->system_role_id;
        }

        if (Schema::hasTable('user_roles')) {
            $roleIds = array_merge(
                $roleIds,
                DB::table('user_roles')->where('user_id', $this->id)->pluck('role_id')->map(fn ($id) => (int) $id)->all()
            );
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

        // 4. Employee Profile / Department Baseline Check
        if (Schema::hasTable('employees_new') && Schema::hasTable('department_module_access')) {
            $employee = DB::table('employees_new')->where('user_id', $this->id)->first(['department_id']);
            if ($employee && ! empty($employee->department_id)) {
                $hasDeptPerm = DB::table('department_module_access')
                    ->where('department_id', $employee->department_id)
                    ->where('permission_key', $permissionKey)
                    ->where('is_allowed', 1)
                    ->exists();

                if ($hasDeptPerm) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasModuleAccess(string $moduleKey): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        if (Schema::hasTable('user_module_access')) {
            $userMod = DB::table('user_module_access')
                ->where('user_id', $this->id)
                ->where('module_key', $moduleKey)
                ->whereNull('permission_key')
                ->first(['is_allowed', 'is_enabled']);

            if ($userMod) {
                return (bool) ($userMod->is_allowed ?? $userMod->is_enabled);
            }
        }

        return true;
    }
}
