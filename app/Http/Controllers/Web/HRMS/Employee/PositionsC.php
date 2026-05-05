<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\HRMS\Employee\StorePositionRequest;
use App\Models\Core\LogM as Log;
use App\Models\HRMS\Employee\PositionM as Position;
use Illuminate\Http\Request;

class PositionsC extends Controller
{
    private $positions;

    public function __construct()
    {
        $this->middleware('auth');  
        
        $this->positions = resolve(Position::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Position::withCount('employees')
            ->with(['employees' => function($query) {
                $query->take(5);
            }, 'employees.user']);

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
        }

        $positions = $query->latest()->paginate();
        
        return view('hrms.employee.positions.index', compact('positions', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    
    public function create()
    {
        return view('hrms.employee.positions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePositionRequest $request)
    {
        Position::create($request->validated());

        Log::create([
            'description' => auth()->user()->employee->name . " created a position named '" . $request->input('name') . "'"
        ]);

        return redirect()->route('hrms.designations.index')->with('status', 'Successfully created a position.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HRMS\Employee\PositionM  $position
     * @return \Illuminate\Http\Response
     */
    public function show(Position $position)
    {
        return view('hrms.employee.positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HRMS\Employee\PositionM  $position
     * @return \Illuminate\Http\Response
     */
    public function edit(Position $position)
    {
        return view('hrms.employee.positions.edit', compact('position'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HRMS\Employee\PositionM  $position
     * @return \Illuminate\Http\Response
     */
    public function update(StorePositionRequest $request, Position $position)
    {
        Position::where('id', $position->id)->update($request->validated());

        Log::create([
            'description' => auth()->user()->employee->name . " updated a position's detail named '" . $position->name . "'"
        ]);

        return redirect()->route('hrms.designations.index')->with('status', 'Successfully updated position.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HRMS\Employee\PositionM  $position
     * @return \Illuminate\Http\Response
     */
    public function destroy(Position $position)
    {
        Position::where('id', $position->id)->delete();

        Log::create([
            'description' => auth()->user()->employee->name . " deleted a position named '" . $position->name . "'"
        ]);

        return redirect()->route('hrms.designations.index')->with('status', 'Successfully deleted a position.');
    }

    public function print() {
        $positions = Position::all();

        return view('hrms.employee.positions.print', compact('positions'));
    }
}
