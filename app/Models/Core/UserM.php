<?php

namespace App\Models\Core;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $roles = array_map(fn ($r) => strtolower(trim((string) $r)), (array) $roles);

        // 1. Check primary role
        $primaryRoleId = $this->system_role_id ?: ($this->role_id ?? null);
        if ($primaryRoleId) {
            $primary = $this->primaryRole()->first();
            if ($primary) {
                if (in_array(strtolower($primary->slug), $roles, true) || in_array(strtolower($primary->name), $roles, true)) {
                    return true;
                }
            }
        }

        // 2. Check multiple roles from user_roles
        if (Schema::hasTable('user_roles')) {
            return $this->roles()
                ->where(function ($q) use ($roles) {
                    $q->whereIn(DB::raw('LOWER(roles.slug)'), $roles)
                        ->orWhereIn(DB::raw('LOWER(roles.name)'), $roles);
                })
                ->exists();
        }

        return false;
    }

    public function isAdmin(): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->hasRole([
            'super_admin',
            'admin',
            'hr_admin',
            'finance_admin',
            'project_admin',
            'operations_admin',
            'custom_admin',
            'manager',
        ]);
    }

    public function isHrAdmin(): bool
    {
        return $this->hasRole(['super_admin', 'admin', 'hr_admin', 'hr admin', 'hr', 'human resources']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(['super_admin', 'super admin']);
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
            if (
                str_starts_with($permissionKey, 'employees.') ||
                str_starts_with($permissionKey, 'employee.') ||
                str_starts_with($permissionKey, 'employee_') ||
                str_starts_with($permissionKey, 'attendance.') ||
                str_starts_with($permissionKey, 'attendance_') ||
                str_starts_with($permissionKey, 'leave.') ||
                str_starts_with($permissionKey, 'leave_') ||
                str_starts_with($permissionKey, 'departments.') ||
                str_starts_with($permissionKey, 'designations.') ||
                str_starts_with($permissionKey, 'organization_hierarchy.') ||
                str_starts_with($permissionKey, 'probation.') ||
                str_starts_with($permissionKey, 'internship.') ||
                str_starts_with($permissionKey, 'hrms_exit_policy.') ||
                str_starts_with($permissionKey, 'reporting.') ||
                str_starts_with($permissionKey, 'reporting_structure.') ||
                str_starts_with($permissionKey, 'document_generation.') ||
                str_starts_with($permissionKey, 'documents.') ||
                str_starts_with($permissionKey, 'company_documents.') ||
                str_starts_with($permissionKey, 'asset_allocation.') ||
                str_starts_with($permissionKey, 'asset_allocations.')
            ) {
                return true;
            }
        }

        // Fallback / Alias mappings
        if ($permissionKey === 'employees.update') {
            $permissionKey = 'employees.edit';
        } elseif ($permissionKey === 'attendance.regularization.view') {
            if (
                $this->hasPermission('attendance.regularization.view_all') ||
                $this->hasPermission('attendance.regularization.view_team') ||
                $this->hasPermission('attendance.regularization.view_own')
            ) {
                return true;
            }
        } elseif ($permissionKey === 'attendance.monthly_report.view') {
            if (
                $this->hasPermission('attendance.monthly_report.view_all') ||
                $this->hasPermission('attendance.monthly_report.view_team') ||
                $this->hasPermission('attendance.monthly_report.view_own')
            ) {
                return true;
            }
        } elseif ($permissionKey === 'attendance.work_reports.view') {
            if (
                $this->hasPermission('attendance.work_reports.view_all') ||
                $this->hasPermission('attendance.work_reports.view_team') ||
                $this->hasPermission('attendance.work_reports.view_own')
            ) {
                return true;
            }
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

        // 4. Employee Position / Designation Permission Check
        if (Schema::hasTable('employees_new') && Schema::hasTable('designation_module_access')) {
            $employee = DB::table('employees_new')->where('user_id', $this->id)->first(['designation_id', 'department_id']);
            if ($employee && ! empty($employee->designation_id)) {
                $hasPosPerm = DB::table('designation_module_access')
                    ->where('designation_id', $employee->designation_id)
                    ->where('permission_key', $permissionKey)
                    ->where(function ($q) {
                        $q->where('is_allowed', 1)->orWhere('is_enabled', 1);
                    })
                    ->exists();

                if ($hasPosPerm) {
                    return true;
                }
            }

            // 5. Employee Profile / Department Baseline Check
            if ($employee && ! empty($employee->department_id) && Schema::hasTable('department_module_access')) {
                $hasDeptPerm = DB::table('department_module_access')
                    ->where('department_id', $employee->department_id)
                    ->where('permission_key', $permissionKey)
                    ->where(function ($q) {
                        $q->where('is_allowed', 1)->orWhere('is_enabled', 1);
                    })
                    ->exists();

                if ($hasDeptPerm) {
                    return true;
                }
            }
        } elseif (Schema::hasTable('employees_new') && Schema::hasTable('department_module_access')) {
            $employee = DB::table('employees_new')->where('user_id', $this->id)->first(['department_id']);
            if ($employee && ! empty($employee->department_id)) {
                $hasDeptPerm = DB::table('department_module_access')
                    ->where('department_id', $employee->department_id)
                    ->where('permission_key', $permissionKey)
                    ->where(function ($q) {
                        $q->where('is_allowed', 1)->orWhere('is_enabled', 1);
                    })
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
