<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\HRMS\Organization\StoreDepartmentRequest;
use App\Models\HRMS\Department\DepartmentM as Department;
use App\Models\Core\LogM as Log;
use Illuminate\Http\Request;

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
     * @return \Illuminate\Http\Response
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
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('hrms.employee.departments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDepartmentRequest $request)
    {
        Department::create($request->validated());

        Log::create([
            'description' => auth()->user()->employee->name . " created an department named '" . $request->input('name') . "'"
        ]);

        return redirect()->route('hrms.departments.index')->with('status', 'Successfully created a department.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HRMS\Department\DepartmentM  $department
     * @return \Illuminate\Http\Response
     */
    public function show(Department $department)
    {
        return view('hrms.employee.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HRMS\Department\DepartmentM  $department
     * @return \Illuminate\Http\Response
     */
    public function edit(Department $department)
    {
        return view('hrms.employee.departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HRMS\Department\DepartmentM  $department
     * @return \Illuminate\Http\Response
     */
    public function update(StoreDepartmentRequest $request, Department $department)
    {
        Department::where('id', $department->id)->update($request->validated());

        Log::create([
            'description' => auth()->user()->employee->name . " updated an department named '" . $department->name . "'"
        ]);

        return redirect()->route('hrms.departments.index')->with('status', 'Successfully updated department.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HRMS\Department\DepartmentM  $department
     * @return \Illuminate\Http\Response
     */
    public function destroy(Department $department)
    {
        Department::where('id', $department->id)->delete();
        
        Log::create([
            'description' => auth()->user()->employee->name . " deleted an department named '" . $department->name . "'"
        ]);

        return redirect()->route('hrms.departments.index')->with('status', 'Successfully deleted department.');
    }

    public function print() {
        $departments = Department::all();
        return view('hrms.employee.departments.print', compact('departments'));
    }
}
