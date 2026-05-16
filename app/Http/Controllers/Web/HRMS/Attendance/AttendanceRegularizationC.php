<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceRegularizationC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('attendance.regularization.view_all')
            || $this->userHasPermission('attendance.regularization.view_team')
            || $this->userHasPermission('attendance.regularization.view_own')
            || $this->userHasPermission('attendance.regularization.view'),
            403
        );

        $query = $this->employeeJoinedQuery('attendance_regularizations')
            ->whereNull('attendance_regularizations.deleted_at');
        $this->scopeEmployeeVisibility($query, 'attendance.regularization.view_all', 'attendance.regularization.view_team', 'attendance_regularizations.employee_id');

        $this->applyCommonFilters($query, $request, [
            'dateColumn' => 'attendance_regularizations.created_at',
            'filterMap' => [
                'employee_id' => 'attendance_regularizations.employee_id',
                'status' => 'attendance_regularizations.status',
                'request_type' => 'attendance_regularizations.request_type',
            ],
        ]);

        return view('hrms.attendance.regularizations.index', $this->pageData($query->latest('attendance_regularizations.id')->paginate(50), $request));
    }

    public function store(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.regularization.create'), 403);

        $data = $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'request_type' => 'required|string|max:80',
            'requested_punch_in' => 'nullable|date',
            'requested_punch_out' => 'nullable|date',
            'reason' => 'nullable|string',
            'status' => 'nullable|in:pending,approved,rejected,cancelled',
        ]);

        if (! $this->canViewAll('attendance.regularization.view_all')) {
            $employeeId = $this->ownEmployeeId();
            abort_if(! $employeeId, 403);
            $data['employee_id'] = $employeeId;
        }

        DB::table('attendance_regularizations')->insert(array_merge($data, [
            'status' => $data['status'] ?? 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return back()->with('success', 'Regularization request saved.');
    }

    public function update(Request $request, $id)
    {
        $this->authorizeRegularizationRow($id, true);

        $data = $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'request_type' => 'required|string|max:80',
            'requested_punch_in' => 'nullable|date',
            'requested_punch_out' => 'nullable|date',
            'reason' => 'nullable|string',
            'status' => 'nullable|in:pending,approved,rejected,cancelled',
        ]);

        DB::table('attendance_regularizations')->where('id', $id)->update(array_merge($data, ['updated_at' => now()]));

        return back()->with('success', 'Regularization request updated.');
    }

    public function approve($id)
    {
        abort_unless($this->userHasPermission('attendance.regularization.approve'), 403);
        $this->authorizeRegularizationRow($id, false);

        DB::table('attendance_regularizations')->where('id', $id)->update([
            'status' => 'approved',
            'approved_by_user_id' => $this->actorId(),
            'approved_at' => $this->nowKolkata(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Regularization approved.');
    }

    public function reject($id)
    {
        abort_unless($this->userHasPermission('attendance.regularization.reject'), 403);
        $this->authorizeRegularizationRow($id, false);

        DB::table('attendance_regularizations')->where('id', $id)->update([
            'status' => 'rejected',
            'approved_by_user_id' => $this->actorId(),
            'approved_at' => $this->nowKolkata(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Regularization rejected.');
    }

    public function destroy($id)
    {
        $this->authorizeRegularizationRow($id, true);

        DB::table('attendance_regularizations')->where('id', $id)->update(['deleted_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'Regularization deleted.');
    }

    private function pageData($rows, Request $request): array
    {
        $employees = $this->scopedEmployeeOptions('attendance.regularization.view_all', 'attendance.regularization.view_team')->pluck('display_name', 'id')->toArray();
        $requestTypes = DB::table('attendance_regularizations')->whereNull('deleted_at')->whereNotNull('request_type')->distinct()->pluck('request_type', 'request_type')->toArray();

        return [
            'accesses' => $this->accesses(),
            'active' => 'attendance',
            'pageTitle' => 'Attendance Regularizations',
            'pageSubtitle' => 'Review, create, approve, and reject attendance correction requests.',
            'rows' => $rows,
            'columns' => [
                ['key' => 'employee_display_name', 'label' => 'Employee'],
                ['key' => 'employee_code', 'label' => 'Code'],
                ['key' => 'request_type', 'label' => 'Type'],
                ['key' => 'requested_punch_in', 'label' => 'Punch In', 'type' => 'datetime'],
                ['key' => 'requested_punch_out', 'label' => 'Punch Out', 'type' => 'datetime'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
            ],
            'filters' => [
                ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled']],
                ['name' => 'request_type', 'label' => 'Request Type', 'type' => 'select', 'options' => $requestTypes],
                ['name' => 'from', 'label' => 'From', 'type' => 'date'],
                ['name' => 'to', 'label' => 'To', 'type' => 'date'],
            ],
            'formFields' => [
                ['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $employees],
                ['name' => 'request_type', 'label' => 'Request Type'],
                ['name' => 'requested_punch_in', 'label' => 'Requested Punch In', 'type' => 'datetime-local'],
                ['name' => 'requested_punch_out', 'label' => 'Requested Punch Out', 'type' => 'datetime-local'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled']],
                ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'col' => 12],
            ],
            'canCreate' => true,
            'canEdit' => true,
            'canDelete' => true,
            'storeRoute' => 'hrms.attendance.regularizations.store',
            'updateRoute' => 'hrms.attendance.regularizations.update',
            'deleteRoute' => 'hrms.attendance.regularizations.destroy',
            'rowActions' => [
                ['label' => 'Approve', 'route' => 'hrms.attendance.regularizations.approve', 'icon' => 'fas fa-check', 'confirm' => 'Approve this request?'],
                ['label' => 'Reject', 'route' => 'hrms.attendance.regularizations.reject', 'icon' => 'fas fa-times', 'confirm' => 'Reject this request?'],
            ],
        ];
    }

    private function authorizeRegularizationRow($id, bool $allowOwn): void
    {
        $row = DB::table('attendance_regularizations')->where('id', $id)->first();
        abort_if(! $row, 404);

        if ($this->canViewAll('attendance.regularization.view_all')) {
            return;
        }

        if ($this->canViewTeam('attendance.regularization.view_team') && in_array((int) $row->employee_id, $this->teamEmployeeIds(false), true)) {
            return;
        }

        abort_unless($allowOwn && (int) $row->employee_id === (int) $this->ownEmployeeId(), 403);
    }
}
