<?php

namespace App\Http\Controllers\Web\HRMS\Announcement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\HRMS\Announcement\StoreAnnouncementRequest;
use App\Models\HRMS\Announcement\AnnouncementM as Announcement;
use App\Models\Core\LogM as Log;
use App\Services\HRMS\Announcement\AnnouncementS;

class AnnouncementsC extends Controller
{
    private AnnouncementS $announcementService;

    public function __construct(AnnouncementS $announcementService)
    {
        $this->middleware('auth');  
        $this->announcementService = $announcementService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $announcements = $this->announcementService->paginate();
        return view('hrms.announcement.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = $this->announcementService->departments();
        return view('hrms.announcement.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAnnouncementRequest $request)
    {
        $createArray = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'department_id' => $request->input('department_id'),
            'created_by' => auth()->user()->employee->id,
        ];

        if($request->has('attachment') && $request->file('attachment') !== null) {
            $createArray['attachment'] = $this->announcementService->storeAttachment($request->file('attachment'));
        }

        $this->announcementService->create($createArray);

        Log::create([
            'description' => auth()->user()->employee->name . " created an announcement titled '" . $request->input('title') . "'"
        ]);

        return redirect()->route('announcements')->with('status', 'Successfully created an announcement.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HRMS\Announcement\AnnouncementM  $announcement
     * @return \Illuminate\Http\Response
     */
    public function show(Announcement $announcement)
    {
        return view('hrms.announcement.show', compact('announcement'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HRMS\Announcement\AnnouncementM  $announcement
     * @return \Illuminate\Http\Response
     */
    public function edit(Announcement $announcement)
    {
        $departments = $this->announcementService->departments();
        return view('hrms.announcement.edit', compact('announcement', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HRMS\Announcement\AnnouncementM  $announcement
     * @return \Illuminate\Http\Response
     */
    public function update(StoreAnnouncementRequest $request, Announcement $announcement)
    {
        $updateArray = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'department_id' => $request->input('department_id'),
        ];

        if($request->has('attachment') && $request->file('attachment') !== null) {
            $updateArray['attachment'] = $this->announcementService->storeAttachment($request->file('attachment'));
        }

        $this->announcementService->updateOwned(
            (int) $announcement->id,
            (int) auth()->user()->employee->id,
            $updateArray
        );

        Log::create([
            'description' => auth()->user()->employee->name . " updated an announcement titled '" . $announcement->title . "'"
        ]);

        return redirect()->route('announcements')->with('status', 'Successfully updated announcement.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HRMS\Announcement\AnnouncementM  $announcement
     * @return \Illuminate\Http\Response
     */
    public function destroy(Announcement $announcement)
    {
        $this->announcementService->deleteOwned(
            (int) $announcement->id,
            (int) auth()->user()->employee->id
        );

        Log::create([
            'description' => auth()->user()->employee->name . " deleted an announcement titled '" . $announcement->title . "'"
        ]);

        return redirect()->route('announcements')->with('status', 'Successfully deleted announcement.');
    }

    public function print() 
    {
        $announcements = Announcement::all();
        return view('hrms.announcement.print', compact('announcements'));
    }
}
