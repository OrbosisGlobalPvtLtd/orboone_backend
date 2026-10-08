<?php

namespace App\Http\Controllers\Web\Reporting;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Attendance\AttendancesC;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Reporting\ReportingAssignmentM;
use App\Services\HRMS\Reporting\ReportingScopeS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\HRMS\Leave\LeaveTypeM;

class ReportingC extends Controller
{
    protected ReportingScopeS $scopeS;
    protected AttendancesC $attendancesC;

    public function __construct(ReportingScopeS $scopeS, AttendancesC $attendancesC)
    {
        $this->scopeS = $scopeS;
        $this->attendancesC = $attendancesC;
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
        if ($isGlobal && empty($supervisedEmpIds)) {
            $employeesCount = EmployeeM::active()->count();
        }

        $supervisorsCount = DB::table('reporting_assignments')->where('is_active', 1)->distinct('supervisor_employee_id')->count('supervisor_employee_id')
            + DB::table('technical_lead_assignments')->where('is_active', 1)->distinct('employee_id')->count('employee_id');

        $projectsQuery = DB::table('projects');
        if (Schema::hasColumn('projects', 'is_active')) {
            $projectsQuery->where('is_active', 1);
        } elseif (Schema::hasColumn('projects', 'status')) {
            $projectsQuery->where('status', 'active');
        }
        $projectsCount = $projectsQuery->count();

        // Today's Attendance stats
        $today = date('Y-m-d');
        $attendanceQuery = DB::table('attendances')->whereDate('attendance_date', $today);
        $attendanceQuery = $this->scopeS->scopeAttendanceQuery($attendanceQuery, $supervisorEmpId);
        $todayAttendances = $attendanceQuery->get();

        $presentCount = $todayAttendances->whereIn('attendance_status', ['present', 'late', 'half_day'])->count();
        $wfhCount = $todayAttendances->where('work_mode', 'wfh')->count();
        $absentCount = $todayAttendances->where('attendance_status', 'absent')->count();

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

        // Combined Tasks Aggregation (project_tasks + taskmanagement + attendance_work_logs)
        $ptTasks = DB::table('project_tasks');
        $ptTasks = $this->scopeS->scopeTasks($ptTasks, $supervisorEmpId)->get();

        $teamUserIds = DB::table('employees_new')->whereIn('id', $supervisedEmpIds)->pluck('user_id')->filter()->toArray();
        if (Auth::user()) {
            $teamUserIds[] = Auth::user()->id;
        }
        $tmTasks = DB::table('taskmanagement')->whereIn('user_id', array_unique($teamUserIds))->get();

        $workLogsQuery = DB::table('attendance_work_logs');
        $workLogsQuery = $this->scopeS->scopeWorkReports($workLogsQuery, $supervisorEmpId)->get();

        $totalTasks = 0;
        $completedTasks = 0;
        $inProgressTasks = 0;
        $todoTasks = 0;
        $blockedTasks = 0;

        foreach ($ptTasks as $t) {
            $totalTasks++;
            $st = strtolower($t->status ?? 'todo');
            if (in_array($st, ['completed', 'done'])) $completedTasks++;
            elseif (in_array($st, ['in_progress', 'doing', 'testing'])) $inProgressTasks++;
            elseif (in_array($st, ['blocked', 'hold'])) $blockedTasks++;
            else $todoTasks++;
        }

        foreach ($tmTasks as $t) {
            $totalTasks++;
            $st = strtolower($t->status ?? 'pending');
            if (in_array($st, ['completed', 'done'])) $completedTasks++;
            elseif (in_array($st, ['in_progress', 'doing', 'testing'])) $inProgressTasks++;
            elseif (in_array($st, ['blocked', 'hold'])) $blockedTasks++;
            else $todoTasks++;
        }

        foreach ($workLogsQuery as $wl) {
            if (!empty($wl->work_summary_json)) {
                $data = json_decode($wl->work_summary_json, true);
                if (isset($data['projects']) && is_array($data['projects'])) {
                    foreach ($data['projects'] as $p) {
                        if (isset($p['tasks']) && is_array($p['tasks'])) {
                            foreach ($p['tasks'] as $t) {
                                $totalTasks++;
                                $isComp = !empty($t['completed']) || !empty($t['is_completed']);
                                if ($isComp) {
                                    $completedTasks++;
                                } else {
                                    $inProgressTasks++;
                                }
                            }
                        }
                    }
                }
            }
        }

        $taskStats = [
            'total' => $totalTasks,
            'completed' => $completedTasks,
            'in_progress' => $inProgressTasks,
            'todo' => $todoTasks,
            'blocked' => $blockedTasks,
        ];

        // Developer daily status list with server-side search and pagination
        $perPage = (int) $request->input('per_page', 25);
        $search = trim($request->input('search', ''));

        $query = EmployeeM::with(['user', 'designation', 'department'])
            ->whereIn('id', $supervisedEmpIds);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('designation', function($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('department', function($dpq) use ($search) {
                      $dpq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $recentDevelopersList = $query->paginate($perPage)->appends($request->query());

        $recentDevelopersList->getCollection()->transform(function ($emp) use ($today, $todayAttendances, $ptTasks, $tmTasks, $workLogsQuery) {
            $att = $todayAttendances->firstWhere('employee_id', $emp->id);
            $lve = DB::table('leave_requests')
                ->where('employee_id', $emp->id)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->where('status', 'approved')
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

            $empTotTasks = 0;
            $empCompTasks = 0;

            $empPt = $ptTasks->where('assigned_employee_id', $emp->id);
            $empTotTasks += $empPt->count();
            $empCompTasks += $empPt->whereIn('status', ['completed', 'done'])->count();

            if ($emp->user_id) {
                $empTm = $tmTasks->where('user_id', $emp->user_id);
                $empTotTasks += $empTm->count();
                $empCompTasks += $empTm->whereIn('status', ['completed', 'done'])->count();
            }

            $empWlogs = $workLogsQuery->where('employee_id', $emp->id);
            foreach ($empWlogs as $wl) {
                if (!empty($wl->work_summary_json)) {
                    $data = json_decode($wl->work_summary_json, true);
                    if (isset($data['projects']) && is_array($data['projects'])) {
                        foreach ($data['projects'] as $p) {
                            if (isset($p['tasks']) && is_array($p['tasks'])) {
                                foreach ($p['tasks'] as $t) {
                                    $empTotTasks++;
                                    if (!empty($t['completed']) || !empty($t['is_completed'])) {
                                        $empCompTasks++;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return [
                'employee' => $emp,
                'attendance' => $att,
                'leave' => $lve,
                'work_log' => $wlog,
                'projects' => $prjs,
                'total_tasks' => $empTotTasks,
                'completed_tasks' => $empCompTasks,
            ];
        });

        $recentDevelopers = $recentDevelopersList;

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

        $perPage = (int) $request->input('per_page', 25);
        if ($perPage < 5 || $perPage > 200) {
            $perPage = 25;
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
            ->paginate($perPage)
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

        $activeSupervisorIds = DB::table('reporting_assignments')
            ->where('is_active', 1)
            ->pluck('supervisor_employee_id')
            ->merge(DB::table('employees_new')->whereNotNull('reporting_manager_employee_id')->pluck('reporting_manager_employee_id'))
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        $supervisors = EmployeeM::with(['user', 'designation', 'department'])
            ->whereIn('id', $activeSupervisorIds)
            ->active()
            ->orderBy('id')
            ->get();

        $allSupervisors = EmployeeM::with(['user', 'designation', 'department'])->active()->orderBy('id')->get();
        $isAdminOrHR = $this->scopeS->isSuperAdminOrGlobal();

        return view('hrms.reporting.assignments', compact('assignments', 'employees', 'supervisors', 'allSupervisors', 'selectedSupervisor', 'isAdminOrHR'));
    }

    /**
     * Process Supervisor & Employee Assignment / Transfer
     */
    public function assignSupervisor(Request $request)
    {
        if (!$this->scopeS->isSuperAdminOrGlobal()) {
            abort(403, 'Unauthorized. Only HR Admin / Super Admin can assign reporting managers.');
        }

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
    public function relieveEmployee(Request $request, int|string $id)
    {
        if (!$this->scopeS->isSuperAdminOrGlobal()) {
            abort(403, 'Unauthorized. Only HR Admin / Super Admin can relieve reporting assignments.');
        }

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

        // Fetch all supervised employees for metrics & summary stats
        $allSupervised = EmployeeM::with(['user', 'designation', 'department', 'reportingManager.user'])
            ->active()
            ->whereIn('id', $supervisedEmpIds)
            ->get();

        $projectAssignCounts = DB::table('project_assignments')
            ->whereIn('employee_id', $supervisedEmpIds)
            ->where('is_active', 1)
            ->pluck('employee_id')
            ->toArray();

        $projectEmpIdCounts = array_count_values($projectAssignCounts);

        // Calculate dynamic summary stats across supervised team
        $summaryStats = [
            'total' => $allSupervised->count(),
            'reporting_team' => 0,
            'project_team' => 0,
            'both' => 0,
            'paid' => 0,
            'unpaid' => 0,
            'interns' => 0,
        ];

        foreach ($allSupervised as $emp) {
            $isReporting = in_array((int)$emp->id, $allReportingEmpIds, true);
            $hasProject = isset($projectEmpIdCounts[$emp->id]) && $projectEmpIdCounts[$emp->id] > 0;

            if ($isReporting && $hasProject) {
                $summaryStats['both']++;
                $summaryStats['reporting_team']++;
                $summaryStats['project_team']++;
            } elseif ($isReporting) {
                $summaryStats['reporting_team']++;
            } elseif ($hasProject) {
                $summaryStats['project_team']++;
            }

            $stageRaw = strtolower($emp->employee_stage ?? '');
            $typeRaw = strtolower($emp->employment_type ?? '');
            $isIntern = ($stageRaw === 'internship' || $typeRaw === 'intern');
            $isPaidIntern = $emp->is_paid_intern ?? null;

            if ($isIntern) {
                $summaryStats['interns']++;
                if ($isPaidIntern === 1 || $isPaidIntern === true) {
                    $summaryStats['paid']++;
                } else {
                    $summaryStats['unpaid']++;
                }
            } else {
                $summaryStats['paid']++;
            }
        }

        // Dynamic Per-Page and Query Filters
        $perPage = (int) $request->input('per_page', 25);
        if ($perPage < 5 || $perPage > 200) {
            $perPage = 25;
        }

        $query = EmployeeM::with(['user', 'designation', 'department', 'reportingManager.user'])
            ->active()
            ->whereIn('id', $supervisedEmpIds);

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('employee_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('designation', function ($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('department', function ($dpq) use ($search) {
                      $dpq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Department Filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        // Designation Filter
        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->input('designation_id'));
        }

        // Reporting Manager Filter
        if ($request->filled('manager_id')) {
            $query->where('reporting_manager_employee_id', $request->input('manager_id'));
        }

        // Employment Stage / Type Filter
        if ($request->filled('employment')) {
            $empType = strtolower(trim($request->input('employment')));
            if ($empType === 'internship' || $empType === 'intern') {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(employee_stage) = ?', ['internship'])
                      ->orWhereRaw('LOWER(employment_type) = ?', ['intern'])
                      ->orWhereRaw('LOWER(employment_type) = ?', ['internship']);
                });
            } elseif ($empType === 'permanent') {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(employee_stage) = ?', ['permanent'])
                      ->orWhereRaw('LOWER(employment_type) = ?', ['permanent'])
                      ->orWhere(function($sq) {
                          $sq->whereNull('employee_stage')->whereNull('employment_type');
                      });
                });
            } elseif ($empType === 'probation') {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(employee_stage) = ?', ['probation'])
                      ->orWhereRaw('LOWER(employment_type) = ?', ['probation'])
                      ->orWhereIn('probation_status', ['pending', 'ongoing']);
                });
            } elseif ($empType === 'contract') {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(employee_stage) = ?', ['contract'])
                      ->orWhereRaw('LOWER(employment_type) = ?', ['contract']);
                });
            } elseif ($empType === 'freelance' || $empType === 'freelancer') {
                $query->where(function ($q) {
                    $q->whereRaw('LOWER(employee_stage) = ?', ['freelance'])
                      ->orWhereRaw('LOWER(employment_type) = ?', ['freelancer'])
                      ->orWhereRaw('LOWER(employment_type) = ?', ['freelance']);
                });
            }
        }

        // Pay Status Filter
        if ($request->filled('pay_status')) {
            $payStatus = strtolower(trim($request->input('pay_status')));
            if ($payStatus === 'unpaid') {
                $query->where(function ($q) {
                    $q->where('is_paid_intern', 0)
                      ->orWhere('is_paid_intern', false);
                });
            } elseif ($payStatus === 'paid') {
                $query->where(function ($q) {
                    $q->where('is_paid_intern', 1)
                      ->orWhere('is_paid_intern', true)
                      ->orWhereNull('is_paid_intern');
                });
            }
        }

        // Project Filter
        if ($request->filled('project_id')) {
            $prjId = $request->input('project_id');
            $prjEmpIds = DB::table('project_assignments')
                ->where('project_id', $prjId)
                ->where('is_active', 1)
                ->pluck('employee_id')
                ->toArray();
            $query->whereIn('id', $prjEmpIds);
        }

        // Team Source Filter (Reporting Team, Project Team, Both)
        if ($request->filled('team_source')) {
            $teamSource = trim($request->input('team_source'));
            $allProjectEmpIds = DB::table('project_assignments')
                ->whereIn('employee_id', $supervisedEmpIds)
                ->where('is_active', 1)
                ->pluck('employee_id')
                ->unique()
                ->toArray();

            if ($teamSource === 'Both') {
                $bothIds = array_intersect($allReportingEmpIds, $allProjectEmpIds);
                $query->whereIn('id', $bothIds);
            } elseif ($teamSource === 'Reporting Team' || $teamSource === 'Reporting') {
                $query->whereIn('id', $allReportingEmpIds);
            } elseif ($teamSource === 'Project Team' || $teamSource === 'Project') {
                $query->whereIn('id', $allProjectEmpIds);
            }
        }

        // Paginated employees query
        $employees = $query->paginate($perPage)->appends($request->query());

        $employees->getCollection()->transform(function ($emp) use ($allReportingEmpIds) {
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

            // Internship Period formatting
            $startDate = $emp->internship_start_date ?? null;
            $endDate = $emp->internship_extended_to ?? $emp->internship_end_date ?? null;
            if ($startDate && $endDate) {
                $emp->internship_period = Carbon::parse($startDate)->format('d M Y') . ' – ' . Carbon::parse($endDate)->format('d M Y');
            } elseif ($startDate) {
                $emp->internship_period = 'From ' . Carbon::parse($startDate)->format('d M Y');
            } else {
                $emp->internship_period = '—';
            }

            return $emp;
        });

        // Dropdown lists for advanced filters
        $departments = DB::table('departments')->where('is_active', 1)->orderBy('name')->get();
        $designations = DB::table('designations')->where('is_active', 1)->orderBy('name')->get();
        
        $projectsQuery = DB::table('projects');
        if (Schema::hasColumn('projects', 'status')) {
            $projectsQuery->where('status', 'active');
        } elseif (Schema::hasColumn('projects', 'is_active')) {
            $projectsQuery->where('is_active', 1);
        }
        $projects = $projectsQuery->orderBy('name')->get();
        
        $managerIds = $allSupervised->pluck('reporting_manager_employee_id')->filter()->unique()->toArray();
        $reportingManagers = EmployeeM::with('user')->whereIn('id', $managerIds)->get();

        return view('hrms.reporting.my_employees', compact(
            'employees',
            'summaryStats',
            'departments',
            'designations',
            'projects',
            'reportingManagers'
        ));
    }

    /**
     * Scoped Team Attendance (Unified with Centralized Team Attendance)
     */
    public function attendance(Request $request)
    {
        return $this->attendancesC->teamAttendance($request);
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
            ->leftJoin('leave_types as lt', 'lt.id', '=', 'leave_requests.leave_type_id')
            ->leftJoin('users as mu', 'mu.id', '=', 'leave_requests.manager_approved_by')
            ->leftJoin('users as hru', 'hru.id', '=', 'leave_requests.hr_approved_by')
            ->leftJoin('employees_new as rm', 'rm.id', '=', 'e.reporting_manager_employee_id')
            ->leftJoin('users as rmu', 'rmu.id', '=', 'rm.user_id')
            ->leftJoin('users as reju', 'reju.id', '=', 'leave_requests.approved_by_user_id');

        $query = $this->scopeS->scopeLeaveQuery($query, $supervisorEmpId);

        if ($request->filled('employee_id')) {
            $query->where('leave_requests.employee_id', $request->employee_id);
        }

        if ($request->filled('reporting_manager_id')) {
            $query->where('e.reporting_manager_employee_id', $request->reporting_manager_id);
        }

        if ($request->filled('status')) {
            $st = strtolower($request->status);
            if ($st === 'pending_manager') {
                $query->where('leave_requests.status', 'pending')
                    ->whereNotNull('e.reporting_manager_employee_id')
                    ->whereNull('leave_requests.manager_approved_at');
            } elseif ($st === 'pending_hr') {
                $query->where('leave_requests.status', 'pending')
                    ->where(function ($q) {
                        $q->whereNull('e.reporting_manager_employee_id')
                            ->orWhereNotNull('leave_requests.manager_approved_at')
                            ->orWhere('leave_requests.approval_level', 'manager_approved');
                    });
            } elseif ($st === 'all') {

            } else {
                $query->where('leave_requests.status', $st);
            }
        } else {
            $query->where('leave_requests.status', 'pending');
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_requests.leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('leave_requests.start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('leave_requests.end_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('eu.name', 'like', $search)
                    ->orWhere('e.employee_code', 'like', $search);
            });
        }

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        // Handle Full Dataset Export (CSV / Excel / JSON)
        if ($request->filled('export')) {
            $exportType = strtolower($request->input('export'));
            $allQuery = clone $query;
            $allLeaves = $allQuery->select(
                'leave_requests.*',
                'e.reporting_manager_employee_id as current_reporting_manager_id',
                DB::raw('COALESCE(eu.name, e.employee_code) as display_name'),
                'e.employee_code',
                'd.name as designation_name',
                'dept.name as department_name',
                'lt.name as leave_type_name',
                'mu.name as manager_approver_name',
                'hru.name as hr_approver_name',
                'rmu.name as reporting_manager_name',
                'reju.name as rejected_by_name'
            )
                ->orderByDesc('leave_requests.id')
                ->get();

            if ($exportType === 'json') {
                $formattedData = [];
                foreach ($allLeaves as $idx => $req) {
                    $stLower = strtolower(trim($req->status ?? 'pending'));
                    $mgrApproved = !empty($req->manager_approved_by) || !empty($req->manager_approved_at) || ($req->approval_level === 'manager_approved');
                    $mgrRejected = ($stLower === 'rejected' && empty($req->manager_approved_by));
                    $hrApproved = ($stLower === 'approved');
                    $hrRejected = ($stLower === 'rejected' && !empty($req->manager_approved_by));

                    if ($hrApproved) {
                        $managerStatus = $mgrApproved ? ('Approved' . ($req->manager_approver_name ? ' by ' . $req->manager_approver_name : '')) : 'Not Required';
                        $hrStatus = 'Approved' . ($req->hr_approver_name ? ' by ' . $req->hr_approver_name : '');
                    } elseif ($mgrApproved) {
                        $managerStatus = 'Approved' . ($req->manager_approver_name ? ' by ' . $req->manager_approver_name : '');
                        $hrStatus = 'Pending HR';
                    } elseif ($mgrRejected) {
                        $managerStatus = 'Rejected' . ($req->rejected_by_name ? ' by ' . $req->rejected_by_name : '');
                        $hrStatus = '—';
                    } elseif ($hrRejected) {
                        $managerStatus = 'Approved' . ($req->manager_approver_name ? ' by ' . $req->manager_approver_name : '');
                        $hrStatus = 'Rejected' . ($req->rejected_by_name ? ' by ' . $req->rejected_by_name : '');
                    } else {
                        $managerStatus = 'Pending';
                        $hrStatus = 'Pending';
                    }

                    $period = Carbon::parse($req->start_date)->format('d M Y');
                    if ($req->start_date !== $req->end_date) {
                        $period .= ' -> ' . Carbon::parse($req->end_date)->format('d M Y');
                    }

                    $daysVal = (float)($req->requested_days ?? $req->deducted_days ?? 1);
                    $daysStr = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . Str::plural('Day', $daysVal);

                    $formattedData[] = [
                        'sr_no' => $idx + 1,
                        'employee_name' => $req->display_name ?? 'Employee',
                        'employee_code' => $req->employee_code ?? '',
                        'designation' => $req->designation_name ?? '',
                        'department' => $req->department_name ?? '',
                        'reporting_manager' => $req->reporting_manager_name ?: '—',
                        'leave_type' => $req->leave_type_name ?? 'Leave',
                        'leave_period' => $period,
                        'days' => $daysStr,
                        'manager_approval' => $managerStatus,
                        'hr_approval' => $hrStatus,
                        'overall_status' => strtoupper($req->status ?? 'pending'),
                        'reason' => $req->reason ?? '',
                    ];
                }

                return response()->json([
                    'success' => true,
                    'total' => count($formattedData),
                    'data' => $formattedData,
                ]);
            }

            if ($exportType === 'csv') {
                return $this->exportTeamLeaveCsv($allLeaves);
            }

            if ($exportType === 'excel') {
                return $this->exportTeamLeaveExcel($allLeaves);
            }
        }

        $leaveRequests = $query->select(
            'leave_requests.*',
            'e.reporting_manager_employee_id as current_reporting_manager_id',
            DB::raw('COALESCE(eu.name, e.employee_code) as display_name'),
            'e.employee_code',
            'd.name as designation_name',
            'dept.name as department_name',
            'lt.name as leave_type_name',
            'mu.name as manager_approver_name',
            'hru.name as hr_approver_name',
            'rmu.name as reporting_manager_name',
            'reju.name as rejected_by_name'
        )
            ->orderByDesc('leave_requests.id')
            ->paginate($perPage)
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

        $baseCountQuery = DB::table('leave_requests')
            ->join('employees_new as e', 'e.id', '=', 'leave_requests.employee_id');
        $baseCountQuery = $this->scopeS->scopeLeaveQuery($baseCountQuery, $supervisorEmpId);

        $totalPendingCount = (clone $baseCountQuery)->where('leave_requests.status', 'pending')->count();
        $managerPendingCount = (clone $baseCountQuery)
            ->where('leave_requests.status', 'pending')
            ->whereNotNull('e.reporting_manager_employee_id')
            ->whereNull('leave_requests.manager_approved_at')
            ->count();
        $hrPendingCount = (clone $baseCountQuery)
            ->where('leave_requests.status', 'pending')
            ->where(function ($q) {
                $q->whereNull('e.reporting_manager_employee_id')
                    ->orWhereNotNull('leave_requests.manager_approved_at')
                    ->orWhere('leave_requests.approval_level', 'manager_approved');
            })
            ->count();
        $approvedLeaveCount = (clone $baseCountQuery)->where('leave_requests.status', 'approved')->count();
        $rejectedLeaveCount = (clone $baseCountQuery)->where('leave_requests.status', 'rejected')->count();

        $isGlobal = $this->scopeS->isSuperAdminOrGlobal();

        if ($isGlobal) {
            $employees = EmployeeM::with(['user'])->active()->get();
        } else {
            $employees = EmployeeM::with(['user'])->active()->whereIn('id', $supervisedEmpIds)->get();
        }
        $teamEmployees = $employees;

        $leaveTypes = LeaveTypeM::orderBy('name')->get();
        $reportingManagers = EmployeeM::whereIn('id', function ($q) {
            $q->select('reporting_manager_employee_id')->from('employees_new')->whereNotNull('reporting_manager_employee_id');
        })->with(['user'])->get();

        return view('hrms.reporting.leave', compact(
            'leaveRequests',
            'todayLeaves',
            'totalTeamCount',
            'onLeaveTodayCount',
            'totalPendingCount',
            'managerPendingCount',
            'hrPendingCount',
            'approvedLeaveCount',
            'rejectedLeaveCount',
            'teamEmployees',
            'employees',
            'leaveTypes',
            'reportingManagers',
            'supervisorEmpId'
        ));
    }

    /**
     * Scoped Daily Work Reports
     */
    public function workReports(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);
        $isSuperAdminOrGlobal = $this->scopeS->isSuperAdminOrGlobal();

        if ($isSuperAdminOrGlobal) {
            $accessibleEmpIds = EmployeeM::active()->pluck('id')->toArray();
        } else {
            $accessibleEmpIds = $supervisedEmpIds;
        }

        $query = DB::table('attendance_work_logs')
            ->join('employees_new as e', 'e.id', '=', 'attendance_work_logs.employee_id')
            ->leftJoin('users as eu', 'eu.id', '=', 'e.user_id')
            ->leftJoin('projects as p', 'p.id', '=', 'attendance_work_logs.project_id')
            ->leftJoin('departments as d', 'd.id', '=', 'e.department_id')
            ->leftJoin('designations as des', 'des.id', '=', 'e.designation_id')
            ->leftJoin('attendances as att', 'att.id', '=', 'attendance_work_logs.attendance_id')
            ->leftJoin('attendance_times as at_time', 'at_time.id', '=', 'att.attendance_time_id');

        if (!$isSuperAdminOrGlobal) {
            if (!empty($accessibleEmpIds)) {
                $query->whereIn('attendance_work_logs.employee_id', $accessibleEmpIds);
            } else {
                $query->whereRaw('0 = 1');
            }
        }

        // 5. Month & Date Scope Handling
        $months = [];
        $currentDateObj = Carbon::now();
        for ($i = 0; $i < 12; $i++) {
            $mObj = $currentDateObj->copy()->subMonths($i);
            $months[$mObj->format('Y-m')] = $mObj->format('F Y');
        }

        $selectedMonth = $request->input('month', date('Y-m'));

        // Apply Month / Date Filter
        if ($request->filled('date')) {
            $query->whereDate('attendance_work_logs.work_date', $request->date);
        } elseif ($request->filled('from_date') || $request->filled('to_date')) {
            if ($request->filled('from_date')) {
                $query->whereDate('attendance_work_logs.work_date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('attendance_work_logs.work_date', '<=', $request->to_date);
            }
        } elseif ($selectedMonth && $selectedMonth !== 'all' && $selectedMonth !== 'custom') {
            $mParts = explode('-', $selectedMonth);
            if (count($mParts) === 2) {
                $query->whereYear('attendance_work_logs.work_date', $mParts[0])
                      ->whereMonth('attendance_work_logs.work_date', $mParts[1]);
            }
        }

        if ($request->filled('work_mode')) {
            $query->where('att.work_mode', strtoupper($request->work_mode));
        }

        if ($request->filled('employee_id')) {
            $query->where('attendance_work_logs.employee_id', $request->employee_id);
        }

        if ($request->filled('project_id')) {
            $query->where('attendance_work_logs.project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('attendance_work_logs.work_summary', 'LIKE', $search)
                  ->orWhere('attendance_work_logs.work_summary_json', 'LIKE', $search)
                  ->orWhere('eu.name', 'LIKE', $search)
                  ->orWhere('e.employee_code', 'LIKE', $search)
                  ->orWhere('p.name', 'LIKE', $search);
            });
        }

        // Summary Stats based on filtered query before pagination
        $statsBaseQuery = clone $query;
        $totalReportsCount = (clone $statsBaseQuery)->count();
        $todayReportsCount = (clone $statsBaseQuery)->whereDate('attendance_work_logs.work_date', Carbon::today()->toDateString())->count();
        $wfoReportsCount = (clone $statsBaseQuery)->where('att.work_mode', 'WFO')->count();
        $wfhReportsCount = (clone $statsBaseQuery)->where('att.work_mode', 'WFH')->count();
        $totalGrossMinutes = (clone $statsBaseQuery)->sum('att.gross_work_minutes') ?? 0;
        $totalGrossHours = round($totalGrossMinutes / 60, 1);

        $stats = [
            'total' => $totalReportsCount,
            'today' => $todayReportsCount,
            'wfo' => $wfoReportsCount,
            'wfh' => $wfhReportsCount,
            'gross_hours' => $totalGrossHours,
            'selected_month' => $selectedMonth,
            'months' => $months,
        ];

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 25;
        }

        $workReports = $query->select(
            'attendance_work_logs.*',
            DB::raw('COALESCE(eu.name, e.employee_code) as display_name'),
            'e.employee_code',
            'p.name as project_name',
            'd.name as department_name',
            'des.name as designation_name',
            'att.attendance_status',
            'att.is_lwp',
            'att.work_mode',
            'att.gross_work_minutes',
            'att.punch_in_time',
            'att.punch_out_time',
            'at_time.name as shift_name'
        );

        // Handle Full Dataset Export (CSV / Excel / JSON)
        if ($request->filled('export')) {
            $exportType = strtolower($request->input('export'));
            $allQuery = clone $query;
            $allReports = $allQuery->orderByDesc('attendance_work_logs.work_date')
                ->orderByDesc('attendance_work_logs.id')
                ->get();

            if ($exportType === 'json') {
                $formattedData = [];
                foreach ($allReports as $idx => $item) {
                    $row = formatWorkReportRow($item);
                    $formattedData[] = [
                        'sr_no' => $idx + 1,
                        'employee_name' => $row['employee_name'] ?? '',
                        'employee_code' => $row['employee_code'] ?? '',
                        'designation' => $row['designation'] ?? '',
                        'department' => $row['department'] ?? '',
                        'date' => $row['date'] ?? '',
                        'day_name' => $row['day_name'] ?? '',
                        'mode' => $row['mode'] ?? 'WFO',
                        'shift_context' => $row['shift_context'] ?? 'General Shift',
                        'gross_work' => $row['gross_work'] ?? '0h 0m',
                        'work_summary' => $row['summary_desc'] ?? '',
                        'structured_tasks' => !empty($row['structured_tasks']) ? array_map(function($t) {
                            return ($t['done'] ? '✓ ' : '• ') . $t['text'];
                        }, $row['structured_tasks']) : [],
                    ];
                }
                return response()->json([
                    'success' => true,
                    'total' => count($formattedData),
                    'data' => $formattedData,
                ]);
            }

            if ($exportType === 'csv') {
                return $this->exportWorkReportsCsv($allReports);
            }

            if ($exportType === 'excel') {
                return $this->exportWorkReportsExcel($allReports);
            }
        }

        $workReports = $query
            ->orderByDesc('attendance_work_logs.work_date')
            ->orderByDesc('attendance_work_logs.id')
            ->paginate($perPage)
            ->appends($request->query());

        $teamEmployees = EmployeeM::with(['user', 'department', 'designation', 'position'])
            ->active()
            ->whereIn('id', $accessibleEmpIds)
            ->get()
            ->sortBy(function ($emp) {
                return strtolower($emp->display_name ?? $emp->employee_code);
            })
            ->values();

        $teamProjectsQuery = DB::table('projects')
            ->where(function ($q) use ($accessibleEmpIds, $isSuperAdminOrGlobal) {
                if (!$isSuperAdminOrGlobal && !empty($accessibleEmpIds)) {
                    $q->whereIn('id', function ($sub) use ($accessibleEmpIds) {
                        $sub->select('project_id')
                            ->from('project_assignments')
                            ->whereIn('employee_id', $accessibleEmpIds)
                            ->where('is_active', 1);
                    });
                }
            });

        if (Schema::hasColumn('projects', 'status')) {
            $teamProjectsQuery->where('status', '!=', 'archived');
        } elseif (Schema::hasColumn('projects', 'is_active')) {
            $teamProjectsQuery->where('is_active', 1);
        }

        $teamProjects = $teamProjectsQuery->orderBy('name')->get();

        if ($isSuperAdminOrGlobal && $teamProjects->isEmpty()) {
            $fallbackQuery = DB::table('projects');
            if (Schema::hasColumn('projects', 'status')) {
                $fallbackQuery->where('status', '!=', 'archived');
            } elseif (Schema::hasColumn('projects', 'is_active')) {
                $fallbackQuery->where('is_active', 1);
            }
            $teamProjects = $fallbackQuery->orderBy('name')->get();
        }

        return view('hrms.reporting.work_reports', compact('workReports', 'teamEmployees', 'teamProjects', 'stats', 'months'));
    }

    /**
     * Export all filtered work reports as CSV
     */
    protected function exportWorkReportsCsv($reports)
    {
        $filename = 'Daily_Work_Reports_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($reports) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                '#',
                'Employee Name',
                'Employee Code',
                'Designation',
                'Department',
                'Work Date',
                'Day',
                'Work Mode',
                'Shift Context',
                'Gross Work Hours',
                'Work Summary Description',
                'Structured Tasks',
            ]);

