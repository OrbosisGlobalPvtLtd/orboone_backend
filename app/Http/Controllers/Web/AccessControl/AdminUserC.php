<?php

namespace App\Http\Controllers\Web\AccessControl;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AccessControl\StoreAdminUserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserC extends Controller
{
    private const ADMIN_ROLE_SLUGS = [
        'super_admin',
        'admin',
        'hr_admin',
        'finance_admin',
        'project_admin',
        'operations_admin',
        'custom_admin',
    ];

    public function index()
    {
        $adminRoleIds = $this->adminRoleIds();
        $select = [
            'users.id',
            'users.name',
            'users.email',
            'users.is_active',
            'users.system_role_id',
            'primary_roles.name as primary_role_name',
            'primary_roles.slug as primary_role_slug',
        ];

        $select[] = Schema::hasColumn('users', 'is_web_access')
            ? 'users.is_web_access'
            : DB::raw('0 as is_web_access');

        $select[] = Schema::hasColumn('users', 'is_app_access')
            ? 'users.is_app_access'
            : DB::raw('0 as is_app_access');

        $users = DB::table('users')
            ->leftJoin('roles as primary_roles', 'primary_roles.id', '=', 'users.system_role_id')
            ->select($select)
            ->where(function ($q) use ($adminRoleIds) {
                if (Schema::hasColumn('users', 'is_web_access')) {
                    $q->where('users.is_web_access', 1);
                }

                if (! empty($adminRoleIds)) {
                    $q->orWhereIn('users.system_role_id', $adminRoleIds);

                    if (Schema::hasTable('user_roles')) {
                        $q->orWhereExists(function ($subQuery) use ($adminRoleIds) {
                            $subQuery->select(DB::raw(1))
                                ->from('user_roles')
                                ->whereColumn('user_roles.user_id', 'users.id')
                                ->whereIn('user_roles.role_id', $adminRoleIds);
                        });
                    }
                }
            })
            ->orderBy('users.name')
            ->paginate(15);

        $this->attachRoleSummaries($users);

        return view('access_control.admins.index', compact('users'));
    }

    public function create()
    {
        return view('access_control.admins.create', [
            'roles' => $this->adminRoles(),
        ]);
    }

    public function store(StoreAdminUserRequest $request)
    {
        DB::transaction(function () use ($request) {
            $selectedRoleIds = $this->selectedAdminRoleIds($request);
            $primaryRoleId = $selectedRoleIds[0];
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'system_role_id' => $primaryRoleId,
                'is_active' => $request->boolean('is_active', true),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('users', 'role_id')) {
                $userData['role_id'] = $primaryRoleId;
            }

            if (Schema::hasColumn('users', 'is_web_access')) {
                $userData['is_web_access'] = 1;
            }

            if (Schema::hasColumn('users', 'is_app_access')) {
                $userData['is_app_access'] = $request->boolean('is_app_access');
            }

            $userId = DB::table('users')->insertGetId($userData);

            $this->syncAdminRoles($userId, $selectedRoleIds);
        });

