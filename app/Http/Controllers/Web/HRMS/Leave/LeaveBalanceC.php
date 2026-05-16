<?php

namespace App\Http\Controllers\Web\HRMS\Leave;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\AccessM;
use App\Models\HRMS\Leave\LeaveAllocationM;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveBalanceC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('leave.balance.view_all')
            || $this->userHasPermission('leave.balance.view_team')
            || $this->userHasPermission('leave.balance.view_own')
            || $this->userHasPermission('leave.balance.view'),
            403
        );

        $year = (int) ($request->year ?: Carbon::now('Asia/Kolkata')->year);
        $balances = LeaveAllocationM::with(['employee.user', 'policy'])
            ->where('year', $year);

        $this->scopeEmployeeVisibility($balances, 'leave.balance.view_all', 'leave.balance.view_team');

        if (($this->canViewAll('leave.balance.view_all') || $this->canViewTeam('leave.balance.view_team')) && $request->employee_id) {
            $balances->where('employee_id', $request->employee_id);
        }

        $balances = $balances->paginate(30);
        $employees = $this->scopedEmployeeOptions('leave.balance.view_all', 'leave.balance.view_team');
        $accesses = $this->accesses();

        return view('hrms.leave.balances.index', compact('balances', 'employees', 'year', 'accesses'))->with('active', 'leave_management');
    }
}
