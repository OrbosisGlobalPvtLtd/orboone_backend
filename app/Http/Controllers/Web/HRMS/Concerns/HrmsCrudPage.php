<?php

namespace App\Http\Controllers\Web\HRMS\Concerns;

use App\Models\Core\AccessM;
use App\Models\Core\UserM as User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait HrmsCrudPage
{
    protected function currentEmployee()
    {
        return DB::table('employees_new')->where('user_id', Auth::id())->first();
    }

    protected function userHasPermission(string $permission): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission($permission);
    }

    protected function canViewAll(string $permission): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $roleName = strtolower($user->role->name ?? '');

        return (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || in_array($roleId, [1, 2, 3], true)
            || in_array($roleName, ['super_admin', 'super admin', 'admin', 'hr_admin', 'hr admin', 'hr', 'human resources'], true)
            || $this->userHasPermission($permission);
    }

    protected function canViewTeam(string $permission): bool
    {
        return $this->userHasPermission($permission);
    }

    protected function teamEmployeeIds(bool $includeSelf = true): array
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return [];
        }

        $today = Carbon::now('Asia/Kolkata')->toDateString();
        $ids = DB::table('employees_new')
            ->leftJoin('employee_profiles', 'employee_profiles.employee_id', '=', 'employees_new.id')
            ->where('employees_new.reporting_manager_employee_id', $employee->id)
            ->where('employees_new.employment_status', 'active')
            ->where(function ($q) {
                $q->whereNull('employees_new.is_active')->orWhere('employees_new.is_active', 1);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('employees_new.relieving_date')->orWhere('employees_new.relieving_date', '>', $today);
            })
            ->where('employee_profiles.is_profile_completed', 1)
            ->where(function ($query) {
                $query->whereNull('employee_profiles.profile_status')
                      ->orWhere('employee_profiles.profile_status', 'approved');
            })
            ->pluck('employees_new.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($includeSelf) {
            $ids[] = (int) $employee->id;
        }

        return array_values(array_unique($ids));
    }

    protected function ownEmployeeId(): ?int
    {
        return optional($this->currentEmployee())->id;
    }

    protected function scopeEmployeeVisibility(mixed $query, string $allPermission, ?string $teamPermission = null, string $column = 'employee_id')
    {
        /** @var User|null $user */
        $user = Auth::user();

        // 1. If user has full view/manage permission (via SuperAdmin, User Override, Role, Position, or Dept)
        if ($this->canViewAll($allPermission)) {
            return $query;
        }

        // 2. If user has team view permission (e.g. Manager / Team Lead) or has subordinates
        $teamIds = $this->teamEmployeeIds(false);
        if (($teamPermission && $this->canViewTeam($teamPermission)) || ! empty($teamIds) || (method_exists($user, 'hasRole') && $user->hasRole(['manager', 'lead', 'team_lead']))) {
            $ids = $this->teamEmployeeIds(true);
            if (! empty($ids)) {
                return $query->whereIn($column, $ids);
            }
        }

        // 3. Fallback to own employee record
        $employeeId = $this->ownEmployeeId();
        abort_if(! $employeeId, 403);

        return $query->where($column, $employeeId);
    }

    protected function scopedEmployeeOptions(string $allPermission, ?string $teamPermission = null, bool $onlyActiveEligible = true)
    {
        $query = DB::table('employees_new')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->leftJoin('employee_profiles', 'employee_profiles.employee_id', '=', 'employees_new.id')
            ->select(
                'employees_new.id',
                'employees_new.employee_code',
                DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as display_name"),
                DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as user_name")
            );

        if ($onlyActiveEligible) {
            $today = Carbon::now('Asia/Kolkata')->toDateString();
            $query->where('employees_new.employment_status', 'active')
                ->where(function ($q) {
                    $q->whereNull('employees_new.is_active')->orWhere('employees_new.is_active', 1);
                })
                ->where(function ($q) use ($today) {
                    $q->whereNull('employees_new.relieving_date')->orWhere('employees_new.relieving_date', '>', $today);
                })
                ->where('employee_profiles.is_profile_completed', 1)
                ->where(function ($st) {
                    $st->whereNull('employee_profiles.profile_status')->orWhere('employee_profiles.profile_status', 'approved');
                });
        }

        $this->scopeEmployeeVisibility($query, $allPermission, $teamPermission, 'employees_new.id');

        return $query->orderByRaw("COALESCE(users.name, employees_new.employee_code)")->get();
    }

    protected function accesses()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $roleId = $user?->role_id ?? $user?->system_role_id ?? null;

        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }

    protected function employeeOptions(bool $onlyActiveEligible = true)
    {
        $query = DB::table('employees_new')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->leftJoin('employee_profiles', 'employee_profiles.employee_id', '=', 'employees_new.id')
            ->select(
                'employees_new.id',
                'employees_new.employee_code',
                'employees_new.work_mode',
                DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as display_name")
            );

        if ($onlyActiveEligible) {
            $today = Carbon::now('Asia/Kolkata')->toDateString();
            $query->where('employees_new.employment_status', 'active')
                ->where(function ($q) {
                    $q->whereNull('employees_new.is_active')->orWhere('employees_new.is_active', 1);
                })
                ->where(function ($q) use ($today) {
                    $q->whereNull('employees_new.relieving_date')->orWhere('employees_new.relieving_date', '>', $today);
                })
                ->where('employee_profiles.is_profile_completed', 1)
                ->where(function ($st) {
                    $st->whereNull('employee_profiles.profile_status')->orWhere('employee_profiles.profile_status', 'approved');
                });
        }

        return $query->orderByRaw("COALESCE(users.name, employees_new.employee_code)")->get();
    }

    protected function employeeJoinedQuery(string $table, string $employeeColumn = 'employee_id')
    {
        return DB::table($table)
            ->leftJoin('employees_new', 'employees_new.id', '=', "{$table}.{$employeeColumn}")
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->select(
                "{$table}.*",
                'employees_new.employee_code',
                DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as employee_display_name")
            );
    }

    protected function applyCommonFilters(mixed $query, Request $request, array $config)
    {
        foreach (($config['filterMap'] ?? []) as $input => $column) {
            if ($request->filled($input) && $request->input($input) !== 'all') {
                $query->where($column, $request->input($input));
            }
        }

        if (! empty($config['dateColumn'])) {
            $fromDate = $request->input('from_date') ?: $request->input('from');
            $toDate = $request->input('to_date') ?: $request->input('to');

            if ($fromDate) {
                $query->whereDate($config['dateColumn'], '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate($config['dateColumn'], '<=', $toDate);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('employees_new.employee_code', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    protected function boolValue(Request $request, string $key, bool $default = false): bool
    {
        if (! $request->has($key)) {
            return $default;
        }

        return $request->boolean($key);
    }

    protected function nowKolkata()
    {
        return Carbon::now('Asia/Kolkata');
    }

    protected function actorId(): ?int
    {
        return Auth::id();
    }
}
