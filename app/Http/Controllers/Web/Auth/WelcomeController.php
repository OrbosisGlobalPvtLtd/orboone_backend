<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\Core\UserM as User;
use App\Models\HRMS\Announcement\AnnouncementM as Announcement;
use App\Models\HRMS\Employee\RecruitmentM as Recruitment;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    private $announcements;
    private $recruitments;

    public function __construct()
    {
        $this->announcements = resolve(Announcement::class);
        $this->recruitments = resolve(Recruitment::class);
    }

    public function index()
    {
        // Fetch announcements and recruitments (if needed for the view)
        $announcements = Announcement::all();
        $recruitments = Recruitment::all();
        
        return view('auth.login', compact('announcements', 'recruitments'));
    }

    public function edit_user_registration()
    {
        $id = session()->get('vendor_id');
        $data = User::find($id);
        return view('user.auth.edit_user_registration', compact('data'));
    }

    public function login()
    {
        return view('auth.login');
    }

    public function announcements () 
    {
        $announcements = $this->announcements->where('department_id', null)->latest()->paginate(10);
        return view('hrms.announcement.public.index', compact('announcements'));
    }

    public function announcementShow (Announcement $announcement) 
    {
        return view('hrms.announcement.public.show', compact('announcement'));
    }

    public function recruitments () 
    {
        $recruitments = $this->recruitments->where('is_active', 1)->latest()->paginate(10);
        return view('hrms.employee.recruitments.public-index', compact('recruitments'));
    }

    public function recruitmentShow (Recruitment $recruitment) 
    {
        return view('hrms.employee.recruitments.public-show', compact('recruitment'));
    }
}
