<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;

use App\Http\Requests\Web\AccessControl\StoreRoleRequest;
use App\Models\Core\AccessM as Access;
use App\Models\Core\AdminM as Admin;
use App\Models\Core\LogM as Log;
use App\Models\Core\MenuM as Menu;
use App\Models\Core\RoleM as Role;
use Illuminate\Http\Request;

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
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = $this->roles->paginate();
        return view('settings.roles', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menus = Menu::all();
        return view('settings.roles_create', compact('menus'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRoleRequest $request)
    {
        $roleId = Role::create(['name' => $request->input('name')])->id;

        if($request->input('is_super_user') == "1") {
            Admin::create([
                'role_id' => $roleId
            ]);
        }

        foreach($request->menuAndAccessLevel as $mna) {
            $key = key($mna);
            Access::create([
                'role_id' => $roleId,
                'menu_id' => $key,
                'status' => $mna[$key]
            ]);
        }

        Log::create([
            'description' => auth()->user()->employee->name . " created a role named '" . $request->input('name') . "'"
        ]);

        return redirect()->route('roles.index')->with('status', 'Successfully created a role.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Core\RoleM  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        $accessesForEditing = Access::where('role_id', $role->id)->with('menu', 'role')->orderBy('menu_id', 'ASC')->get();
        return view('settings.roles_show', compact('accessesForEditing', 'role'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Core\RoleM  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $accessesForEditing = Access::where('role_id', $role->id)->with('menu', 'role')->orderBy('menu_id', 'ASC')->get();

        return view('settings.roles_edit', compact('accessesForEditing', 'role'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Core\RoleM  $role
     * @return \Illuminate\Http\Response
     */
    public function update(StoreRoleRequest $request, Role $role)
    {
        $role->update([
                'name' => $request->input('name'),
                'is_super_user' => $request->input('is_super_user'),
            ]);

        if($request->input('is_super_user') == "0") {
            Admin::whereRoleId($role->id)->delete();
        }

        foreach($request->menuAndAccessLevel as $mna) {
            $key = key($mna);
            Access::where([
                ['role_id', '=', $role->id],
                ['menu_id', '=', $key],
            ])->update([
                'status' => $mna[$key]
            ]);
        }

        Log::create([
            'description' => auth()->user()->employee->name . " updated a role's detail named '" . $role->name . "'"
        ]);

        return redirect()->route('roles.index')->with('status', 'Successfully updated role.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Core\RoleM  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        $this->roles->where('id', $role->id)->delete();

        Log::create([
            'description' => auth()->user()->employee->name . " deleted a role named '" . $role->name . "'"
        ]);

        return redirect()->route('roles.index')->with('status', 'Successfully deleted role.');
    }

    public function print ()
    {
        $roles = $this->roles->all();
        return view('settings.roles_print', compact('roles'));
    }
}
