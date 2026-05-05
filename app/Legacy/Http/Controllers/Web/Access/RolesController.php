<?php

namespace App\Legacy\Http\Controllers\Web\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AccessControl\StoreRoleRequest;
use App\Models\Access;
use App\Models\Log;
use App\Models\Menu;
use App\Models\Core\RoleM as Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolesController extends Controller
{
    private $roles;

    public function __construct()
    {
        $this->middleware('auth');
        $this->roles = resolve(Role::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = $this->roles->paginate();
        return view('settings.roles', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $menus = Menu::all();
        return view('settings.roles_create', compact('menus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        DB::beginTransaction();

        try {
            $name = trim($request->input('name'));
            $code = $request->filled('code')
                ? Str::slug($request->input('code'), '_')
                : Str::slug($name, '_');

            // duplicate code avoid
            $originalCode = $code;
            $counter = 1;

            while (Role::where('code', $code)->exists()) {
                $code = $originalCode . '_' . $counter;
                $counter++;
            }

            $role = Role::create([
                'name'           => $name,
                'code'           => $code,
                'description'    => $request->input('description'),
                'is_system'      => $request->boolean('is_system'),
                'is_active'      => $request->has('is_active') ? $request->boolean('is_active') : true,
                'is_super_admin' => $request->boolean('is_super_admin'),
            ]);

            if ($request->filled('menuAndAccessLevel') && is_array($request->menuAndAccessLevel)) {
                foreach ($request->menuAndAccessLevel as $mna) {
                    $key = key($mna);

                    if ($key !== null) {
                        Access::create([
                            'role_id' => $role->id,
                            'menu_id' => $key,
                            'status'  => $mna[$key],
                        ]);
                    }
                }
            }

            Log::create([
                'description' => $this->actorName() . " created a role named '{$role->name}'",
            ]);

            DB::commit();

            return redirect()->route('roles')->with('status', 'Successfully created a role.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create role. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $accessesForEditing = Access::where('role_id', $role->id)
            ->with('menu', 'role')
            ->orderBy('menu_id', 'ASC')
            ->get();

        return view('settings.roles_show', compact('accessesForEditing', 'role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $accessesForEditing = Access::where('role_id', $role->id)
            ->with('menu', 'role')
            ->orderBy('menu_id', 'ASC')
            ->get();

        return view('settings.roles_edit', compact('accessesForEditing', 'role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRoleRequest $request, Role $role)
    {
        DB::beginTransaction();

        try {
            $name = trim($request->input('name'));
            $code = $request->filled('code')
                ? Str::slug($request->input('code'), '_')
                : Str::slug($name, '_');

            $originalCode = $code;
            $counter = 1;

            while (
                Role::where('code', $code)
                    ->where('id', '!=', $role->id)
                    ->exists()
            ) {
                $code = $originalCode . '_' . $counter;
                $counter++;
            }

            $role->update([
                'name'           => $name,
                'code'           => $code,
                'description'    => $request->input('description'),
                'is_system'      => $request->boolean('is_system'),
                'is_active'      => $request->has('is_active') ? $request->boolean('is_active') : false,
                'is_super_admin' => $request->boolean('is_super_admin'),
            ]);

            if ($request->filled('menuAndAccessLevel') && is_array($request->menuAndAccessLevel)) {
                foreach ($request->menuAndAccessLevel as $mna) {
                    $key = key($mna);

                    if ($key !== null) {
                        $access = Access::where([
                            ['role_id', '=', $role->id],
                            ['menu_id', '=', $key],
                        ])->first();

                        if ($access) {
                            $access->update([
                                'status' => $mna[$key],
                            ]);
                        } else {
                            Access::create([
                                'role_id' => $role->id,
                                'menu_id' => $key,
                                'status'  => $mna[$key],
                            ]);
                        }
                    }
                }
            }

            Log::create([
                'description' => $this->actorName() . " updated role details named '{$role->name}'",
            ]);

            DB::commit();

            return redirect()->route('roles')->with('status', 'Successfully updated role.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update role. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        DB::beginTransaction();

        try {
            if ((bool) $role->is_system) {
                return redirect()->route('roles')->with('error', 'System roles cannot be deleted.');
            }

            Access::where('role_id', $role->id)->delete();
            $roleName = $role->name;
            $role->delete();

            Log::create([
                'description' => $this->actorName() . " deleted a role named '{$roleName}'",
            ]);

            DB::commit();

            return redirect()->route('roles')->with('status', 'Successfully deleted role.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('roles')->with('error', 'Failed to delete role. ' . $e->getMessage());
        }
    }

    public function print()
    {
        $roles = $this->roles->all();
        return view('settings.roles_print', compact('roles'));
    }

    private function actorName(): string
    {
        $user = auth()->user();

        return $user->employee->name
            ?? $user->name
            ?? 'System User';
    }
}
