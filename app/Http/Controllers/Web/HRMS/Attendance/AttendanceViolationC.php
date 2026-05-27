<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceViolationC extends Controller
{
    use HrmsCrudPage;

    public function index(Request $request)
    {
        $query = $this->employeeJoinedQuery('attendance_violations');
        $this->applyCommonFilters($query, $request, ['dateColumn' => 'attendance_violations.violation_date', 'filterMap' => ['employee_id' => 'attendance_violations.employee_id', 'type' => 'attendance_violations.type', 'converted_to_lwp' => 'attendance_violations.converted_to_lwp']]);
        $types = DB::table('attendance_violations')->whereNotNull('type')->distinct()->pluck('type', 'type')->toArray();
        return view('hrms.attendance.violations.index', [
            'accesses' => $this->accesses(), 'active' => 'attendance', 'pageTitle' => 'Attendance Violations', 'pageSubtitle' => 'Read-only audit list of late, early, missed punch, and conversion events.',
            'rows' => $query->latest('attendance_violations.id')->paginate(50),
            'columns' => [['key' => 'employee_display_name', 'label' => 'Employee'], ['key' => 'violation_date', 'label' => 'Date', 'type' => 'date'], ['key' => 'type', 'label' => 'Type'], ['key' => 'minutes', 'label' => 'Minutes'], ['key' => 'converted_to_half_day', 'label' => 'Half Day', 'type' => 'badge'], ['key' => 'converted_to_lwp', 'label' => 'LWP', 'type' => 'badge'], ['key' => 'policy_action', 'label' => 'Policy Action']],
            'filters' => [['name' => 'employee_id', 'label' => 'Employee', 'type' => 'select', 'options' => $this->employeeOptions()->pluck('display_name', 'id')->toArray()], ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => $types], ['name' => 'converted_to_lwp', 'label' => 'Converted', 'type' => 'select', 'options' => [1 => 'Converted to LWP', 0 => 'Not Converted']], ['name' => 'from', 'label' => 'From', 'type' => 'date'], ['name' => 'to', 'label' => 'To', 'type' => 'date']],
            'canCreate' => false,
        ]);
    }

    public function exportExcel(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.export'), 403);

        $query = $this->employeeJoinedQuery('attendance_violations');
        $this->applyCommonFilters($query, $request, [
            'dateColumn' => 'attendance_violations.violation_date',
            'filterMap' => [
                'employee_id' => 'attendance_violations.employee_id',
                'type' => 'attendance_violations.type',
                'converted_to_lwp' => 'attendance_violations.converted_to_lwp',
            ],
        ]);
        $rows = $query->latest('attendance_violations.id')->get();

        return response()->stream(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Employee', 'Type', 'Minutes', 'Converted Half Day', 'Converted LWP', 'Policy Action', 'Source']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->violation_date,
                    $row->employee_display_name,
                    $row->type,
                    $row->minutes,
                    (int) $row->converted_to_half_day,
                    (int) $row->converted_to_lwp,
                    $row->policy_action,
                    $row->source,
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_violations.csv"',
        ]);
    }
}
