<?php

namespace App\Http\Controllers\Web\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Services\HRMS\ProjectManagement\TechnicalLeadScopeS;
use App\Models\HRMS\ProjectManagement\TechnicalLeadAssignmentM;
use App\Models\HRMS\ProjectManagement\ProjectM;
use App\Models\HRMS\ProjectManagement\ProjectTaskM;
use App\Models\HRMS\Attendance\AttendanceM;
use App\Models\HRMS\Attendance\AttendanceWorkLogM;
use App\Models\HRMS\Leave\LeaveRequestM;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TechnicalLeadC extends Controller
{
    use HrmsCrudPage;

    public function __construct(private TechnicalLeadScopeS $scopeS)
    {
        $this->middleware('auth');
    }

    /**
     * Technical Lead Dashboard
     */
    public function dashboard(Request $request)
    {
        $tlEmpId = $this->scopeS->getOwnEmployeeId();
        $supervisedIds = $this->scopeS->getActiveSupervisedEmployeeIds();

        // Metric counts
        $today = Carbon::today()->format('Y-m-d');
        $attendanceToday = AttendanceM::whereIn('employee_id', $supervisedIds)
            ->whereDate('attendance_date', $today)
            ->get();

        $presentCount = $attendanceToday->where('status', 'present')->count();
        $wfhCount = $attendanceToday->where('work_type', 'WFH')->count();
        $wfoCount = $attendanceToday->where('work_type', 'WFO')->count();
        $lateCount = $attendanceToday->where('is_late', 1)->count();
        $absentCount = $attendanceToday->where('status', 'absent')->count();

        $onLeaveCount = LeaveRequestM::whereIn('employee_id', $supervisedIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $workReportsSubmittedToday = AttendanceWorkLogM::whereIn('employee_id', $supervisedIds)
            ->whereDate('work_date', $today)
            ->count();

        $supervisedProjectIds = $this->scopeS->getSupervisedProjectIds();
        $projectsCount = count($supervisedProjectIds);
        $developersCount = count($supervisedIds);

        // Task stats
        $tasksQuery = ProjectTaskM::query();
        $this->scopeS->scopeTasks($tasksQuery);
        $taskStats = [
            'total' => (clone $tasksQuery)->count(),
            'todo' => (clone $tasksQuery)->where('status', 'todo')->count(),
            'in_progress' => (clone $tasksQuery)->where('status', 'in_progress')->count(),
            'blocked' => (clone $tasksQuery)->where('status', 'blocked')->count(),
            'completed' => (clone $tasksQuery)->where('status', 'completed')->count(),
        ];

        // Today's Developer Roster Activity
        $recentDevelopers = EmployeeM::with(['designation', 'department', 'user'])
            ->whereIn('id', $supervisedIds)
            ->get()
            ->map(function ($emp) use ($today) {
                $att = AttendanceM::where('employee_id', $emp->id)->whereDate('attendance_date', $today)->first();
                $log = AttendanceWorkLogM::where('employee_id', $emp->id)->whereDate('work_date', $today)->first();
                $leave = LeaveRequestM::where('employee_id', $emp->id)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->first();

                // Active assigned projects & teams
                $assignedProjects = DB::table('project_assignments')
                    ->join('projects', 'projects.id', '=', 'project_assignments.project_id')
                    ->leftJoin('project_teams', 'project_teams.id', '=', 'project_assignments.project_team_id')
                    ->where('project_assignments.employee_id', $emp->id)
                    ->where('project_assignments.is_active', 1)
                    ->select('projects.name as project_name', 'projects.project_code', 'project_teams.name as team_name')
                    ->get();

                // Developer tasks stats
                $devTasks = ProjectTaskM::where('assigned_employee_id', $emp->id)->get();
                $totalDevTasks = $devTasks->count();
                $completedDevTasks = $devTasks->where('status', 'completed')->count();
                $inProgressDevTasks = $devTasks->where('status', 'in_progress')->count();

                return [
                    'employee' => $emp,
                    'projects' => $assignedProjects,
                    'attendance' => $att,
                    'work_log' => $log,
                    'leave' => $leave,
                    'total_tasks' => $totalDevTasks,
                    'completed_tasks' => $completedDevTasks,
                    'in_progress_tasks' => $inProgressDevTasks,
                ];
            });

        $pendingWorkReportsCount = max(0, $developersCount - $workReportsSubmittedToday);

        return view('hrms.technical-lead.dashboard', compact(
            'presentCount',
            'wfhCount',
            'wfoCount',
            'lateCount',
            'absentCount',
            'onLeaveCount',
            'workReportsSubmittedToday',
            'pendingWorkReportsCount',
            'projectsCount',
            'developersCount',
            'taskStats',
            'recentDevelopers'
        ));
    }

    /**
     * Technical Supervisors Summary Directory (Super Admin / HR Admin)
     */
    public function supervisors(Request $request)
    {
        $supervisorsData = DB::table('technical_lead_assignments')
            ->join('employees_new as tl', 'tl.id', '=', 'technical_lead_assignments.technical_lead_employee_id')
            ->leftJoin('designations', 'designations.id', '=', 'tl.designation_id')
            ->where('technical_lead_assignments.is_active', 1)
            ->select(
                'tl.id as tl_id',
                'tl.display_name as tl_name',
                'tl.employee_code as tl_code',
                'designations.name as designation_name',
                DB::raw('COUNT(DISTINCT technical_lead_assignments.employee_id) as developers_count')
            )
            ->groupBy('tl.id', 'tl.display_name', 'tl.employee_code', 'designations.name')
            ->get();

        $supervisorsData->transform(function ($item) {
            $memberIds = DB::table('technical_lead_assignments')
                ->where('technical_lead_employee_id', $item->tl_id)
                ->where('is_active', 1)
                ->pluck('employee_id');

            $item->projects_count = DB::table('project_assignments')
                ->whereIn('employee_id', $memberIds)
                ->where('is_active', 1)
                ->distinct('project_id')
                ->count('project_id');

            return $item;
        });

        return view('hrms.technical-lead.supervisors', compact('supervisorsData'));
    }

    /**
     * Developers Roster & Assignments
     */
    public function developers(Request $request)
    {
        $tlEmpId = $this->scopeS->getOwnEmployeeId();

        $assignmentsQuery = TechnicalLeadAssignmentM::with([
            'employee.user',
            'employee.designation',
            'technicalLead.user'
        ])->where('is_active', 1);

        if (!$this->scopeS->isSuperAdminOrGlobal() && $tlEmpId) {
            $assignmentsQuery->where('technical_lead_employee_id', $tlEmpId);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $assignmentsQuery->whereHas('employee', function($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $assignments = $assignmentsQuery->orderByDesc('id')->paginate(20)->appends($request->query());

        // Map active project details from project_assignments
        $assignments->getCollection()->transform(function ($assign) {
            $empId = $assign->employee_id;
            $assign->active_projects = DB::table('project_assignments')
                ->join('projects', 'projects.id', '=', 'project_assignments.project_id')
                ->leftJoin('project_teams', 'project_teams.id', '=', 'project_assignments.project_team_id')
                ->where('project_assignments.employee_id', $empId)
                ->where('project_assignments.is_active', 1)
                ->select(
                    'projects.name as project_name',
                    'projects.project_code',
                    'project_assignments.project_role',
                    'project_teams.name as team_name'
                )
                ->get();
            return $assign;
        });

        // Fetch historical relieved assignments for history modal
        $historyQuery = TechnicalLeadAssignmentM::with([
            'employee.user',
            'employee.designation',
            'technicalLead.user'
        ])->where('is_active', 0);

        if (!$this->scopeS->isSuperAdminOrGlobal() && $tlEmpId) {
            $historyQuery->where('technical_lead_employee_id', $tlEmpId);
        }
        $historyAssignments = $historyQuery->orderByDesc('relieved_at')->take(50)->get();

        // All employees & technical leads for dropdowns
        $employees = EmployeeM::with(['user', 'designation'])->where('status', 'active')->orderBy('display_name')->get();
        $employees->transform(function ($emp) {
            $prjAssign = DB::table('project_assignments')
                ->join('projects', 'projects.id', '=', 'project_assignments.project_id')
                ->leftJoin('project_teams', 'project_teams.id', '=', 'project_assignments.project_team_id')
                ->where('project_assignments.employee_id', $emp->id)
                ->where('project_assignments.is_active', 1)
                ->select('projects.name as project_name', 'project_teams.name as team_name', 'project_assignments.project_role')
                ->first();

            $emp->current_project_name = $prjAssign ? $prjAssign->project_name : null;
            $emp->current_team_name = $prjAssign ? $prjAssign->team_name : null;
            $emp->current_role = $prjAssign ? $prjAssign->project_role : null;

            // Check current active TL
            $activeTL = DB::table('technical_lead_assignments')
                ->join('employees_new as tl', 'tl.id', '=', 'technical_lead_assignments.technical_lead_employee_id')
                ->where('technical_lead_assignments.employee_id', $emp->id)
                ->where('technical_lead_assignments.is_active', 1)
                ->select('tl.display_name')
                ->first();

            $emp->current_tl_name = $activeTL ? $activeTL->display_name : null;
            return $emp;
        });

        $technicalLeads = EmployeeM::with(['user', 'designation'])->where('status', 'active')->orderBy('display_name')->get();

        return view('hrms.technical-lead.developers', compact('assignments', 'historyAssignments', 'employees', 'technicalLeads', 'tlEmpId'));
    }

    /**
     * Assign developer to Technical Lead
     */
    public function assignDeveloper(Request $request)
    {
        $validated = $request->validate([
            'technical_lead_employee_id' => 'required|exists:employees_new,id',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees_new,id',
            'assigned_at' => 'nullable|date',
        ]);

        $assignedAt = !empty($validated['assigned_at']) ? Carbon::parse($validated['assigned_at']) : now();

        $count = 0;
        foreach ($validated['employee_ids'] as $empId) {
            if ($empId == $validated['technical_lead_employee_id']) continue;
            $this->scopeS->assignDeveloper([
                'technical_lead_employee_id' => $validated['technical_lead_employee_id'],
                'employee_id' => $empId,
                'assigned_at' => $assignedAt,
            ]);
            $count++;
        }

        return back()->with('success', "{$count} developer(s) assigned under Technical Lead supervision successfully.");
    }

    /**
     * Relieve developer from Technical Lead supervision
     */
    public function relieveDeveloper($id)
    {
        $this->scopeS->relieveDeveloper((int) $id);
        return back()->with('success', 'Developer relieved from Technical Lead supervision.');
    }

    /**
     * Technical Lead Attendance View
     */
    public function attendance(Request $request)
    {
        $query = AttendanceM::with(['employee.user', 'employee.designation']);
        $this->scopeS->scopeAttendanceQuery($query);

        if ($request->filled('from')) {
            $query->whereDate('attendance_date', '>=', $request->from);
        } else {
            $query->whereDate('attendance_date', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'));
        }

        if ($request->filled('to')) {
            $query->whereDate('attendance_date', '<=', $request->to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('work_type')) {
            $query->where('work_type', $request->work_type);
        }

        $attendances = $query->orderByDesc('attendance_date')->orderByDesc('id')->paginate(25)->appends($request->query());

        return view('hrms.technical-lead.attendance', compact('attendances'));
    }

    /**
     * Technical Lead Leave View
     */
    public function leave(Request $request)
    {
        $query = LeaveRequestM::with(['employee.user', 'employee.designation', 'leaveType']);
        $this->scopeS->scopeLeaveQuery($query);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('start_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('end_date', '<=', $request->to);
        }

        $leaveRequests = $query->orderByDesc('id')->paginate(25)->appends($request->query());

        return view('hrms.technical-lead.leave', compact('leaveRequests'));
    }

    /**
     * Technical Lead Daily Work Reports View
     */
    public function workReports(Request $request)
    {
        $query = AttendanceWorkLogM::with(['employee.user', 'employee.designation']);
        $this->scopeS->scopeWorkReports($query);

        if ($request->filled('from')) {
            $query->whereDate('work_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('work_date', '<=', $request->to);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $workLogs = $query->orderByDesc('work_date')->orderByDesc('id')->paginate(25)->appends($request->query());
        $projects = ProjectM::where('status', 'active')->orderBy('name')->get();

        return view('hrms.technical-lead.work-reports', compact('workLogs', 'projects'));
    }

    /**
     * Technical Lead Supervised Projects View
     */
    public function projects(Request $request)
    {
        $projectIds = $this->scopeS->getSupervisedProjectIds();

        $projects = ProjectM::with([
            'deliveryHead.user',
            'teams.teamLead.user',
            'activeAssignments.employee.user',
            'tasks'
        ])->whereIn('id', $projectIds)
          ->orderBy('name')
          ->get();

        return view('hrms.technical-lead.projects', compact('projects'));
    }
}
