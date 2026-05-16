<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\HRMS\Attendance\HolidayWorkRequestM;
use App\Services\HRMS\Leave\CompOffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolidayWorkRequestC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $query = $this->employeeJoinedQuery('holiday_work_requests')->whereNull('holiday_work_requests.deleted_at');
        $this->applyCommonFilters($query, $request, [
            'dateColumn' => 'holiday_work_requests.worked_date',
            'filterMap' => ['employee_id' => 'holiday_work_requests.employee_id', 'status' => 'holiday_work_requests.status', 'work_type' => 'holiday_work_requests.work_type'],
        ]);

        return view('hrms.attendance.holiday_work.index', $this->pageData($query->latest('holiday_work_requests.id')->paginate(50)));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['employee_id' => 'required|exists:employees_new,id', 'worked_date' => 'required|date', 'work_type' => 'required|string|max:80', 'reason' => 'nullable|string', 'status' => 'nullable|in:pending,approved,rejected,cancelled']);
        DB::table('holiday_work_requests')->insert(array_merge($data, ['status' => $data['status'] ?? 'pending', 'created_at' => now(), 'updated_at' => now()]));
        return back()->with('success', 'Holiday work request saved.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate(['employee_id' => 'required|exists:employees_new,id', 'worked_date' => 'required|date', 'work_type' => 'required|string|max:80', 'reason' => 'nullable|string', 'status' => 'nullable|in:pending,approved,rejected,cancelled']);
        DB::table('holiday_work_requests')->where('id', $id)->update(array_merge($data, ['updated_at' => now()]));
        return back()->with('success', 'Holiday work request updated.');
    }

    public function approve($id, CompOffService $compOffService)
    {
        $request = HolidayWorkRequestM::with('employee')->findOrFail($id);
        $compOffService->generateFromHolidayWork($request, $this->actorId());
        return back()->with('success', 'Holiday work approved and comp off generated.');
    }

    public function reject($id)
    {
        DB::table('holiday_work_requests')->where('id', $id)->update(['status' => 'rejected', 'approved_by_user_id' => $this->actorId(), 'approved_at' => $this->nowKolkata(), 'updated_at' => now()]);
        return back()->with('success', 'Holiday work rejected.');
    }

    public function destroy($id)
    {
        DB::table('holiday_work_requests')->where('id', $id)->update(['deleted_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Holiday work request deleted.');
    }

    private function pageData($rows): array
    {
        $employees = $this->employeeOptions()->pluck('display_name', 'id')->toArray();
        return [
            'accesses' => $this->accesses(), 'active' => 'attendance', 'pageTitle' => 'Holiday Work Requests',
            'pageSubtitle' => 'Approve holiday/weekoff work and generate comp off credits.',
            'rows' => $rows,
            'columns' => [
                ['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'employee_code', 'label' => 'Code'], ['key' => 'worked_date', 'label' => 'Worked Date', 'type' => 'date'], ['key' => 'work_type', 'label' => 'Work Type'], ['key' => 'comp_off_generated', 'label' => 'Comp Off', 'type' => 'badge'], ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
            ],
            'filters' => [
                ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled']], ['name' => 'work_type', 'label' => 'Work Type', 'type' => 'select', 'options' => ['holiday_work' => 'Holiday Work', 'weekoff_work' => 'Weekoff Work']], ['name' => 'from', 'label' => 'From', 'type' => 'date'], ['name' => 'to', 'label' => 'To', 'type' => 'date'],
            ],
            'formFields' => [
                ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees], ['name' => 'worked_date', 'label' => 'Worked Date', 'type' => 'date'], ['name' => 'work_type', 'label' => 'Work Type', 'type' => 'select', 'options' => ['holiday_work' => 'Holiday Work', 'weekoff_work' => 'Weekoff Work']], ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled']], ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'col' => 12],
            ],
            'canCreate' => true, 'canEdit' => true, 'canDelete' => true,
            'storeRoute' => 'hrms.attendance.holiday_work.store', 'updateRoute' => 'hrms.attendance.holiday_work.update', 'deleteRoute' => 'hrms.attendance.holiday_work.destroy',
            'rowActions' => [['label' => 'Approve', 'route' => 'hrms.attendance.holiday_work.approve', 'icon' => 'fas fa-check', 'confirm' => 'Approve and generate comp off?'], ['label' => 'Reject', 'route' => 'hrms.attendance.holiday_work.reject', 'icon' => 'fas fa-times', 'confirm' => 'Reject this request?']],
        ];
    }
}
