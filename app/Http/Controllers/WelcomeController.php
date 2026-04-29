<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Announcement;
use App\Models\Recruitment;
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
        return view('pages.welcome_announcements', compact('announcements'));
    }

    public function announcementShow (Announcement $announcement) 
    {
        return view('pages.welcome_announcements_show', compact('announcement'));
    }

    public function recruitments () 
    {
        $recruitments = $this->recruitments->where('is_active', 1)->latest()->paginate(10);
        return view('pages.welcome_recruitments', compact('recruitments'));
    }

    public function recruitmentShow (Recruitment $recruitment) 
    {
        return view('pages.welcome_recruitments_show', compact('recruitment'));
    }
}