        return redirect()
            ->route('admins.index')
            ->with('success', 'Admin user created successfully.');
    }

    public function edit($admin)
    {
        $adminUser = $this->findAdmin($admin);
        abort_if(! $adminUser, 404);

        return view('access_control.admins.edit', [
            'admin' => $adminUser,
            'roles' => $this->adminRoles(),
        ]);
    }

    public function update(StoreAdminUserRequest $request, $admin)
    {
        $adminUser = $this->findAdmin($admin);
        abort_if(! $adminUser, 404);
        $selectedRoleIds = $this->selectedAdminRoleIds($request, $adminUser->id);

        DB::transaction(function () use ($request, $adminUser, $selectedRoleIds) {
            $primaryRoleId = $selectedRoleIds[0];
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'system_role_id' => $primaryRoleId,
                'is_active' => $request->boolean('is_active'),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('users', 'role_id')) {
                $userData['role_id'] = $primaryRoleId;
            }

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if (Schema::hasColumn('users', 'is_web_access')) {
                $employeeRoleId = null;
                if (Schema::hasTable('roles')) {
                    $employeeRoleId = DB::table('roles')->where('slug', 'employee')->value('id');
                }
                $userData['is_web_access'] = ($employeeRoleId && (int)$primaryRoleId === (int)$employeeRoleId) ? 0 : 1;
            }

            if (Schema::hasColumn('users', 'is_app_access')) {
                $userData['is_app_access'] = $request->boolean('is_app_access');
            }

            DB::table('users')->where('id', $adminUser->id)->update($userData);

            $this->syncAdminRoles($adminUser->id, $selectedRoleIds);
        });

        return redirect()
            ->route('admins.index')
            ->with('success', 'Admin user updated successfully.');
    }

    public function destroy($admin)
    {
        $adminUser = $this->findAdmin($admin);
        abort_if(! $adminUser, 404);

        if ((int) $adminUser->id === (int) auth()->id()) {
            return back()->with('error', 'You cannot delete your own admin account.');
        }

        if (in_array('super_admin', $adminUser->assigned_role_slugs ?? [], true)) {
            return back()->with('error', 'Super Admin users cannot be deleted.');
        }

        if (Schema::hasTable('employees_new') && DB::table('employees_new')->where('user_id', $adminUser->id)->exists()) {
            return back()->with('error', 'This user is linked with an employee record and cannot be deleted here.');
        }

        DB::transaction(function () use ($adminUser) {
            DB::table('user_roles')->where('user_id', $adminUser->id)->delete();
            DB::table('users')->where('id', $adminUser->id)->delete();
        });

        return redirect()
            ->route('admins.index')
            ->with('success', 'Admin user deleted successfully.');
    }

    private function findAdmin($admin)
    {
        $adminUser = DB::table('users')
            ->leftJoin('roles as primary_roles', 'primary_roles.id', '=', 'users.system_role_id')
            ->where('users.id', $admin)
            ->select(
                'users.*',
                'primary_roles.name as role_name',
                'primary_roles.slug as role_slug'
            )
            ->first();

        if ($adminUser) {
            $adminUser->assigned_role_ids = $this->assignedAdminRoleIds($adminUser->id, $adminUser->system_role_id);
            $adminUser->assigned_role_slugs = $this->assignedRoleSlugs($adminUser->id, $adminUser->system_role_id);
        }

        return $adminUser;
    }

    private function adminRoles()
    {
        $query = DB::table('roles')->select('id', 'name', 'slug', 'status');

        if (Schema::hasColumn('roles', 'status')) {
            $query->where('status', 1);
        }

        return $query
            ->whereIn('slug', self::ADMIN_ROLE_SLUGS)
            ->orderBy('name')
            ->get();
    }

    private function adminRoleIds(): array
    {
        return DB::table('roles')
            ->whereIn('slug', self::ADMIN_ROLE_SLUGS)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function selectedAdminRoleIds(StoreAdminUserRequest $request, $adminUserId = null): array
    {
        $roleIds = $request->input('role_ids', []);

        if (! is_array($roleIds)) {
            $roleIds = [$roleIds];
        }

        if ($request->filled('role_id')) {
            $roleIds[] = $request->role_id;
        }

        $roleIds = collect($roleIds)
            ->filter()
            ->map(fn ($roleId) => (int) $roleId)
            ->unique()
            ->values()
            ->all();

        $query = DB::table('roles')
            ->whereIn('id', $roleIds)
            ->whereIn('slug', self::ADMIN_ROLE_SLUGS);

        if (Schema::hasColumn('roles', 'status')) {
            $query->where('status', 1);
        }

        $selectedRoleIds = $query->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($selectedRoleIds)) {
            if ($adminUserId && Schema::hasTable('employees_new') && DB::table('employees_new')->where('user_id', $adminUserId)->exists()) {
                $employeeRoleId = DB::table('roles')->where('slug', 'employee')->value('id');
                if ($employeeRoleId) {
                    return [(int) $employeeRoleId];
                }
            }

            throw \Illuminate\Validation\ValidationException::withMessages([
                'role_ids' => 'Select at least one active admin role.',
            ]);
        }

        return $selectedRoleIds;
    }

    private function syncAdminRoles(int $userId, array $selectedRoleIds): void
    {
        if (! Schema::hasTable('user_roles')) {
            return;
        }

        $adminRoleIds = $this->adminRoleIds();

        if (! empty($adminRoleIds)) {
            DB::table('user_roles')
                ->where('user_id', $userId)
                ->whereIn('role_id', $adminRoleIds)
                ->delete();
        }

        $this->ensureEmployeePivotRole($userId);

        foreach (array_unique($selectedRoleIds) as $roleId) {
            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $userId, 'role_id' => $roleId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        if (class_exists(\App\Services\AccessControl\SidebarS::class)) {
            app(\App\Services\AccessControl\SidebarS::class)->clearCache($userId);
        }
    }

    private function ensureEmployeePivotRole(int $userId): void
    {
        if (! Schema::hasTable('employees_new') || ! Schema::hasTable('user_roles')) {
            return;
        }

        if (! DB::table('employees_new')->where('user_id', $userId)->exists()) {
            return;
        }

        $employeeRoleId = DB::table('roles')->where('slug', 'employee')->value('id');

        if (! $employeeRoleId) {
            return;
        }

        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $userId, 'role_id' => (int) $employeeRoleId],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    private function assignedAdminRoleIds(int $userId, $systemRoleId = null): array
    {
        $roleIds = [];

        if ($systemRoleId) {
            $roleIds[] = (int) $systemRoleId;
        }

        if (Schema::hasTable('user_roles')) {
            $roleIds = array_merge(
                $roleIds,
                DB::table('user_roles')
                    ->where('user_id', $userId)
                    ->pluck('role_id')
                    ->map(fn ($id) => (int) $id)
                    ->all()
            );
        }

        return DB::table('roles')
            ->whereIn('id', array_unique($roleIds))
            ->whereIn('slug', self::ADMIN_ROLE_SLUGS)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function assignedRoleSlugs(int $userId, $systemRoleId = null): array
    {
        $roleIds = [];

        if ($systemRoleId) {
            $roleIds[] = (int) $systemRoleId;
        }

        if (Schema::hasTable('user_roles')) {
            $roleIds = array_merge(
                $roleIds,
                DB::table('user_roles')
                    ->where('user_id', $userId)
                    ->pluck('role_id')
                    ->map(fn ($id) => (int) $id)
                    ->all()
            );
        }

        return DB::table('roles')
            ->whereIn('id', array_unique($roleIds))
            ->pluck('slug')
            ->filter()
            ->values()
            ->all();
    }

    private function attachRoleSummaries($users): void
    {
        $collection = $users->getCollection();
        $userIds = $collection->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (empty($userIds)) {
            return;
        }

        $pivotRoles = collect();

        if (Schema::hasTable('user_roles')) {
            $pivotRoles = DB::table('user_roles')
                ->join('roles', 'roles.id', '=', 'user_roles.role_id')
                ->whereIn('user_roles.user_id', $userIds)
                ->select('user_roles.user_id', 'roles.id', 'roles.name', 'roles.slug')
                ->get()
                ->groupBy('user_id');
        }

        $users->setCollection($collection->map(function ($user) use ($pivotRoles) {
            $roleNames = collect();
            $roleSlugs = collect();

            if ($user->primary_role_name) {
                $roleNames->push($user->primary_role_name);
            }

            if ($user->primary_role_slug) {
                $roleSlugs->push($user->primary_role_slug);
            }

            foreach ($pivotRoles->get($user->id, collect()) as $role) {
                $roleNames->push($role->name);
                $roleSlugs->push($role->slug);
            }

            $user->role_name = $roleNames->unique()->implode(', ') ?: '-';
            $user->role_slug = $roleSlugs->first();
            $user->role_slugs = $roleSlugs->unique()->values()->all();

            return $user;
        }));
    }
}
