<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Services\HRMS\Attendance\AttendanceViolationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceViolationC extends Controller
{
    use HrmsCrudPage;

    public function __construct(
        private AttendanceViolationService $violationService
    ) {}

    /**
     * Resolve employee display name SQL expression based on existing table columns.
     */
    private function getEmployeeNameSql(): string
    {
        $cols = [];
        if (Schema::hasColumn('employees_new', 'name')) {
            $cols[] = 'employees_new.name';
        }
        if (Schema::hasColumn('employees_new', 'full_name')) {
            $cols[] = 'employees_new.full_name';
        }
        if (Schema::hasColumn('employees_new', 'employee_name')) {
            $cols[] = 'employees_new.employee_name';
        }
        $cols[] = 'users.name';
        $cols[] = "'Employee'";

        return "COALESCE(" . implode(', ', $cols) . ")";
    }

    /**
     * Display Attendance Violations Audit Dashboard.
     */
    public function index(Request $request)
    {
        // 1. Fetch Real-time Dashboard Summary Metrics
        $summaryMetrics = $this->violationService->getSummaryMetrics($request->all());

        // 2. Base Query with Dynamic Column Resolution
        $query = DB::table('attendance_violations')
            ->leftJoin('employees_new', 'employees_new.id', '=', 'attendance_violations.employee_id')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->leftJoin('attendances', 'attendances.id', '=', 'attendance_violations.attendance_id');

        if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
            $query->whereNull('attendance_violations.deleted_at');
        }

        $empNameExpr = $this->getEmployeeNameSql();

        $selects = [
            'attendance_violations.*',
            DB::raw("{$empNameExpr} as employee_display_name"),
            'employees_new.employee_code',
            'attendances.attendance_status as current_attendance_status',
            'attendances.attendance_source',
        ];

        if (Schema::hasTable('departments') && Schema::hasColumn('employees_new', 'department_id')) {
            $query->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id');
            $selects[] = 'departments.name as department_name';
        } else {
            $selects[] = DB::raw("NULL as department_name");
        }

        if (Schema::hasTable('designations') && Schema::hasColumn('employees_new', 'designation_id')) {
            $query->leftJoin('designations', 'designations.id', '=', 'employees_new.designation_id');
            $selects[] = 'designations.name as designation_name';
        } else {
            $selects[] = DB::raw("NULL as designation_name");
        }

        $query->addSelect($selects);

        // 3. Apply Multi-filters
        if ($request->filled('employee_id')) {
            $query->where('attendance_violations.employee_id', $request->input('employee_id'));
        }
        if ($request->filled('department_id') && Schema::hasColumn('employees_new', 'department_id')) {
            $query->where('employees_new.department_id', $request->input('department_id'));
        }
        if ($request->filled('designation_id') && Schema::hasColumn('employees_new', 'designation_id')) {
            $query->where('employees_new.designation_id', $request->input('designation_id'));
        }
        if ($request->filled('type')) {
            $query->where('attendance_violations.type', $request->input('type'));
        }
        if ($request->filled('month')) {
            $monthDate = Carbon::parse($request->input('month') . '-01');
            $query->whereDate('attendance_violations.violation_date', '>=', $monthDate->copy()->startOfMonth()->toDateString())
                  ->whereDate('attendance_violations.violation_date', '<=', $monthDate->copy()->endOfMonth()->toDateString());
        } else {
            if ($request->filled('from')) {
                $query->whereDate('attendance_violations.violation_date', '>=', $request->input('from'));
            }
            if ($request->filled('to')) {
                $query->whereDate('attendance_violations.violation_date', '<=', $request->input('to'));
            }
        }
        if ($request->filled('penalty_status')) {
            $status = $request->input('penalty_status');
            if ($status === 'regularized') {
                $query->where('attendances.attendance_source', 'regularization');
            } elseif ($status === 'converted_half_day') {
                $query->where('attendance_violations.converted_to_half_day', 1);
            } elseif ($status === 'converted_lwp') {
                $query->where('attendance_violations.converted_to_lwp', 1);
            } elseif ($status === 'consumed') {
                if (Schema::hasColumn('attendance_violations', 'is_consumed')) {
                    $query->where('attendance_violations.is_consumed', 1);
                }
            } elseif ($status === 'resolved') {
                $query->where('attendance_violations.policy_action', 'resolved');
            } elseif ($status === 'active') {
                if (Schema::hasColumn('attendance_violations', 'is_consumed')) {
                    $query->where(function ($q) {
                        $q->where('attendance_violations.is_consumed', 0)->orWhereNull('attendance_violations.is_consumed');
                    });
                }
                $query->where(function ($q) {
                    $q->whereNull('attendance_violations.policy_action')->orWhere('attendance_violations.policy_action', '!=', 'resolved');
                });
            }
        }

        // Apply Common Security Scopes
        if (! ($this->canViewAll('attendance.violations.view_all') || $this->canViewAll('attendance.violations.view') || $this->canViewAll('attendance.records.view_all') || $this->canViewAll('attendance.dashboard.view'))) {
            $this->scopeEmployeeVisibility($query, 'attendance.violations.view_all', 'attendance.violations.view_team', 'attendance_violations.employee_id');
        }

        $perPageInput = $request->input('per_page');
        if ($perPageInput === 'all' || $perPageInput == -1) {
            $perPage = 5000;
        } else {
            $perPage = (int) ($perPageInput ?: 500); // Default to 500 so DataTables can paginate 10, 25, 50, 100, All seamlessly!
        }

        $paginatedRows = $query->latest('attendance_violations.id')->paginate($perPage);

        // 4. Enrich Row Items with Read-Only Audit Metrics
        $paginatedRows->getCollection()->transform(function ($row) {
            $row->human_type = $this->violationService->resolveHumanViolationLabel($row->type, $row->policy_action);
            $row->formatted_date = Carbon::parse($row->violation_date)->format('d M Y');
            $row->attendance_status_label = ucfirst(str_replace('_', ' ', $row->current_attendance_status ?? 'N/A'));

            return $row;
        });

        $this->violationService->enrichViolationsWithCycles($paginatedRows->getCollection());

        // 5. Dropdown Options for Filters
        $departments = Schema::hasTable('departments') ? (Schema::hasColumn('departments', 'deleted_at') ? DB::table('departments')->whereNull('deleted_at')->pluck('name', 'id')->toArray() : DB::table('departments')->pluck('name', 'id')->toArray()) : [];
        $designations = Schema::hasTable('designations') ? (Schema::hasColumn('designations', 'deleted_at') ? DB::table('designations')->whereNull('deleted_at')->pluck('name', 'id')->toArray() : DB::table('designations')->pluck('name', 'id')->toArray()) : [];
        $types = [
            'late_login' => 'Late Login Warning',
            'early_logout' => 'Early Logout Warning',
            'missed_punch' => 'Missed Punch Warning',
        ];
        $penaltyStatuses = [
            'active' => 'Active',
            'consumed' => 'Consumed',
            'converted_half_day' => 'Converted to Half Day',
            'converted_lwp' => 'Converted to LWP',
            'regularized' => 'Regularized',
            'resolved' => 'Resolved',
        ];

        // Month options (last 12 months)
        $months = [];
        $now = Carbon::now(AttendanceViolationService::TIMEZONE);
        for ($i = 0; $i < 12; $i++) {
            $m = (clone $now)->subMonths($i);
            $months[$m->format('Y-m')] = $m->format('F Y');
        }

        // Employee Options
        $empQuery = DB::table('employees_new')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->select('employees_new.id', DB::raw("{$empNameExpr} as name"), 'employees_new.employee_code');

        if (Schema::hasColumn('employees_new', 'deleted_at')) {
            $empQuery->whereNull('employees_new.deleted_at');
        }

        $employeeOptions = $empQuery->orderBy('name')->get()->mapWithKeys(function ($e) {
            return [$e->id => "{$e->name} ({$e->employee_code})"];
        })->toArray();

        return view('hrms.attendance.violations.index', [
            'accesses' => $this->accesses(),
            'active' => 'attendance',
            'summaryMetrics' => $summaryMetrics,
            'rows' => $paginatedRows,
            'filters' => [
                'departments' => $departments,
                'designations' => $designations,
                'types' => $types,
                'penalty_statuses' => $penaltyStatuses,
                'months' => $months,
                'employee_options' => $employeeOptions,
            ],
            'pageTitle' => 'Attendance Violations Audit Dashboard',
            'pageSubtitle' => 'Enterprise audit overview of attendance discipline, missed punch cycles, and penalty conversions.',
        ]);
    }

    /**
     * AJAX Payload for Employee Audit Side Drawer.
     */
    public function employeeAudit(int $employeeId)
    {
        $payload = $this->violationService->getEmployeeAuditPayload($employeeId);
        return response()->json($payload);
    }

    /**
     * AJAX Payload for Attendance Detail Audit Modal.
     */
    public function attendanceAudit(int $attendanceId)
    {
        $payload = $this->violationService->getAttendanceAuditDetail($attendanceId);
        return response()->json($payload);
    }

    /**
     * CSV/Excel Audit Log Export.
     */
    public function exportExcel(Request $request)
    {
        $query = DB::table('attendance_violations')
            ->leftJoin('employees_new', 'employees_new.id', '=', 'attendance_violations.employee_id')
            ->leftJoin('users', 'users.id', '=', 'employees_new.user_id')
            ->leftJoin('attendances', 'attendances.id', '=', 'attendance_violations.attendance_id');

        if (Schema::hasColumn('attendance_violations', 'deleted_at')) {
            $query->whereNull('attendance_violations.deleted_at');
        }

        if ($request->filled('employee_id')) {
            $query->where('attendance_violations.employee_id', $request->input('employee_id'));
        }
        if ($request->filled('department_id') && Schema::hasColumn('employees_new', 'department_id')) {
            $query->where('employees_new.department_id', $request->input('department_id'));
        }
        if ($request->filled('designation_id') && Schema::hasColumn('employees_new', 'designation_id')) {
            $query->where('employees_new.designation_id', $request->input('designation_id'));
        }
        if ($request->filled('type')) {
            $query->where('attendance_violations.type', $request->input('type'));
        }
        if ($request->filled('month')) {
            $monthDate = Carbon::parse($request->input('month') . '-01');
            $query->whereDate('attendance_violations.violation_date', '>=', $monthDate->copy()->startOfMonth()->toDateString())
                  ->whereDate('attendance_violations.violation_date', '<=', $monthDate->copy()->endOfMonth()->toDateString());
        } else {
            if ($request->filled('from')) {
                $query->whereDate('attendance_violations.violation_date', '>=', $request->input('from'));
            }
            if ($request->filled('to')) {
                $query->whereDate('attendance_violations.violation_date', '<=', $request->input('to'));
            }
        }

        $empNameExpr = $this->getEmployeeNameSql();

        $selects = [
            'attendance_violations.violation_date',
            'attendance_violations.type',
            'attendance_violations.minutes',
            'attendance_violations.remarks',
            'attendance_violations.policy_action',
            'attendance_violations.converted_to_half_day',
            'attendance_violations.converted_to_lwp',
            'attendance_violations.is_consumed',
            'attendance_violations.created_at',
            DB::raw("{$empNameExpr} as employee_name"),
            'employees_new.employee_code',
            'attendances.attendance_status',
        ];

        if (Schema::hasTable('departments') && Schema::hasColumn('employees_new', 'department_id')) {
            $query->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id');
            $selects[] = 'departments.name as department_name';
        }

        if (Schema::hasTable('designations') && Schema::hasColumn('employees_new', 'designation_id')) {
            $query->leftJoin('designations', 'designations.id', '=', 'employees_new.designation_id');
            $selects[] = 'designations.name as designation_name';
        }

        $records = $query->select($selects)->latest('attendance_violations.id')->get();
        $this->violationService->enrichViolationsWithCycles($records);

        $filename = 'attendance_violations_audit_' . Carbon::now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'S.No.',
                'Employee Name',
                'Employee Code',
                'Department',
                'Designation',
                'Violation Date',
                'Violation Type',
                'Minutes',
                'Active Counter',
                'Penalty Status',
                'Attendance Status',
                'Remarks',
                'Created At',
            ]);

            foreach ($records as $index => $row) {
                $humanType = $this->violationService->resolveHumanViolationLabel($row->type, $row->policy_action);

                fputcsv($file, [
                    $index + 1,
                    $row->employee_name,
                    $row->employee_code,
                    $row->department_name ?? 'N/A',
                    $row->designation_name ?? 'N/A',
                    Carbon::parse($row->violation_date)->format('d M Y'),
                    $humanType,
                    $row->minutes ?? 0,
                    $row->active_counter ?? '-',
                    $row->penalty_status_label ?? 'Active',
                    ucfirst(str_replace('_', ' ', $row->attendance_status ?? 'N/A')),
                    $row->remarks ?? '',
                    Carbon::parse($row->created_at)->format('d M Y h:i A'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
