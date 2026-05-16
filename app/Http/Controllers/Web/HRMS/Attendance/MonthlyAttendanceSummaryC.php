<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyAttendanceSummaryC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.monthly_summary.view'), 403);

        $query = $this->employeeJoinedQuery('monthly_attendance_summaries');
        $this->applyCommonFilters($query, $request, ['filterMap' => ['employee_id' => 'monthly_attendance_summaries.employee_id', 'month' => 'monthly_attendance_summaries.month', 'year' => 'monthly_attendance_summaries.year', 'locked' => 'monthly_attendance_summaries.is_locked']]);
        $rows = $query->orderByDesc('year')->orderByDesc('month')->paginate(50);
        $employees = $this->employeeOptions()->pluck('display_name', 'id')->toArray();

        return view('hrms.attendance.monthly_summary.index', [
            'accesses' => $this->accesses(), 'active' => 'attendance', 'pageTitle' => 'Monthly Attendance Summary',
            'pageSubtitle' => 'Payroll-ready monthly attendance summaries with lock controls.',
            'rows' => $rows,
            'columns' => [
                ['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'month', 'label' => 'Month'], ['key' => 'year', 'label' => 'Year'], ['key' => 'present_days', 'label' => 'Present'], ['key' => 'paid_leave_days', 'label' => 'Paid Leave'], ['key' => 'lwp_days', 'label' => 'LWP'], ['key' => 'payable_days', 'label' => 'Payable'], ['key' => 'is_locked', 'label' => 'Locked', 'type' => 'badge'],
            ],
            'filters' => [
                ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees], ['name' => 'month', 'label' => 'Month', 'type' => 'select', 'options' => array_combine(range(1, 12), range(1, 12))], ['name' => 'year', 'label' => 'Year', 'type' => 'number'], ['name' => 'locked', 'label' => 'Locked', 'type' => 'select', 'options' => [1 => 'Locked', 0 => 'Unlocked']],
            ],
            'canCreate' => false, 'canEdit' => false,
            'rowActions' => [['label' => 'Lock', 'route' => 'hrms.attendance.monthly_summary.lock', 'icon' => 'fas fa-lock', 'confirm' => 'Lock this summary?'], ['label' => 'Unlock', 'route' => 'hrms.attendance.monthly_summary.unlock', 'icon' => 'fas fa-unlock', 'confirm' => 'Unlock this summary?']],
        ]);
    }

    public function lock($id)
    {
        abort_unless($this->userHasPermission('attendance.monthly_summary.view'), 403);

        DB::table('monthly_attendance_summaries')->where('id', $id)->update(['is_locked' => 1, 'locked_by_user_id' => $this->actorId(), 'locked_at' => $this->nowKolkata(), 'updated_at' => now()]);
        return back()->with('success', 'Summary locked.');
    }

    public function unlock($id)
    {
        abort_unless($this->userHasPermission('attendance.monthly_summary.view'), 403);

        DB::table('monthly_attendance_summaries')->where('id', $id)->update(['is_locked' => 0, 'locked_by_user_id' => null, 'locked_at' => null, 'updated_at' => now()]);
        return back()->with('success', 'Summary unlocked.');
    }
}
