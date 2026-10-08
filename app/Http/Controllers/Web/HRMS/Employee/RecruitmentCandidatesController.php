<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;

use App\Http\Requests\Web\HRMS\Employee\StoreRecruitmentCandidateRequest;
use App\Models\Core\LogM as Log;
use App\Models\HRMS\Employee\RecruitmentM as Recruitment;
use App\Models\HRMS\Employee\RecruitmentCandidateM as RecruitmentCandidate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\HRMS\Employee\RecruitmentCandidateM;

class RecruitmentCandidatesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(StoreRecruitmentCandidateRequest $request)
    {
        RecruitmentCandidate::create([
            'recruitment_id' => $request->input('recruitment_id'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'message' => $request->input('message'),
            'address' => $request->input('address'),
            'photo' => $request->file('photo')->store('photos', 'public'),
            'cv' => $request->file('cv')->store('cvs', 'public')
        ]);

        $name = Recruitment::whereId($request->input('recruitment_id'))->first()->position->name;

        Log::create([
            'description' => $request->input('name') . " applied for position named '" . $name . "'"
        ]);

        return back()->with('status', 'Successfully apply for this job.');
    }

    /**
     * Display the specified resource.
     *
     * @param  RecruitmentCandidateM  $recruitmentCandidate
     * @return Response
     */
    public function show(RecruitmentCandidate $recruitmentCandidate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  RecruitmentCandidateM  $recruitmentCandidate
     * @return Response
     */
    public function edit(RecruitmentCandidate $recruitmentCandidate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  RecruitmentCandidateM  $recruitmentCandidate
     * @return Response
     */
    public function update(Request $request, RecruitmentCandidate $recruitmentCandidate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  RecruitmentCandidateM  $recruitmentCandidate
     * @return Response
     */
    public function destroy(RecruitmentCandidate $recruitmentCandidate)
    {
        //
    }
}
