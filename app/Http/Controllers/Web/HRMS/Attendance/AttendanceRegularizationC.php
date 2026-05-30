<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Mail\HrWorkflowAlertMail;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Employee\EmployeeM;
use App\Services\HRMS\Attendance\AttendanceS;
use App\Services\HRMS\Notification\NotificationS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AttendanceRegularizationC extends Controller
{
    use HrmsCrudPage;
    private const REQUEST_TYPES = [
        'missed_punch_in','missed_punch_out','wrong_punch_time','late_mark_exemption',
        'early_logout_correction','geofence_issue','system_error','other',
    ];

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
            ->leftJoin('attendances', 'attendances.id', '=', 'attendance_regularizations.attendance_id')
            ->addSelect([
                DB::raw('attendances.attendance_date as mapped_attendance_date'),
                DB::raw('attendances.punch_in_time as mapped_current_punch_in'),
                DB::raw('attendances.punch_out_time as mapped_current_punch_out'),
            ])
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
            'attendance_date' => 'required|date|before_or_equal:today',
            'request_type' => 'required|string|in:' . implode(',', self::REQUEST_TYPES),
            'requested_punch_in' => 'nullable|date_format:H:i',
            'requested_punch_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|min:5',
            'status' => 'nullable|in:pending,approved,rejected,cancelled',
        ]);

        if (! $this->canViewAll('attendance.regularization.view_all')) {
            $employeeId = $this->ownEmployeeId();
            abort_if(! $employeeId, 403);
            $data['employee_id'] = $employeeId;
        }

        $attendance = AttendanceM::where('employee_id', $data['employee_id'])
            ->whereDate('attendance_date', $data['attendance_date'])
            ->first();
        $attendanceDate = $data['attendance_date'];
        unset($data['attendance_date']);
        $pendingExists = DB::table('attendance_regularizations')
            ->where('employee_id', $data['employee_id'])
            ->where('request_type', $data['request_type'])
            ->where('status', 'pending')
            ->whereNull('deleted_at')
            ->whereDate('created_at', $attendanceDate)
            ->exists();
        if ($pendingExists) {
            return back()->with('error', 'Duplicate pending request for same date/type already exists.');
        }
        if ($data['request_type'] === 'missed_punch_in' && empty($data['requested_punch_in'])) {
            return back()->withErrors(['requested_punch_in' => 'Requested punch in time is required.'])->withInput();
        }
        if ($data['request_type'] === 'missed_punch_out' && empty($data['requested_punch_out'])) {
            return back()->withErrors(['requested_punch_out' => 'Requested punch out time is required.'])->withInput();
        }

        $requestedIn = ! empty($data['requested_punch_in']) ? Carbon::parse($attendanceDate . ' ' . $data['requested_punch_in'])->toDateTimeString() : null;
        $requestedOut = ! empty($data['requested_punch_out']) ? Carbon::parse($attendanceDate . ' ' . $data['requested_punch_out'])->toDateTimeString() : null;
        DB::table('attendance_regularizations')->insert(array_merge($data, [
            'attendance_id' => $attendance?->id,
            'existing_punch_in' => $attendance?->punch_in_time,
            'existing_punch_out' => $attendance?->punch_out_time,
            'requested_punch_in' => $requestedIn,
            'requested_punch_out' => $requestedOut,
            'status' => $data['status'] ?? 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        app(NotificationS::class)->notifyHrAndSuperAdmin(
            'Attendance Regularization Request',
            'Regularization request submitted by employee.',
            'attendance_regularization_submitted',
            'hrms.attendance.regularizations.index'
        );

        $hrEmail = config('hrms.emails.hr');
        if ($hrEmail) {
            $employee = EmployeeM::with(['user', 'department'])->find($data['employee_id']);
            $details = [
                'Employee Name' => $employee?->display_name ?: 'Employee',
                'Employee Code' => $employee?->employee_code ?: 'N/A',
                'Attendance Date' => $attendanceDate,
                'Request Type' => (string) $data['request_type'],
                'Current Punch In' => (string) ($attendance?->punch_in_time ?: '-'),
                'Current Punch Out' => (string) ($attendance?->punch_out_time ?: '-'),
                'Requested Punch In' => (string) ($data['requested_punch_in'] ?: '-'),
                'Requested Punch Out' => (string) ($data['requested_punch_out'] ?: '-'),
                'Reason' => (string) $data['reason'],
            ];

            Mail::to($hrEmail)->queue(new HrWorkflowAlertMail(
                subjectText: 'Attendance Regularization Request - ' . ($employee?->display_name ?: 'Employee'),
                workflowTitle: 'Attendance Regularization Request',
                details: $details,
                actionUrl: route('hrms.attendance.regularizations.index'),
                replyToEmail: $employee?->user?->email
            ));
        }

        return back()->with('success', 'Regularization request saved.');
    }

    public function update(Request $request, $id)
    {
        $this->authorizeRegularizationRow($id, true);
        $row = DB::table('attendance_regularizations')->where('id', $id)->first();
        abort_if(! $row, 404);

        $data = $request->validate([
            'employee_id' => 'required|exists:employees_new,id',
            'request_type' => 'required|string|in:' . implode(',', self::REQUEST_TYPES),
            'requested_punch_in' => 'nullable|date_format:H:i',
            'requested_punch_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|min:5',
            'status' => 'nullable|in:pending,approved,rejected,cancelled',
        ]);

        $baseDate = Carbon::parse($row->created_at)->toDateString();
        $data['requested_punch_in'] = ! empty($data['requested_punch_in']) ? Carbon::parse($baseDate . ' ' . $data['requested_punch_in'])->toDateTimeString() : null;
        $data['requested_punch_out'] = ! empty($data['requested_punch_out']) ? Carbon::parse($baseDate . ' ' . $data['requested_punch_out'])->toDateTimeString() : null;
        DB::table('attendance_regularizations')->where('id', $id)->update(array_merge($data, ['updated_at' => now()]));

        return back()->with('success', 'Regularization request updated.');
    }

    public function approve($id)
    {
        abort_unless($this->userHasPermission('attendance.regularization.approve'), 403);
        $this->authorizeRegularizationRow($id, false);

        $row = DB::table('attendance_regularizations')->where('id', $id)->first();
        if (! $row || $row->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }
        $attendance = $row->attendance_id ? AttendanceM::find($row->attendance_id) : null;
        if (! $attendance) {
            $attendance = AttendanceM::firstOrCreate(
                ['employee_id' => $row->employee_id, 'attendance_date' => Carbon::parse($row->created_at)->toDateString()]
            );
        }
        if ($attendance->payroll_processed || $attendance->is_locked) {
            return back()->with('error', 'Attendance is locked/payroll processed for this date.');
        }
        if ($row->requested_punch_in) {
            $attendance->punch_in_time = Carbon::parse($row->requested_punch_in)->format('H:i:s');
        }
        if ($row->requested_punch_out) {
            $attendance->punch_out_time = Carbon::parse($row->requested_punch_out)->format('H:i:s');
        }
        $attendance->save();
        app(AttendanceS::class)->calculateAttendanceStats($attendance);
        DB::table('attendance_regularizations')->where('id', $id)->update([
            'attendance_id' => $attendance->id,
            'status' => 'approved',
            'approved_by_user_id' => $this->actorId(),
            'approved_at' => $this->nowKolkata(),
            'updated_at' => now(),
        ]);
        $employee = EmployeeM::find($row->employee_id);
        if ($employee?->user_id) {
            app(NotificationS::class)->notifyEmployee(
                'Attendance Regularization Update',
                'Your regularization request has been approved.',
                'attendance_regularization_approved',
                'hrms.attendance.regularizations.index',
                [],
                ['regularization_id' => $id],
                (int) $employee->user_id
            );
        }

        return back()->with('success', 'Regularization approved.');
    }

    public function reject($id)
    {
        abort_unless($this->userHasPermission('attendance.regularization.reject'), 403);
        $this->authorizeRegularizationRow($id, false);

        $note = request('rejection_note') ?: request('rejection_reason');
        DB::table('attendance_regularizations')->where('id', $id)->update([
            'status' => 'rejected',
            'approved_by_user_id' => $this->actorId(),
            'approved_at' => $this->nowKolkata(),
            'rejection_reason' => $note,
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

    public function exportExcel(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.export'), 403);

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
        $rows = $query->latest('attendance_regularizations.id')->get();

        return response()->stream(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Employee', 'Code', 'Type', 'Requested In', 'Requested Out', 'Status', 'Created At']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->employee_display_name,
                    $row->employee_code,
                    $row->request_type,
                    $row->requested_punch_in,
                    $row->requested_punch_out,
                    $row->status,
                    $row->created_at,
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_regularizations.csv"',
        ]);
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
                ['key' => 'mapped_attendance_date', 'label' => 'Date', 'type' => 'date'],
                ['key' => 'request_type', 'label' => 'Request Type'],
                ['key' => 'mapped_current_punch_in', 'label' => 'Current In', 'type' => 'datetime'],
                ['key' => 'mapped_current_punch_out', 'label' => 'Current Out', 'type' => 'datetime'],
                ['key' => 'requested_punch_in', 'label' => 'Requested In', 'type' => 'datetime'],
                ['key' => 'requested_punch_out', 'label' => 'Requested Out', 'type' => 'datetime'],
                ['key' => 'reason', 'label' => 'Reason'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ['key' => 'created_at', 'label' => 'Submitted At', 'type' => 'datetime'],
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
                ['name' => 'attendance_date', 'label' => 'Attendance Date', 'type' => 'date'],
                ['name' => 'request_type', 'label' => 'Request Type', 'type' => 'select', 'options' => [
                    'missed_punch_in' => 'Missed Punch In',
                    'missed_punch_out' => 'Missed Punch Out',
                    'wrong_punch_time' => 'Wrong Punch Timing',
                    'late_mark_exemption' => 'Late Mark Exemption',
                    'early_logout_correction' => 'Early Logout Correction',
                    'geofence_issue' => 'Geofence Issue',
                    'system_error' => 'System/App Error',
                    'other' => 'Other',
                ]],
                ['name' => 'requested_punch_in', 'label' => 'Requested Punch In', 'type' => 'time'],
                ['name' => 'requested_punch_out', 'label' => 'Requested Punch Out', 'type' => 'time'],
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
