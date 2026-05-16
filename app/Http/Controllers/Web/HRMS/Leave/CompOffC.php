<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\AccessM;
use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use App\Models\HRMS\Leave\CompOffM;
use App\Services\HRMS\Leave\CompOffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompOffC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private CompOffService $compOffService)
    {
    }

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('leave.comp_off.view_all')
            || $this->userHasPermission('leave.comp_off.view_own')
            || $this->userHasPermission('leave.comp_off.view')
            || $this->userHasPermission('leave.comp_off.manage'),
            403
        );

        $compOffs = CompOffM::with('employee.user')
            ->when($request->status, fn ($query) => $query->where('status', $request->status));

        if (! $this->canViewAll('leave.comp_off.view_all') && ! $this->userHasPermission('leave.comp_off.manage')) {
            $employeeId = $this->ownEmployeeId();
            abort_if(! $employeeId, 403);
            $compOffs->where('employee_id', $employeeId);
        }

        $compOffs = $compOffs->latest()->paginate(30);
        $holidayWorkRequests = $this->userHasPermission('leave.comp_off.manage')
            ? HolidayWorkRequestM::with('employee.user')->where('status', 'pending')->latest()->get()
            : collect();
        $accesses = $this->accesses();

        return view('hrms.leave.comp_offs.index', compact('compOffs', 'holidayWorkRequests', 'accesses'))->with('active', 'leave_management');
    }

    public function approveHolidayWork($id)
    {
        abort_unless($this->userHasPermission('leave.comp_off.manage'), 403);

        $request = HolidayWorkRequestM::with('employee')->findOrFail($id);
        $this->compOffService->generateFromHolidayWork($request, Auth::id());

        return back()->with('success', 'Comp off generated from holiday/weekoff work.');
    }

    public function expire()
    {
        abort_unless($this->userHasPermission('leave.comp_off.manage'), 403);

        $count = $this->compOffService->expireDue();

        return back()->with('success', "{$count} comp off record(s) expired.");
    }

}
