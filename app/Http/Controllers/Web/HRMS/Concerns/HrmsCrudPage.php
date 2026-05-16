<?php

namespace App\Http\Controllers\Web\HRMS\Concerns;

use App\Models\Core\AccessM;
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
        $user = auth()->user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission($permission);
    }

    protected function canViewAll(string $permission): bool
    {
        $user = auth()->user();

        return ($user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
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

        $ids = DB::table('employees_new')
            ->where('reporting_manager_employee_id', $employee->id)
            ->pluck('id')
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

    protected function scopeEmployeeVisibility($query, string $allPermission, ?string $teamPermission = null, string $column = 'employee_id')
    {
        if ($this->canViewAll($allPermission)) {
            return $query;
        }

        if ($teamPermission && $this->canViewTeam($teamPermission)) {
            $ids = $this->teamEmployeeIds(true);
            abort_if(empty($ids), 403);
            return $query->whereIn($column, $ids);
        }

        $employeeId = $this->ownEmployeeId();
        abort_if(! $employeeId, 403);

        return $query->where($column, $employeeId);
    }

    protected function scopedEmployeeOptions(string $allPermission, ?string $teamPermission = null)
    {
        $query = DB::table('employees_new')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->select(
                'employees_new.id',
                'employees_new.employee_code',
                DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as display_name"),
                DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as user_name")
            );

        $this->scopeEmployeeVisibility($query, $allPermission, $teamPermission, 'employees_new.id');

        return $query->orderByRaw("COALESCE(users.name, employees_new.employee_code)")->get();
    }

    protected function accesses()
    {
        $roleId = auth()->user()->role_id ?? auth()->user()->system_role_id ?? null;

        return $roleId ? AccessM::where('role_id', $roleId)->get() : collect();
    }

    protected function employeeOptions()
    {
        return DB::table('employees_new')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->select(
                'employees_new.id',
                'employees_new.employee_code',
                DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as display_name")
            )
            ->orderByRaw("COALESCE(users.name, employees_new.employee_code)")
            ->get();
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

    protected function applyCommonFilters($query, Request $request, array $config)
    {
        foreach (($config['filterMap'] ?? []) as $input => $column) {
            if ($request->filled($input)) {
                $query->where($column, $request->input($input));
            }
        }

        if (! empty($config['dateColumn'])) {
            if ($request->filled('from')) {
                $query->whereDate($config['dateColumn'], '>=', $request->from);
            }

            if ($request->filled('to')) {
                $query->whereDate($config['dateColumn'], '<=', $request->to);
            }
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
