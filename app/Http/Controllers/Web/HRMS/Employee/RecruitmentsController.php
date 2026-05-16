<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;

use App\Http\Requests\Web\HRMS\Employee\StoreRecruitmentRequest;
use App\Models\Core\LogM as Log;
use App\Models\HRMS\Employee\PositionM as Position;
use App\Models\HRMS\Employee\RecruitmentM as Recruitment;
use Illuminate\Http\Request;

class RecruitmentsController extends Controller
{
    private $recruitments;

    public function __construct()
    {
        $this->middleware('auth');  
        
        $this->recruitments = resolve(Recruitment::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $recruitments = $this->recruitments->paginate();
        return view('hrms.employee.recruitments.index', compact('recruitments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $positions = Position::where('open_for_recruitment', 1)->latest()->get();
        return view('hrms.employee.recruitments.create', compact('positions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRecruitmentRequest $request)
    {
        $createArray = [
            'position_id' => $request->input('position_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ];

        if($request->has('attachment') && $request->attachment !== null) {
            $createArray["attachment"] = $request->file('attachment')->store('attachments', 'public');
        }

        $this->recruitments->create($createArray);

        Log::create([
            'description' => (optional(auth()->user()->employee)->name ?? auth()->user()->name) . " created a recruitment titled '" . $request->input('title') . "'"
        ]);

        return redirect()->route('hrms.recruitments.index')->with('status', 'Successfully created a recruitment.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HRMS\Employee\RecruitmentM  $recruitment
     * @return \Illuminate\Http\Response
     */
    public function show(Recruitment $recruitment)
    {
        $recruitmentCandidates = $recruitment->recruitmentCanditate()->paginate(10);
        return view('hrms.employee.recruitments.show', compact('recruitment', 'recruitmentCandidates'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HRMS\Employee\RecruitmentM  $recruitment
     * @return \Illuminate\Http\Response
     */
    public function edit(Recruitment $recruitment)
    {
        $positions = Position::where('open_for_recruitment', 1)->latest()->get();
        return view('hrms.employee.recruitments.edit', compact('recruitment', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HRMS\Employee\RecruitmentM  $recruitment
     * @return \Illuminate\Http\Response
     */
    public function update(StoreRecruitmentRequest $request, Recruitment $recruitment)
    {
        $updateArray = [
            'position_id' => $request->input('position_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'is_active' => $request->input('is_active')
        ];

        if($request->has('attachment') && $request->attachment !== null) {
            $updateArray["attachment"] = $request->file('attachment')->store('attachments', 'public');
        }

        $this->recruitments->whereId($recruitment->id)->update($updateArray);

        Log::create([
            'description' => (optional(auth()->user()->employee)->name ?? auth()->user()->name) . " updated a recruitment's detail titled '" . $recruitment->title . "'"
        ]);

        return redirect()->route('hrms.recruitments.index')->with('status', 'Successfully updated recruitment.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HRMS\Employee\RecruitmentM  $recruitment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Recruitment $recruitment)
    {
        $this->recruitments->whereId($recruitment->id)->delete();

        Log::create([
            'description' => (optional(auth()->user()->employee)->name ?? auth()->user()->name) . " deleted a recruitment titled '" . $recruitment->title . "'"
        ]);

        return redirect()->route('hrms.recruitments.index')->with('status', 'Successfully deleted recruitment.');
    }

    public function print () 
    {
        $recruitments = $this->recruitments->all();
        return view('hrms.employee.recruitments.print', compact('recruitments'));
    }
}