            foreach ($reports as $index => $report) {
                $row = formatWorkReportRow($report);
                $tasksStr = '';
                if (!empty($row['structured_tasks'])) {
                    $tasksList = array_map(fn($t) => ($t['done'] ? '[Done] ' : '[Pending] ') . $t['text'], $row['structured_tasks']);
                    $tasksStr = implode("\n", $tasksList);
                }

                fputcsv($handle, [
                    $index + 1,
                    $row['employee_name'] ?? '',
                    $row['employee_code'] ?? '',
                    $row['designation'] ?? '',
                    $row['department'] ?? '',
                    $row['date'] ?? '',
                    $row['day_name'] ?? '',
                    $row['mode'] ?? '',
                    $row['shift_context'] ?? '',
                    $row['gross_work'] ?? '',
                    $row['summary_desc'] ?? '',
                    $tasksStr,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export all filtered work reports as Excel (HTML Spreadsheet compatible)
     */
    protected function exportWorkReportsExcel($reports)
    {
        $filename = 'Daily_Work_Reports_' . date('Y-m-d_His') . '.xls';
        
        $output = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $output .= '<style>
            table { border-collapse: collapse; font-family: sans-serif; font-size: 11px; width: 100%; }
            th { background-color: #4B00E8; color: #ffffff; border: 1px solid #CBD5E1; padding: 8px; font-weight: bold; }
            td { border: 1px solid #E2E8F0; padding: 6px 8px; vertical-align: top; }
            tr:nth-child(even) { background-color: #F8FAFC; }
        </style></head><body>';
        $output .= '<h2>Daily Work Reports - Complete Export</h2>';
        $output .= '<p>Generated on: ' . date('d-m-Y h:i A') . ' | Total Records: ' . count($reports) . '</p>';
        $output .= '<table><thead><tr>
            <th>#</th>
            <th>Employee Name</th>
            <th>Employee Code</th>
            <th>Designation</th>
            <th>Department</th>
            <th>Work Date</th>
            <th>Day</th>
            <th>Mode</th>
            <th>Shift Context</th>
            <th>Gross Work</th>
            <th>Work Summary Description</th>
            <th>Structured Tasks</th>
        </tr></thead><tbody>';

        foreach ($reports as $index => $report) {
            $row = formatWorkReportRow($report);
            $tasksHtml = '';
            if (!empty($row['structured_tasks'])) {
                foreach ($row['structured_tasks'] as $t) {
                    $prefix = $t['done'] ? '&#10004; ' : '&bull; ';
                    $tasksHtml .= '<div>' . $prefix . htmlspecialchars($t['text']) . '</div>';
                }
            }

            $output .= '<tr>
                <td align="center">' . ($index + 1) . '</td>
                <td><b>' . htmlspecialchars($row['employee_name'] ?? '') . '</b></td>
                <td>' . htmlspecialchars($row['employee_code'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['designation'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['department'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['date'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['day_name'] ?? '') . '</td>
                <td align="center">' . htmlspecialchars($row['mode'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['shift_context'] ?? '') . '</td>
                <td align="center">' . htmlspecialchars($row['gross_work'] ?? '') . '</td>
                <td>' . nl2br(htmlspecialchars($row['summary_desc'] ?? '')) . '</td>
                <td>' . $tasksHtml . '</td>
            </tr>';
        }

        $output .= '</tbody></table></body></html>';

        return response($output, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * Export all filtered team leave applications as CSV
     */
    protected function exportTeamLeaveCsv($leaves)
    {
        $filename = 'Team_Leave_Applications_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($leaves) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                '#',
                'Employee Name',
                'Employee Code',
                'Designation',
                'Department',
                'Reporting Manager',
                'Leave Type',
                'Leave Period',
                'Total Days',
                'Manager Approval',
                'HR Approval',
                'Overall Status',
                'Reason',
            ]);

            foreach ($leaves as $index => $req) {
                $stLower = strtolower(trim($req->status ?? 'pending'));
                $mgrApproved = !empty($req->manager_approved_by) || !empty($req->manager_approved_at) || ($req->approval_level === 'manager_approved');
                $mgrRejected = ($stLower === 'rejected' && empty($req->manager_approved_by));
                $hrApproved = ($stLower === 'approved');
                $hrRejected = ($stLower === 'rejected' && !empty($req->manager_approved_by));

                if ($hrApproved) {
                    $managerStatus = $mgrApproved ? ('Approved' . ($req->manager_approver_name ? ' by ' . $req->manager_approver_name : '')) : 'Not Required';
                    $hrStatus = 'Approved' . ($req->hr_approver_name ? ' by ' . $req->hr_approver_name : '');
                } elseif ($mgrApproved) {
                    $managerStatus = 'Approved' . ($req->manager_approver_name ? ' by ' . $req->manager_approver_name : '');
                    $hrStatus = 'Pending HR';
                } elseif ($mgrRejected) {
                    $managerStatus = 'Rejected' . ($req->rejected_by_name ? ' by ' . $req->rejected_by_name : '');
                    $hrStatus = '—';
                } elseif ($hrRejected) {
                    $managerStatus = 'Approved' . ($req->manager_approver_name ? ' by ' . $req->manager_approver_name : '');
                    $hrStatus = 'Rejected' . ($req->rejected_by_name ? ' by ' . $req->rejected_by_name : '');
                } else {
                    $managerStatus = 'Pending';
                    $hrStatus = 'Pending';
                }

                $period = Carbon::parse($req->start_date)->format('d M Y');
                if ($req->start_date !== $req->end_date) {
                    $period .= ' -> ' . Carbon::parse($req->end_date)->format('d M Y');
                }

                $daysVal = (float)($req->requested_days ?? $req->deducted_days ?? 1);
                $daysStr = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . Str::plural('Day', $daysVal);

                fputcsv($handle, [
                    $index + 1,
                    $req->display_name ?? 'Employee',
                    $req->employee_code ?? '',
                    $req->designation_name ?? '',
                    $req->department_name ?? '',
                    $req->reporting_manager_name ?: '—',
                    $req->leave_type_name ?? 'Leave',
                    $period,
                    $daysStr,
                    $managerStatus,
                    $hrStatus,
                    strtoupper($req->status ?? 'pending'),
                    $req->reason ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export all filtered team leave applications as Excel
     */
    protected function exportTeamLeaveExcel($leaves)
    {
        $filename = 'Team_Leave_Applications_' . date('Y-m-d_His') . '.xls';
        
        $output = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $output .= '<style>
            table { border-collapse: collapse; font-family: sans-serif; font-size: 11px; width: 100%; }
            th { background-color: #4B00E8; color: #ffffff; border: 1px solid #CBD5E1; padding: 8px; font-weight: bold; }
            td { border: 1px solid #E2E8F0; padding: 6px 8px; vertical-align: middle; }
            tr:nth-child(even) { background-color: #F8FAFC; }
        </style></head><body>';
        $output .= '<h2>Team Leave Applications - Full Dataset</h2>';
        $output .= '<p>Generated on: ' . date('d-m-Y h:i A') . ' | Total Records: ' . count($leaves) . '</p>';
        $output .= '<table><thead><tr>
            <th>#</th>
            <th>Employee Name</th>
            <th>Employee Code</th>
            <th>Designation</th>
            <th>Department</th>
            <th>Reporting Manager</th>
            <th>Leave Type</th>
            <th>Leave Period</th>
            <th>Total Days</th>
            <th>Manager Approval</th>
            <th>HR Approval</th>
            <th>Overall Status</th>
            <th>Reason</th>
        </tr></thead><tbody>';

        foreach ($leaves as $index => $req) {
            $stLower = strtolower(trim($req->status ?? 'pending'));
            $mgrApproved = !empty($req->manager_approved_by) || !empty($req->manager_approved_at) || ($req->approval_level === 'manager_approved');
            $mgrRejected = ($stLower === 'rejected' && empty($req->manager_approved_by));
            $hrApproved = ($stLower === 'approved');
            $hrRejected = ($stLower === 'rejected' && !empty($req->manager_approved_by));

            if ($hrApproved) {
                $managerStatus = $mgrApproved ? ('Approved' . ($req->manager_approver_name ? ' by ' . $req->manager_approver_name : '')) : 'Not Required';
                $hrStatus = 'Approved' . ($req->hr_approver_name ? ' by ' . $req->hr_approver_name : '');
            } elseif ($mgrApproved) {
                $managerStatus = 'Approved' . ($req->manager_approver_name ? ' by ' . $req->manager_approver_name : '');
                $hrStatus = 'Pending HR';
            } elseif ($mgrRejected) {
                $managerStatus = 'Rejected' . ($req->rejected_by_name ? ' by ' . $req->rejected_by_name : '');
                $hrStatus = '—';
            } elseif ($hrRejected) {
                $managerStatus = 'Approved' . ($req->manager_approver_name ? ' by ' . $req->manager_approver_name : '');
                $hrStatus = 'Rejected' . ($req->rejected_by_name ? ' by ' . $req->rejected_by_name : '');
            } else {
                $managerStatus = 'Pending';
                $hrStatus = 'Pending';
            }

            $period = Carbon::parse($req->start_date)->format('d M Y');
            if ($req->start_date !== $req->end_date) {
                $period .= ' &rarr; ' . Carbon::parse($req->end_date)->format('d M Y');
            }

            $daysVal = (float)($req->requested_days ?? $req->deducted_days ?? 1);
            $daysStr = ($daysVal == floor($daysVal) ? number_format($daysVal, 0) : number_format($daysVal, 1)) . ' ' . Str::plural('Day', $daysVal);

            $output .= '<tr>
                <td align="center">' . ($index + 1) . '</td>
                <td><b>' . htmlspecialchars($req->display_name ?? 'Employee') . '</b></td>
                <td>' . htmlspecialchars($req->employee_code ?? '') . '</td>
                <td>' . htmlspecialchars($req->designation_name ?? '') . '</td>
                <td>' . htmlspecialchars($req->department_name ?? '') . '</td>
                <td>' . htmlspecialchars($req->reporting_manager_name ?: '—') . '</td>
                <td>' . htmlspecialchars($req->leave_type_name ?? 'Leave') . '</td>
                <td align="center">' . $period . '</td>
                <td align="center">' . $daysStr . '</td>
                <td align="center">' . htmlspecialchars($managerStatus) . '</td>
                <td align="center">' . htmlspecialchars($hrStatus) . '</td>
                <td align="center"><b>' . htmlspecialchars(strtoupper($req->status ?? 'pending')) . '</b></td>
                <td>' . htmlspecialchars($req->reason ?? '') . '</td>
            </tr>';
        }

        $output .= '</tbody></table></body></html>';

        return response($output, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * Scoped Projects & Tasks
     */
    public function projects(Request $request)
    {
        $supervisorEmpId = $this->scopeS->getOwnEmployeeId();
        $isGlobal = $this->scopeS->isSuperAdminOrGlobal();
        $supervisedEmpIds = $this->scopeS->getActiveSupervisedEmployeeIds($supervisorEmpId);

        if ($isGlobal && empty($supervisedEmpIds)) {
            $supervisedEmpIds = EmployeeM::active()->pluck('id')->toArray();
        }

        $teamEmployees = !empty($supervisedEmpIds)
            ? EmployeeM::with(['user', 'designation', 'department'])->whereIn('id', $supervisedEmpIds)->orderBy('id')->get()
            : collect();

        $allProjectsList = DB::table('projects')->select('id', 'name', 'project_code')->orderBy('name')->get();

        $query = DB::table('projects')
            ->leftJoin('employees_new as dh', 'dh.id', '=', 'projects.delivery_head_employee_id')
            ->leftJoin('users as dhu', 'dhu.id', '=', 'dh.user_id');

        if (!$isGlobal || !empty($supervisedEmpIds)) {
            $query->whereExists(function ($sub) use ($supervisedEmpIds) {
                $sub->select(DB::raw(1))
                    ->from('project_assignments')
                    ->whereColumn('project_assignments.project_id', 'projects.id')
                    ->whereIn('project_assignments.employee_id', $supervisedEmpIds)
                    ->where('project_assignments.is_active', 1);
            });
        }

        if ($request->filled('project_id')) {
            $query->where('projects.id', $request->project_id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('projects.status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $empId = $request->employee_id;
            $query->whereExists(function ($sub) use ($empId) {
                $sub->select(DB::raw(1))
                    ->from('project_assignments')
                    ->whereColumn('project_assignments.project_id', 'projects.id')
                    ->where('project_assignments.employee_id', $empId)
                    ->where('project_assignments.is_active', 1);
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('projects.name', 'LIKE', "%{$search}%")
                    ->orWhere('projects.project_code', 'LIKE', "%{$search}%")
                    ->orWhere('projects.client_name', 'LIKE', "%{$search}%")
                    ->orWhere('projects.description', 'LIKE', "%{$search}%");
            });
        }

        $projects = $query->select('projects.*', DB::raw('COALESCE(projects.delivery_head_name, dhu.name, dh.employee_code) as delivery_head_name'))
            ->distinct()
            ->orderByDesc('projects.id')
            ->get();

        $totalMembersCount = 0;
        $totalTasksCount = 0;
        $completedTasksCount = 0;
        $inProgressTasksCount = 0;
        $assignedMemberIds = [];

        foreach ($projects as $prj) {
            $memberQuery = DB::table('project_assignments')
                ->join('employees_new as e', 'e.id', '=', 'project_assignments.employee_id')
                ->leftJoin('users as eu', 'eu.id', '=', 'e.user_id')
                ->leftJoin('designations as ed', 'ed.id', '=', 'e.designation_id')
                ->leftJoin('project_teams as pt', 'pt.id', '=', 'project_assignments.project_team_id')
                ->where('project_assignments.project_id', $prj->id)
                ->where('project_assignments.is_active', 1);

            if (!empty($supervisedEmpIds)) {
                $memberQuery->whereIn('project_assignments.employee_id', $supervisedEmpIds);
            }

            if ($request->filled('employee_id')) {
                $memberQuery->where('project_assignments.employee_id', $request->employee_id);
            }

            $prj->reporting_members = $memberQuery->select(
                'e.id as employee_id',
                DB::raw('COALESCE(eu.name, e.employee_code) as display_name'),
                'e.employee_code',
                'ed.name as designation_name',
                'pt.team_name as team_name',
                'project_assignments.project_role as role_name'
            )
            ->distinct()
            ->get();

            $prjTaskCount = 0;
            $prjCompletedTaskCount = 0;

            foreach ($prj->reporting_members as $mem) {
                $assignedMemberIds[] = $mem->employee_id;
                $mem->tasks = DB::table('project_tasks')
                    ->where('project_id', $prj->id)
                    ->where('assigned_employee_id', $mem->employee_id)
                    ->select('id', 'title', 'status', 'priority', 'progress_percentage', 'due_date')
                    ->orderByDesc('id')
                    ->get();

                foreach ($mem->tasks as $t) {
                    $totalTasksCount++;
                    $prjTaskCount++;
                    $st = strtolower($t->status ?? 'todo');
                    if (in_array($st, ['completed', 'done'])) {
                        $completedTasksCount++;
                        $prjCompletedTaskCount++;
                    } elseif (in_array($st, ['in_progress', 'doing', 'testing'])) {
                        $inProgressTasksCount++;
                    }
                }
            }

            $prj->total_tasks_count = $prjTaskCount;
            $prj->completed_tasks_count = $prjCompletedTaskCount;
            $prj->progress_percentage = $prjTaskCount > 0 ? round(($prjCompletedTaskCount / $prjTaskCount) * 100) : 0;
        }

        $uniqueMembersCount = count(array_unique($assignedMemberIds));

        $stats = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'completed_projects' => $projects->whereIn('status', ['completed', 'closed'])->count(),
            'total_members' => $uniqueMembersCount,
            'total_tasks' => $totalTasksCount,
            'completed_tasks' => $completedTasksCount,
            'in_progress_tasks' => $inProgressTasksCount,
        ];

        return view('hrms.reporting.projects', compact(
            'projects',
            'teamEmployees',
            'allProjectsList',
            'stats',
            'isGlobal'
        ));
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
