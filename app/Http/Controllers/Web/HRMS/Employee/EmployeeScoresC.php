<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\HRMS\Employee\StoreEmployeeScoreRequest;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Employee\EmployeeScoreM as EmployeeScore;
use App\Models\Core\LogM as Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployeeScoresC extends Controller
{
    private $employeeScores;

    public function __construct()
    {
        $this->middleware('auth');  
        
        $this->employeeScores = resolve(EmployeeScore::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $params = $request->only(['search', 'date_from', 'date_to']);
        
        $employeeScores = $this->employeeScores->getSimplifiedScores($params);
        $accesses = \App\Models\Core\AccessM::where('role_id', $user->role_id)->get();
        
        return view('hrms.employee.performance_scores.index', compact('employeeScores', 'accesses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = auth()->user();
        $data = $this->employeeScores->getDataToCreate();

        $employees = $data["employees"]->filter(function($employee) use ($user) {
            return $employee->id !== ($user->employee ? $user->employee->id : null);
        });
        
        $scoreCategories = $data["scoreCategories"];
        $accesses = \App\Models\Core\AccessM::where('role_id', $user->role_id)->get();

        return view('hrms.employee.performance_scores.create', compact('employees', 'scoreCategories', 'accesses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmployeeScoreRequest $request)
    {
        if($this->employeeScores->where('employee_id', $request->input('employee_id'))->whereBetween('created_at', [Carbon::today()->subMonth(), Carbon::tomorrow()])->exists()) {
            return redirect()->route('hrms.employees.performance_scores.index')->with('status', 'You can only score same employee for once in a month.');
        }        
        
        $group_id = Str::uuid();

        foreach($request->categoryAndScore as $cns) {
            EmployeeScore::create([
                'group_id' => $group_id,
                'employee_id' => $request->input('employee_id'),
                'score_category_id' => $cns["id"],
                'score' => $cns["score"],
                'scored_by' => auth()->user()->employee->id
            ]);
        }

        $employeeName = Employee::whereId($request->input('employee_id'))->first()->name;

        Log::create([
            'description' => auth()->user()->employee->name . " created performance scores for employee named '" . $employeeName . "'"
        ]);

        return redirect()->route('hrms.employees.performance_scores.index')->with('status', "Successfully added an employee's score");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HRMS\Employee\EmployeeScoreM  $employeeScore
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $group_id)
    {
        $user = auth()->user();
        $employeeScore = EmployeeScore::where('group_id', $group_id)->firstOrFail();
        $scores = $this->employeeScores->getEmployeeScoreDetail($group_id);
        $accesses = \App\Models\Core\AccessM::where('role_id', $user->role_id)->get();

        return view('hrms.employee.performance_scores.show', compact('employeeScore', 'scores', 'accesses'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HRMS\Employee\EmployeeScoreM  $employeeScore
     * @return \Illuminate\Http\Response
     */
    public function edit($group_id)
    {
        $user = auth()->user();
        $employeeScore = EmployeeScore::where('group_id', $group_id)->firstOrFail();
        $data = $this->employeeScores->getDataToCreate();

        $employees = $data["employees"]->filter(function($employee) use ($user) {
            return $employee->id !== ($user->employee ? $user->employee->id : null);
        });
        
        $scoreCategories = $data["scoreCategories"];
        $scores = $this->employeeScores->getEmployeeScoreDetail($group_id);
        $accesses = \App\Models\Core\AccessM::where('role_id', $user->role_id)->get();

        return view('hrms.employee.performance_scores.edit', compact('employees', 'scoreCategories', 'scores', 'employeeScore', 'accesses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HRMS\Employee\EmployeeScoreM  $employeeScore
     * @return \Illuminate\Http\Response
     */
    public function update(StoreEmployeeScoreRequest $request, EmployeeScore $employeeScore)
    {
        foreach($request->categoryAndScore as $cns) {
            EmployeeScore::where([
                    ['group_id','=',$employeeScore->group_id],
                    ['score_category_id','=',$cns["id"]],
                    ['employee_id', '=', $request->input('employee_id')],
                    ['scored_by', '=', auth()->user()->employee->id],
                ])
                ->update([
                'score' => $cns["score"]
            ]);
        }

        $employeeName = Employee::whereId($request->input('employee_id'))->first()->name;

        Log::create([
            'description' => auth()->user()->employee->name . " updated performance scores for employee named '" . $employeeName . "'"
        ]);

        return redirect()->route('hrms.employees.performance_scores.index')->with('status', "Successfully updated employee's score");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HRMS\Employee\EmployeeScoreM  $employeeScore
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployeeScore $employeeScore)
    {
        EmployeeScore::where('group_id', $employeeScore->group_id)->delete();

        $employeeName = Employee::whereId($employeeScore->employee_id)->first()->name;

        Log::create([
            'description' => auth()->user()->employee->name . " deleted performance scores for employee named '" . $employeeName . "'"
        ]);

        return redirect()->route('hrms.employees.performance_scores.index')->with('status', "Successfully deleted employee's score");
    }

    public function print () {
        $employeeScores = EmployeeScore::latest()->groupBy('group_id')->get();

        foreach ($employeeScores as $score) {
            $scoreDetail = $score->getEmployeeScoreDetail($score->group_id);

            $total = 0;
            foreach($scoreDetail as $scr) {
                $total += $scr->score;
            }

            $total /= count($scoreDetail);

            $score["score"] = $total;
        }

        return view('hrms.employee.performance_scores.print', compact('employeeScores'));
    }
}
