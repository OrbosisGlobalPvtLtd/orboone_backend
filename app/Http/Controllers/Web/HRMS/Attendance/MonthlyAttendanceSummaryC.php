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

        if (!$request->has('month') && !$request->has('year') && !$request->has('reset')) {
            $request->merge([
                'month' => (string) now()->month,
                'year' => (string) now()->year,
            ]);
        }

        $query = DB::table('monthly_attendance_summaries')
            ->leftJoin('employees_new', 'employees_new.id', '=', 'monthly_attendance_summaries.employee_id')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id')
            ->select(
                'monthly_attendance_summaries.*',
                'employees_new.employee_code',
                'departments.name as department_name',
                DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as employee_display_name"),
                DB::raw("(SELECT COUNT(*) FROM attendances a WHERE a.employee_id = monthly_attendance_summaries.employee_id AND MONTH(a.attendance_date) = monthly_attendance_summaries.month AND YEAR(a.attendance_date) = monthly_attendance_summaries.year AND (a.attendance_status IN ('pending_hr','punch_blocked') OR a.is_punch_blocked = 1 OR a.is_blocked = 1 OR a.is_missed_punch = 1 OR a.missed_punch = 1)) as unresolved_count")
            );

        if ($request->filled('employee_id')) {
            $query->where('monthly_attendance_summaries.employee_id', $request->employee_id);
        }
        if ($request->filled('month')) {
            $query->where('monthly_attendance_summaries.month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('monthly_attendance_summaries.year', $request->year);
        }
        if ($request->filled('locked')) {
            $query->where('monthly_attendance_summaries.is_locked', $request->locked);
        }

        $rows = $query->orderByDesc('year')->orderByDesc('month')->paginate(50);

        $employees = DB::table('employees_new')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->select(
                'employees_new.id',
                'employees_new.employee_code',
                DB::raw("COALESCE(users.name, employees_new.employee_code, 'N/A') as display_name")
            )
            ->orderByRaw("COALESCE(users.name, employees_new.employee_code)")
            ->get();

        return view('hrms.attendance.monthly_summary.index', [
            'active' => 'attendances',
            'rows' => $rows,
            'employees' => $employees,
        ]);
    }

    public function generate(Request $request, \App\Services\HRMS\Attendance\PayrollAttendanceSummaryService $summaryService)
    {
        abort_unless($this->userHasPermission('attendance.monthly_summary.view'), 403);

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $employeeId = $request->filled('employee_id') ? (int) $request->employee_id : null;

        $count = $summaryService->generate($month, $year, $employeeId);

        return redirect()->route('hrms.attendance.monthly_summary.index', [
            'month' => $month,
            'year' => $year,
            'employee_id' => $employeeId,
        ])->with('success', "Generated monthly attendance summary for {$count} employee(s).");
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

    public function exportExcel(Request $request)
    {
        abort_unless($this->userHasPermission('attendance.export'), 403);

        $query = $this->employeeJoinedQuery('monthly_attendance_summaries');
        $this->applyCommonFilters($query, $request, [
            'filterMap' => [
                'employee_id' => 'monthly_attendance_summaries.employee_id',
                'month' => 'monthly_attendance_summaries.month',
                'year' => 'monthly_attendance_summaries.year',
                'locked' => 'monthly_attendance_summaries.is_locked',
            ],
        ]);
        $rows = $query->orderByDesc('year')->orderByDesc('month')->get();

        return response()->stream(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Employee', 'Month', 'Year', 'Present', 'Paid Leave', 'Half Days', 'LWP', 'Payable Days', 'Late', 'Early Out', 'Missed Punch', 'Locked']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->employee_display_name,
                    $row->month,
                    $row->year,
                    $row->present_days,
                    $row->paid_leave_days,
                    $row->half_days,
                    $row->lwp_days,
                    $row->payable_days,
                    $row->late_count,
                    $row->early_out_count,
                    $row->missed_punch_count,
                    (int) $row->is_locked,
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="monthly_attendance_summary.csv"',
        ]);
    }
}
