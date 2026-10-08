<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\HRMS\Organization\StoreDepartmentRequest;
use App\Models\HRMS\Department\DepartmentM as Department;
use App\Models\Core\LogM as Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use App\Models\Core\UserM;
use App\Models\HRMS\Department\DepartmentM;

class DepartmentsC extends Controller
{
    private $departments;

    public function __construct()
    {
        $this->middleware('auth');  
        
        $this->departments = resolve(Department::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Department::withCount('employees')
            ->with(['employees' => function($query) {
                $query->take(5);
            }, 'employees.user']);

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
        }

        $departments = $query->latest()->paginate();
        
        return view('hrms.employee.departments.index', compact('departments', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('hrms.employee.departments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(StoreDepartmentRequest $request)
    {
        Department::create($request->validated());

        /** @var UserM|null $user */
        $user = Auth::user();
        $actorName = $user?->employee?->name ?? $user?->name ?? 'User';

        Log::create([
            'description' => $actorName . " created an department named '" . $request->input('name') . "'"
        ]);

        return redirect()->route('hrms.departments.index')->with('status', 'Successfully created a department.');
    }

    /**
     * Display the specified resource.
     *
     * @param  DepartmentM  $department
     * @return Response
     */
    public function show(Department $department)
    {
        return view('hrms.employee.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  DepartmentM  $department
     * @return Response
     */
    public function edit(Department $department)
    {
        return view('hrms.employee.departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  DepartmentM  $department
     * @return Response
     */
    public function update(StoreDepartmentRequest $request, Department $department)
    {
        Department::where('id', $department->id)->update($request->validated());

        /** @var UserM|null $user */
        $user = Auth::user();
        $actorName = $user?->employee?->name ?? $user?->name ?? 'User';

        Log::create([
            'description' => $actorName . " updated an department named '" . $department->name . "'"
        ]);

        return redirect()->route('hrms.departments.index')->with('status', 'Successfully updated department.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  DepartmentM  $department
     * @return Response
     */
    public function destroy(Department $department)
    {
        Department::where('id', $department->id)->delete();
        
        /** @var UserM|null $user */
        $user = Auth::user();
        $actorName = $user?->employee?->name ?? $user?->name ?? 'User';

        Log::create([
            'description' => $actorName . " deleted an department named '" . $department->name . "'"
        ]);

        return redirect()->route('hrms.departments.index')->with('status', 'Successfully deleted department.');
    }

    public function print() {
        $departments = Department::all();
        return view('hrms.employee.departments.print', compact('departments'));
    }
}
