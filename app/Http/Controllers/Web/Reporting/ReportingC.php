<?php

namespace App\Http\Controllers\Web\Reporting;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Reporting\ReportingAssignmentM;
use App\Services\HRMS\Reporting\ReportingScopeS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportingC extends Controller
{
    protected ReportingScopeS $scopeS;

    public function __construct(ReportingScopeS $scopeS)
    {
        $this->scopeS = $scopeS;
    }

    /**
     * Reporting Management Dashboard
     */
    public function dashboard(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $isGlobal = $this->scopeS->isSuperAdminOrGlobal();

        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);

        // Core Counts
        $employeesCount = count($supervisedEmpIds);
        if ($isGlobal) {
            $employeesCount = DB::table('reporting_assignments')->where('is_active', 1)->distinct('employee_id')->count('employee_id')
                + DB::table('technical_lead_assignments')->where('is_active', 1)->distinct('employee_id')->count('employee_id');
        }

        $supervisorsCount = DB::table('reporting_assignments')->where('is_active', 1)->distinct('supervisor_employee_id')->count('supervisor_employee_id')
            + DB::table('technical_lead_assignments')->where('is_active', 1)->distinct('technical_lead_employee_id')->count('technical_lead_employee_id');

        $projectsCount = DB::table('project_assignments')
            ->whereIn('employee_id', $supervisedEmpIds)
            ->where('is_active', 1)
            ->distinct('project_id')
            ->count('project_id');

        // Today's Attendance stats
        $today = date('Y-m-d');
        $attendanceQuery = DB::table('attendances')->whereDate('attendance_date', $today);
        $attendanceQuery = $this->scopeS->scopeAttendanceQuery($attendanceQuery, $supervisorEmpId);
        $todayAttendances = $attendanceQuery->get();

        $presentCount = $todayAttendances->whereIn('status', ['present', 'late', 'half_day'])->count();
        $wfhCount = $todayAttendances->where('work_type', 'wfh')->count();
        $absentCount = $todayAttendances->where('status', 'absent')->count();

        // Today's Leave stats
        $leaveQuery = DB::table('leave_requests')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', 'approved');
        $leaveQuery = $this->scopeS->scopeLeaveQuery($leaveQuery, $supervisorEmpId);
        $onLeaveCount = $leaveQuery->count();

        // Work reports today
        $workReportsQuery = DB::table('attendance_work_logs')->whereDate('work_date', $today);
        $workReportsQuery = $this->scopeS->scopeWorkReports($workReportsQuery, $supervisorEmpId);
        $workReportsSubmittedToday = $workReportsQuery->count();

        // Supervised Tasks
        $tasksQuery = DB::table('project_tasks');
        $tasksQuery = $this->scopeS->scopeTasks($tasksQuery, $supervisorEmpId);
        $tasks = $tasksQuery->get();

        $taskStats = [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'todo' => $tasks->where('status', 'todo')->count(),
            'blocked' => $tasks->where('status', 'blocked')->count(),
        ];

        // Developer daily status list
        $recentDevelopersList = EmployeeM::with(['user', 'designation'])
            ->whereIn('id', array_slice($supervisedEmpIds, 0, 20))
            ->get();

        $recentDevelopers = [];
        foreach ($recentDevelopersList as $emp) {
            $att = $todayAttendances->firstWhere('employee_id', $emp->id);
            $lve = DB::table('leave_requests')
                ->where('employee_id', $emp->id)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();

            $wlog = DB::table('attendance_work_logs')
                ->where('employee_id', $emp->id)
                ->whereDate('work_date', $today)
                ->first();

            $prjs = DB::table('project_assignments')
                ->join('projects', 'projects.id', '=', 'project_assignments.project_id')
                ->leftJoin('project_teams', 'project_teams.id', '=', 'project_assignments.project_team_id')
                ->where('project_assignments.employee_id', $emp->id)
                ->where('project_assignments.is_active', 1)
                ->select('projects.name as project_name', 'project_teams.team_name as team_name')
                ->get();

            $empTasks = $tasks->where('assigned_employee_id', $emp->id);
            $totalEmpTasks = $empTasks->count();
            $completedEmpTasks = $empTasks->where('status', 'completed')->count();

            $recentDevelopers[] = [
                'employee' => $emp,
                'attendance' => $att,
                'leave' => $lve,
                'work_log' => $wlog,
                'projects' => $prjs,
                'total_tasks' => $totalEmpTasks,
                'completed_tasks' => $completedEmpTasks,
            ];
        }

        return view('hrms.reporting.dashboard', compact(
            'employeesCount',
            'supervisorsCount',
            'projectsCount',
            'presentCount',
            'wfhCount',
            'absentCount',
            'onLeaveCount',
            'workReportsSubmittedToday',
            'taskStats',
            'recentDevelopers'
        ));
    }

    /**
     * Reporting Structure Overview
     */
    public function structure(Request $request)
    {
        $activeEmpIds = EmployeeM::active()->pluck('id')->toArray();

        $supervisors = DB::table('reporting_assignments')
            ->join('employees_new as s', 's.id', '=', 'reporting_assignments.supervisor_employee_id')
            ->leftJoin('users as su', 'su.id', '=', 's.user_id')
            ->leftJoin('designations as sd', 'sd.id', '=', 's.designation_id')
            ->leftJoin('departments as sdept', 'sdept.id', '=', 's.department_id')
            ->where('reporting_assignments.is_active', 1)
            ->whereIn('reporting_assignments.supervisor_employee_id', $activeEmpIds)
            ->whereIn('reporting_assignments.employee_id', $activeEmpIds)
            ->select(
                's.id as supervisor_id',
                DB::raw('COALESCE(su.name, s.employee_code) as supervisor_name'),
                's.employee_code as supervisor_code',
                'sd.name as designation_name',
                'sdept.name as department_name'
            )
            ->distinct()
            ->get();

        foreach ($supervisors as $sup) {
            $sup->employees = DB::table('reporting_assignments')
                ->join('employees_new as e', 'e.id', '=', 'reporting_assignments.employee_id')
                ->leftJoin('users as eu', 'eu.id', '=', 'e.user_id')
                ->leftJoin('designations as ed', 'ed.id', '=', 'e.designation_id')
                ->where('reporting_assignments.supervisor_employee_id', $sup->supervisor_id)
                ->where('reporting_assignments.is_active', 1)
                ->whereIn('reporting_assignments.employee_id', $activeEmpIds)
                ->select(DB::raw('COALESCE(eu.name, e.employee_code) as display_name'), 'e.employee_code', 'ed.name as designation_name')
                ->get();
        }

        return view('hrms.reporting.structure', compact('supervisors'));
    }

    /**
     * Assign Supervisor Roster
     */
    public function supervisors(Request $request)
    {
        $activeEmpIds = EmployeeM::active()->pluck('id')->toArray();

        $supervisorsData = DB::table('reporting_assignments')
            ->join('employees_new as s', 's.id', '=', 'reporting_assignments.supervisor_employee_id')
            ->leftJoin('users as su', 'su.id', '=', 's.user_id')
            ->leftJoin('designations as d', 'd.id', '=', 's.designation_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 's.department_id')
            ->where('reporting_assignments.is_active', 1)
            ->whereIn('reporting_assignments.supervisor_employee_id', $activeEmpIds)
            ->whereIn('reporting_assignments.employee_id', $activeEmpIds)
            ->select(
                's.id as supervisor_id',
                DB::raw('COALESCE(su.name, s.employee_code) as supervisor_name'),
                's.employee_code as supervisor_code',
                'd.name as designation_name',
                'dept.name as department_name',
                DB::raw('COUNT(DISTINCT reporting_assignments.employee_id) as employees_count')
            )
            ->groupBy('s.id', 'su.name', 's.employee_code', 'd.name', 'dept.name')
            ->get();

        return view('hrms.reporting.supervisors', compact('supervisorsData'));
    }

    /**
     * Employee Assignments Roster
     */
    public function assignments(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $activeEmpIds = EmployeeM::active()->pluck('id')->toArray();

        $assignmentsQuery = DB::table('reporting_assignments')
            ->join('employees_new as e', 'e.id', '=', 'reporting_assignments.employee_id')
            ->leftJoin('users as eu', 'eu.id', '=', 'e.user_id')
            ->join('employees_new as s', 's.id', '=', 'reporting_assignments.supervisor_employee_id')
            ->leftJoin('users as su', 'su.id', '=', 's.user_id')
            ->leftJoin('designations as ed', 'ed.id', '=', 'e.designation_id')
            ->leftJoin('departments as edept', 'edept.id', '=', 'e.department_id')
            ->where('reporting_assignments.is_active', 1)
            ->whereIn('reporting_assignments.employee_id', $activeEmpIds);

        if (!$this->scopeS->isSuperAdminOrGlobal() && $supervisorEmpId) {
            $assignmentsQuery->where('reporting_assignments.supervisor_employee_id', $supervisorEmpId);
        }

        $selectedSupervisor = null;

        if ($request->filled('supervisor_id')) {
            $selectedSupervisor = EmployeeM::with(['user', 'designation', 'department'])->find($request->supervisor_id);
            $assignmentsQuery->where('reporting_assignments.supervisor_employee_id', $request->supervisor_id);
        } elseif ($request->filled('search')) {
            $search = trim($request->search);
            $matchedSup = EmployeeM::with(['user', 'designation', 'department'])
                ->where(function($q) use ($search) {
                    $q->whereHas('user', function($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('employee_code', $search);
                })
                ->first();

            if ($matchedSup) {
                $isSup = DB::table('reporting_assignments')
                    ->where('supervisor_employee_id', $matchedSup->id)
                    ->where('is_active', 1)
                    ->exists();

                if ($isSup) {
                    $selectedSupervisor = $matchedSup;
                }
            }

            $assignmentsQuery->where(function($q) use ($search) {
                $q->where('eu.name', 'like', "%{$search}%")
                  ->orWhere('e.employee_code', 'like', "%{$search}%")
                  ->orWhere('su.name', 'like', "%{$search}%");
            });
        }

        $assignments = $assignmentsQuery
            ->select(
                'reporting_assignments.*',
                DB::raw('COALESCE(eu.name, e.employee_code) as employee_name'),
                'e.employee_code',
                'ed.name as designation_name',
                'edept.name as department_name',
                DB::raw('COALESCE(su.name, s.employee_code) as supervisor_name')
            )
            ->orderByDesc('reporting_assignments.id')
            ->paginate(20)
            ->appends($request->query());

        $employees = EmployeeM::with(['user', 'designation', 'department'])->active()->orderBy('id')->get();
        $employees->transform(function ($emp) {
            $activeSup = DB::table('reporting_assignments')
                ->join('employees_new as s', 's.id', '=', 'reporting_assignments.supervisor_employee_id')
                ->leftJoin('users as su', 'su.id', '=', 's.user_id')
                ->where('reporting_assignments.employee_id', $emp->id)
                ->where('reporting_assignments.is_active', 1)
                ->select(DB::raw('COALESCE(su.name, s.employee_code) as display_name'))
                ->first();

            $emp->current_supervisor_name = $activeSup ? $activeSup->display_name : null;
            return $emp;
        });

        $supervisors = EmployeeM::with(['user', 'designation', 'department'])->active()->orderBy('id')->get();

        return view('hrms.reporting.assignments', compact('assignments', 'employees', 'supervisors', 'selectedSupervisor'));
    }

    /**
     * Process Supervisor & Employee Assignment / Transfer
     */
    public function assignSupervisor(Request $request)
    {
        $request->validate([
            'supervisor_employee_id' => 'required|exists:employees_new,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees_new,id',
            'start_date' => 'nullable|date',
        ]);

        $supervisorId = (int)$request->supervisor_employee_id;
        $employeeIds = $request->employee_ids;
        $startDate = $request->start_date ?? date('Y-m-d');

        $assignedCount = 0;
        foreach ($employeeIds as $empId) {
            if ((int)$empId === $supervisorId) {
                continue;
            }

            $this->scopeS->assignSupervisor([
                'supervisor_employee_id' => $supervisorId,
                'employee_id' => (int)$empId,
                'start_date' => $startDate,
            ]);
            $assignedCount++;
        }

        return redirect()->route('reporting.assignments')->with('success', "Successfully assigned {$assignedCount} employee(s) to supervisor.");
    }

    /**
     * Process Relieving an Employee from Supervisor
     */
    public function relieveEmployee(Request $request, $id)
    {
        $this->scopeS->relieveEmployee((int)$id);
        return redirect()->back()->with('success', 'Employee successfully relieved from supervisor.');
    }

    /**
     * My Reporting Employees view for Supervisor
     */
    public function myEmployees(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);

        $employees = EmployeeM::with(['user', 'designation', 'department', 'reportingManager.user'])
            ->active()
            ->whereIn('id', $supervisedEmpIds)
            ->paginate(20);

        $today = date('Y-m-d');

        $directReportingEmpIds = DB::table('employees_new')
            ->where('reporting_manager_employee_id', $supervisorEmpId)
            ->pluck('id')
            ->toArray();

        $reportingAssignEmpIds = DB::table('reporting_assignments')
            ->where('supervisor_employee_id', $supervisorEmpId)
            ->where('is_active', 1)
            ->pluck('employee_id')
            ->toArray();

        $allReportingEmpIds = array_unique(array_merge($directReportingEmpIds, $reportingAssignEmpIds));

        $employees->getCollection()->transform(function ($emp) use ($today, $allReportingEmpIds) {
            // Projects, Teams & Roles
            $emp->project_assignments_list = DB::table('project_assignments')
                ->join('projects', 'projects.id', '=', 'project_assignments.project_id')
                ->leftJoin('project_teams', 'project_teams.id', '=', 'project_assignments.project_team_id')
                ->where('project_assignments.employee_id', $emp->id)
                ->where('project_assignments.is_active', 1)
                ->select(
                    'projects.name as project_name',
                    'project_teams.team_name as team_name',
                    'project_assignments.project_role as role_name'
                )
                ->get();

            $isReporting = in_array((int)$emp->id, $allReportingEmpIds, true);
            $hasProjectAssign = count($emp->project_assignments_list) > 0;

            if ($isReporting && $hasProjectAssign) {
                $emp->team_source = 'Both';
            } elseif ($isReporting) {
                $emp->team_source = 'Reporting Team';
            } else {
                $emp->team_source = 'Project Team';
            }

            // Attendance today
            $emp->attendance_today = DB::table('attendances')
                ->where('employee_id', $emp->id)
                ->whereDate('attendance_date', $today)
                ->first();

            // Leave today
            $emp->leave_today = DB::table('leave_requests')
                ->where('employee_id', $emp->id)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->where('status', 'approved')
                ->first();

            // Work report today
            $emp->work_report_today = DB::table('attendance_work_logs')
                ->where('employee_id', $emp->id)
                ->whereDate('work_date', $today)
                ->first();

            // Tasks progress
            $emp->active_task = DB::table('project_tasks')
                ->where('assigned_employee_id', $emp->id)
                ->whereIn('status', ['in_progress', 'todo', 'blocked'])
                ->orderByDesc('id')
                ->first();

            $emp->completed_tasks_count = DB::table('project_tasks')
                ->where('assigned_employee_id', $emp->id)
                ->where('status', 'completed')
                ->count();

            $emp->total_tasks_count = DB::table('project_tasks')
                ->where('assigned_employee_id', $emp->id)
                ->count();

            return $emp;
        });

        return view('hrms.reporting.my_employees', compact('employees'));
    }

    /**
     * Scoped Team Attendance
     */
    public function attendance(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);
        $date = $request->date ?? date('Y-m-d');

        $empQuery = EmployeeM::with(['user', 'designation', 'department'])
            ->active()
            ->whereIn('id', $supervisedEmpIds);

        if ($request->filled('employee_id')) {
            $empQuery->where('id', $request->employee_id);
        }

        $employeesPaginator = $empQuery->paginate(20)->appends($request->query());

        $presentCount = 0;
        $onLeaveCount = 0;
        $notPunchedCount = 0;

        $employeesPaginator->getCollection()->transform(function ($emp) use ($date, &$presentCount, &$onLeaveCount, &$notPunchedCount) {
            $emp->attendance_record = DB::table('attendances')
                ->where('employee_id', $emp->id)
                ->whereDate('attendance_date', $date)
                ->first();

            $emp->leave_record = DB::table('leave_requests')
                ->where('employee_id', $emp->id)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->where('status', 'approved')
                ->first();

            if ($emp->leave_record) {
                $onLeaveCount++;
            } elseif ($emp->attendance_record) {
                $presentCount++;
            } else {
                $notPunchedCount++;
            }

            return $emp;
        });

        $totalTeamCount = count($supervisedEmpIds);
        $teamEmployees = EmployeeM::with(['user'])->active()->whereIn('id', $supervisedEmpIds)->get();

        return view('hrms.reporting.attendance', compact(
            'employeesPaginator',
            'date',
            'teamEmployees',
            'totalTeamCount',
            'presentCount',
            'onLeaveCount',
            'notPunchedCount'
        ));
    }

    /**
     * Scoped Team Leave
     */
    public function leave(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);
        $today = date('Y-m-d');

        $query = DB::table('leave_requests')
            ->join('employees_new as e', 'e.id', '=', 'leave_requests.employee_id')
            ->leftJoin('users as eu', 'eu.id', '=', 'e.user_id')
            ->leftJoin('designations as d', 'd.id', '=', 'e.designation_id')
            ->leftJoin('departments as dept', 'dept.id', '=', 'e.department_id')
            ->leftJoin('leave_types as lt', 'lt.id', '=', 'leave_requests.leave_type_id');

        $query = $this->scopeS->scopeLeaveQuery($query, $supervisorEmpId);

        if ($request->filled('employee_id')) {
            $query->where('leave_requests.employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('leave_requests.status', strtolower($request->status));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('leave_requests.start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('leave_requests.end_date', '<=', $request->end_date);
        }

        $leaveRequests = $query->select(
            'leave_requests.*',
            DB::raw('COALESCE(eu.name, e.employee_code) as display_name'),
            'e.employee_code',
            'd.name as designation_name',
            'dept.name as department_name',
            'lt.name as leave_type_name'
        )
            ->orderByDesc('leave_requests.id')
            ->paginate(20)
            ->appends($request->query());

        // Today's Team Leave summary
        $todayLeaveQuery = DB::table('leave_requests')
            ->join('employees_new as e', 'e.id', '=', 'leave_requests.employee_id')
            ->leftJoin('users as eu', 'eu.id', '=', 'e.user_id')
            ->leftJoin('leave_types as lt', 'lt.id', '=', 'leave_requests.leave_type_id')
            ->whereDate('leave_requests.start_date', '<=', $today)
            ->whereDate('leave_requests.end_date', '>=', $today)
            ->where('leave_requests.status', 'approved');

        $todayLeaveQuery = $this->scopeS->scopeLeaveQuery($todayLeaveQuery, $supervisorEmpId);
        $todayLeaves = $todayLeaveQuery->select('e.id', DB::raw('COALESCE(eu.name, e.employee_code) as display_name'), 'lt.name as leave_type_name')->get();

        // Calculate KPI summary metrics
        $totalTeamCount = count($supervisedEmpIds);
        $onLeaveTodayCount = count($todayLeaves);

        $baseCountQuery = DB::table('leave_requests');
        $baseCountQuery = $this->scopeS->scopeLeaveQuery($baseCountQuery, $supervisorEmpId);

        $approvedLeaveCount = (clone $baseCountQuery)->where('status', 'approved')->count();
        $pendingLeaveCount = (clone $baseCountQuery)->where('status', 'pending')->count();

        $teamEmployees = EmployeeM::with(['user'])->active()->whereIn('id', $supervisedEmpIds)->get();

        return view('hrms.reporting.leave', compact(
            'leaveRequests',
            'todayLeaves',
            'totalTeamCount',
            'onLeaveTodayCount',
            'approvedLeaveCount',
            'pendingLeaveCount',
            'teamEmployees'
        ));
    }

    /**
     * Scoped Daily Work Reports
     */
    public function workReports(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);

        $query = DB::table('attendance_work_logs')
            ->join('employees_new as e', 'e.id', '=', 'attendance_work_logs.employee_id')
            ->leftJoin('users as eu', 'eu.id', '=', 'e.user_id')
            ->leftJoin('projects as p', 'p.id', '=', 'attendance_work_logs.project_id');

        $query = $this->scopeS->scopeWorkReports($query, $supervisorEmpId);

        if ($request->filled('date')) {
            $query->whereDate('attendance_work_logs.work_date', $request->date);
        }

        if ($request->filled('employee_id')) {
            $query->where('attendance_work_logs.employee_id', $request->employee_id);
        }

        if ($request->filled('project_id')) {
            $query->where('attendance_work_logs.project_id', $request->project_id);
        }

        $workReports = $query->select('attendance_work_logs.*', DB::raw('COALESCE(eu.name, e.employee_code) as display_name'), 'e.employee_code', 'p.name as project_name')
            ->orderByDesc('attendance_work_logs.work_date')
            ->orderByDesc('attendance_work_logs.id')
            ->paginate(20)
            ->appends($request->query());

        $teamEmployees = EmployeeM::with(['user'])->active()->whereIn('id', $supervisedEmpIds)->get();
        $teamProjects = DB::table('projects')->whereIn('id', function($q) use ($supervisedEmpIds) {
            $q->select('project_id')->from('project_assignments')->whereIn('employee_id', $supervisedEmpIds)->where('is_active', 1);
        })->get();

        return view('hrms.reporting.work_reports', compact('workReports', 'teamEmployees', 'teamProjects'));
    }

    /**
     * Scoped Projects & Tasks
     */
    public function projects(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);

        $projects = DB::table('project_assignments')
            ->join('projects', 'projects.id', '=', 'project_assignments.project_id')
            ->leftJoin('employees_new as dh', 'dh.id', '=', 'projects.delivery_head_employee_id')
            ->leftJoin('users as dhu', 'dhu.id', '=', 'dh.user_id')
            ->whereIn('project_assignments.employee_id', $supervisedEmpIds)
            ->where('project_assignments.is_active', 1)
            ->select('projects.*', DB::raw('COALESCE(projects.delivery_head_name, dhu.name, dh.employee_code) as delivery_head_name'))
            ->distinct()
            ->get();

        foreach ($projects as $prj) {
            $prj->reporting_members = DB::table('project_assignments')
                ->join('employees_new as e', 'e.id', '=', 'project_assignments.employee_id')
                ->leftJoin('users as eu', 'eu.id', '=', 'e.user_id')
                ->leftJoin('designations as ed', 'ed.id', '=', 'e.designation_id')
                ->leftJoin('project_teams as pt', 'pt.id', '=', 'project_assignments.project_team_id')
                ->where('project_assignments.project_id', $prj->id)
                ->whereIn('project_assignments.employee_id', $supervisedEmpIds)
                ->where('project_assignments.is_active', 1)
                ->select(
                    'e.id as employee_id',
                    DB::raw('COALESCE(eu.name, e.employee_code) as display_name'),
                    'e.employee_code',
                    'ed.name as designation_name',
                    'pt.team_name as team_name',
                    'project_assignments.project_role as role_name'
                )
                ->distinct()
                ->get();

            foreach ($prj->reporting_members as $mem) {
                $mem->tasks = DB::table('project_tasks')
                    ->where('project_id', $prj->id)
                    ->where('assigned_employee_id', $mem->employee_id)
                    ->select('title', 'status', 'progress_percentage')
                    ->get();
            }
        }

        return view('hrms.reporting.projects', compact('projects'));
    }

    /**
     * Reporting History Audit View
     */
    public function history(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();

        $historyQuery = DB::table('reporting_assignments')
            ->join('employees_new as e', 'e.id', '=', 'reporting_assignments.employee_id')
            ->leftJoin('users as eu', 'eu.id', '=', 'e.user_id')
            ->join('employees_new as s', 's.id', '=', 'reporting_assignments.supervisor_employee_id')
            ->leftJoin('users as su', 'su.id', '=', 's.user_id')
            ->leftJoin('designations as ed', 'ed.id', '=', 'e.designation_id')
            ->where('reporting_assignments.is_active', 0);

        if (!$this->scopeS->isSuperAdminOrGlobal() && $supervisorEmpId) {
            $historyQuery->where('reporting_assignments.supervisor_employee_id', $supervisorEmpId);
        }

        $history = $historyQuery
            ->select(
                'reporting_assignments.*',
                DB::raw('COALESCE(eu.name, e.employee_code) as employee_name'),
                'e.employee_code',
                'ed.name as designation_name',
                DB::raw('COALESCE(su.name, s.employee_code) as supervisor_name')
            )
            ->orderByDesc('reporting_assignments.end_date')
            ->paginate(30);

        return view('hrms.reporting.history', compact('history'));
    }
}
