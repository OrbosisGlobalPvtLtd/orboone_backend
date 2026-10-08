<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\UserM as User;
use App\Models\HRMS\Attendance\AttendanceWorkLogM as WorkLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\HRMS\Employee\EmployeeM;
use Illuminate\View\View;

class WorkReportC extends Controller
{
    use HrmsCrudPage;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || in_array($roleId, [1, 2, 3], true);

        $teamEmpIds = $this->teamEmployeeIds(false);
        $isManager = (! empty($teamEmpIds) || (method_exists($user, 'hasRole') && $user->hasRole('manager'))) && ! $isHrOrAdmin;
        $isEmployee = ! $isHrOrAdmin && ! $isManager;

        $canOwn = $this->userHasPermission('attendance.work_reports.view_own')
            || $this->userHasPermission('attendance.work_reports.view')
            || (method_exists($user, 'isEmployee') && $user->isEmployee())
            || in_array($roleId, [7, 8], true);

        abort_unless($isHrOrAdmin || $isManager || $canOwn, 403);

        $ownEmployee = $this->currentEmployee();
        $ownEmployeeId = $ownEmployee ? $ownEmployee->id : $this->ownEmployeeId();
        $userId = Auth::id();

        $query = WorkLog::with([
            'user',
            'employee.department',
            'employee.designation',
            'attendance.attendanceTime'
        ]);

        $isMyWorkReportsRoute = request()->routeIs('hrms.attendance.my-work-reports') || request()->routeIs('my-work-reports');

        if ($isEmployee) {
            // Strictly self only (for regular employees)
            $query->where(function ($q) use ($ownEmployeeId, $userId) {
                if ($ownEmployeeId && $userId) {
                    $q->where('employee_id', $ownEmployeeId)->orWhere('user_id', $userId);
                } elseif ($ownEmployeeId) {
                    $q->where('employee_id', $ownEmployeeId);
                } elseif ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            });
            $request->merge(['employee_id' => $ownEmployeeId]);
        } elseif ($isManager) {
            // Scoped strictly to supervised team members + own
            $allowedEmpIds = array_values(array_unique(array_filter(array_merge($teamEmpIds, array_filter([$ownEmployeeId])))));
            if ($request->filled('employee_id') && in_array((int) $request->employee_id, $allowedEmpIds, true)) {
                $query->where('employee_id', $request->employee_id);
            } else {
                $query->whereIn('employee_id', $allowedEmpIds);
            }
        } elseif ($isHrOrAdmin) {
            // Admin can view all or filter by selected employee
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
        }

        // Build Month Dropdown Options (Last 12 Months)
        $monthOptions = [];
        $cursor = Carbon::now()->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $val = $cursor->format('Y-m');
            $monthOptions[$val] = $cursor->format('F Y');
            $cursor->subMonth();
        }

        // Determine active month / single date / custom date filtering
        $selectedMonth = $request->input('month');
        $hasSingleDate = $request->filled('date');
        $hasDateRange = $request->filled('from_date') || $request->filled('to_date');
        $isCustomDate = ($selectedMonth === 'custom') || (! $request->has('month') && ! $hasSingleDate && $hasDateRange);

        if ($hasSingleDate) {
            $query->whereDate('work_date', $request->date);
            try {
                $selectedMonth = Carbon::parse($request->date)->format('Y-m');
            } catch (\Throwable $e) {
                $selectedMonth = Carbon::now()->format('Y-m');
            }
            if (! isset($monthOptions[$selectedMonth])) {
                try {
                    $monthOptions[$selectedMonth] = Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y');
                } catch (\Throwable $e) {}
            }
        } elseif (! $isCustomDate && $selectedMonth && $selectedMonth !== 'all') {
            try {
                $mDate = Carbon::createFromFormat('Y-m', $selectedMonth);
                $query->whereBetween('work_date', [
                    $mDate->copy()->startOfMonth()->toDateString(),
                    $mDate->copy()->endOfMonth()->toDateString()
                ]);
            } catch (\Throwable $e) {
                $selectedMonth = Carbon::now()->format('Y-m');
                $query->whereBetween('work_date', [
                    Carbon::now()->startOfMonth()->toDateString(),
                    Carbon::now()->endOfMonth()->toDateString()
                ]);
            }
        } elseif ($isCustomDate) {
            if ($request->filled('from_date')) {
                $query->whereDate('work_date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('work_date', '<=', $request->to_date);
            }
        } else {
            $selectedMonth = Carbon::now()->format('Y-m');
            $query->whereBetween('work_date', [
                Carbon::now()->startOfMonth()->toDateString(),
                Carbon::now()->endOfMonth()->toDateString()
            ]);
        }

        // Apply additional request filters
        if ($request->filled('work_mode')) {
            $workMode = strtolower($request->work_mode);
            $query->whereHas('attendance', function ($qa) use ($workMode) {
                $qa->where('work_mode', $workMode);
            });
        }

        // Clone query for overall dataset metrics & employee summaries
        $statsQuery = (clone $query);
        $allFilteredLogs = $statsQuery->get();

        // Build Employee Summaries grouping for Employee Cards View
        $employeeSummaries = $allFilteredLogs->groupBy(function ($log) {
            return $log->employee_id ?: ($log->user_id ?: 0);
        })->map(function ($logs, $empId) {
            $first = $logs->first();
            $emp = $first->employee;
            $user = $first->user;

            $totalSeconds = 0;
            $totalTasks = 0;

            foreach ($logs as $log) {
                $gross = optional($log->attendance)->gross_duration;
                if ($gross) {
                    if (preg_match('/(?:(\d+)\s*h(?:ours?)?)?\s*(?:(\d+)\s*m(?:ins?)?)?/i', $gross, $m)) {
                        $hrs = (int) ($m[1] ?? 0);
                        $mins = (int) ($m[2] ?? 0);
                        $totalSeconds += ($hrs * 3600) + ($mins * 60);
                    }
                }

                $tasks = $log->work_summary_json;
                if (is_string($tasks)) {
                    $tasks = json_decode($tasks, true);
                }
                if (is_array($tasks)) {
                    if (isset($tasks['projects']) && is_array($tasks['projects'])) {
                        foreach ($tasks['projects'] as $p) {
                            if (isset($p['tasks']) && is_array($p['tasks'])) {
                                $totalTasks += count($p['tasks']);
                            }
                        }
                    } elseif (isset($tasks['requirements']) && is_array($tasks['requirements'])) {
                        $totalTasks += count($tasks['requirements']);
                    } elseif (isset($tasks['tasks']) && is_array($tasks['tasks'])) {
                        $totalTasks += count($tasks['tasks']);
                    }
                }
            }

            $hrsTotal = floor($totalSeconds / 3600);
            $minsTotal = floor(($totalSeconds % 3600) / 60);
            $formattedGross = $hrsTotal > 0 ? "{$hrsTotal} hrs {$minsTotal} mins" : "{$minsTotal} mins";

            return [
                'employee_id' => $empId,
                'user_name' => optional($user)->name ?? 'Employee',
                'employee_code' => optional($emp)->employee_code ?? 'N/A',
                'department' => optional(optional($emp)->department)->name ?? 'Staff',
                'designation' => optional(optional($emp)->designation)->name ?? 'Member',
                'passport_photo_url' => resolveEmployeePassportPhoto($emp ?? $first),
                'employee_initial' => resolveEmployeeInitials($emp ?? $first),
                'total_reports' => $logs->count(),
                'total_gross_formatted' => $formattedGross,
                'total_tasks' => $totalTasks,
                'latest_date' => $first->work_date ? $first->work_date->format('d M Y') : '-',
                'latest_summary' => $first->work_summary ?: 'Work report submitted with project tasks.',
                'logs' => $logs,
            ];
        })->values();

        // Calculate aggregate KPI statistics accurately on the filtered dataset
        $totalSecondsAll = 0;
        foreach ($allFilteredLogs as $log) {
            $gross = optional($log->attendance)->gross_duration;
            if ($gross && preg_match('/(?:(\d+)\s*h(?:ours?)?)?\s*(?:(\d+)\s*m(?:ins?)?)?/i', $gross, $m)) {
                $totalSecondsAll += ((int)($m[1] ?? 0) * 3600) + ((int)($m[2] ?? 0) * 60);
            }
        }
        $hrsAll = floor($totalSecondsAll / 3600);
        $minsAll = floor(($totalSecondsAll % 3600) / 60);
        $formattedTotalGross = $hrsAll > 0 ? "{$hrsAll} hrs {$minsAll} mins" : "{$minsAll} mins";

        $statsSummary = [
            'total_reports' => $allFilteredLogs->count(),
            'unique_employees' => $employeeSummaries->count(),
            'total_tasks' => $employeeSummaries->sum('total_tasks'),
            'total_gross_formatted' => $formattedTotalGross,
            'wfo_count' => $allFilteredLogs->filter(fn($l) => strtolower(optional($l->attendance)->work_mode ?? 'wfo') !== 'wfh')->count(),
            'wfh_count' => $allFilteredLogs->filter(fn($l) => strtolower(optional($l->attendance)->work_mode ?? '') === 'wfh')->count(),
        ];

        // Server-Side Pagination
        $perPageParam = $request->input('per_page', 25);
        if ($perPageParam === 'all' || (int) $perPageParam === -1) {
            $perPage = max($allFilteredLogs->count(), 1000);
        } else {
            $perPage = max(1, (int) $perPageParam);
        }

        $workLogs = $query->orderByDesc('work_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        // Batch preload passport photos for current page to eliminate N+1 queries
        $empIds = $workLogs->pluck('employee_id')->filter()->unique()->values()->toArray();
        global $preloadedPassportPhotos;
        $preloadedPassportPhotos = [];
        if (!empty($empIds) && Schema::hasTable('employee_documents_new') && Schema::hasTable('document_types')) {
            try {
                $photos = DB::table('employee_documents_new')
                    ->join('document_types', 'document_types.id', '=', 'employee_documents_new.document_type_id')
                    ->whereIn('employee_documents_new.employee_id', $empIds)
                    ->where(function ($q) {
                        $q->where('document_types.name', 'Passport Size Photo')
                            ->orWhere('document_types.code', 'passport_size_photo')
                            ->orWhere('document_types.name', 'Passport Photo')
                            ->orWhere('document_types.code', 'passport_photo')
                            ->orWhere('document_types.name', 'Photo')
                            ->orWhere('document_types.name', 'Passport')
                            ->orWhere('document_types.name', 'like', '%Passport%Photo%')
                            ->orWhere('document_types.name', 'like', '%Passport%Size%Photo%');
                    })
                    ->select('employee_documents_new.employee_id', 'employee_documents_new.file_path', 'employee_documents_new.verification_status')
                    ->orderByRaw("CASE WHEN employee_documents_new.verification_status = 'verified' THEN 0 ELSE 1 END")
                    ->orderBy('employee_documents_new.id', 'desc')
                    ->get()
                    ->groupBy('employee_id');

                foreach ($empIds as $id) {
                    $document = isset($photos[$id]) ? $photos[$id]->first() : null;
                    $preloadedPassportPhotos[$id] = ($document && $document->file_path)
                        ? route('hrms.documents.file', ['path' => $document->file_path])
                        : null;
                }
            } catch (\Throwable $e) {}
        }

        // Get employees dropdown depending on role visibility (Active & Approved only)
        $isAdminOrManager = $isHrOrAdmin || $isManager;
        $employees = $this->attendanceEmployees($isHrOrAdmin, $isManager, $teamEmpIds);

        $filters = [
            'months' => $monthOptions,
            'selected_month' => $hasSingleDate ? '' : $selectedMonth,
            'current_month' => Carbon::now()->format('Y-m'),
            'is_custom' => $isCustomDate,
            'date' => $request->date,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'per_page' => $perPageParam,
            'work_mode' => $request->work_mode,
            'employee_id' => $isEmployee ? $ownEmployeeId : $request->employee_id,
            'search' => $request->search,
        ];

        return view('hrms.attendance.work-reports.index', compact('workLogs', 'employeeSummaries', 'employees', 'isAdminOrManager', 'statsSummary', 'filters'));
    }

    private function attendanceEmployees(bool $isHrOrAdmin, bool $isManager, array $teamEmpIds = [])
    {
        if (! $isHrOrAdmin && ! $isManager) {
            return collect();
        }

        $query = User::where('is_active', true)
            ->whereHas('employee', function ($eq) use ($isHrOrAdmin, $teamEmpIds) {
                $eq->where('is_active', 1)
                   ->where(function ($q) {
                       $q->whereNull('relieving_date')
                         ->orWhereDate('relieving_date', '>=', now()->toDateString());
                   })
                   ->where(function ($q) {
                       $q->whereNull('employment_status')
                         ->orWhereNotIn('employment_status', ['terminated', 'resigned', 'relieved', 'inactive']);
                   })
                   ->whereHas('profile', function ($pq) {
                       $pq->where('profile_status', 'approved');
                   });

                if (! $isHrOrAdmin) {
                    $allowed = array_values(array_unique(array_filter(array_merge($teamEmpIds, array_filter([$this->ownEmployeeId()])))));
                    $eq->whereIn('id', $allowed);
                }
            })
            ->with(['employee.department', 'employee.designation'])
            ->orderBy('name');

        return $query->get();
    }

    public function employeeHistory(int|string $employeeId, Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || in_array($roleId, [1, 2, 3], true);

        $teamEmpIds = $this->teamEmployeeIds(false);
        $isManager = (! empty($teamEmpIds) || (method_exists($user, 'hasRole') && $user->hasRole('manager'))) && ! $isHrOrAdmin;
        $ownEmpId = $this->ownEmployeeId();

        abort_unless(
            $isHrOrAdmin || $isManager
            || $this->userHasPermission('attendance.work_reports.view_all')
            || $this->userHasPermission('attendance.work_reports.view_team')
            || $this->userHasPermission('attendance.work_reports.view_own'),
            403
        );

        $employee = EmployeeM::with(['user', 'department', 'designation', 'reportingManager.user'])->find($employeeId);
        if (! $employee) {
            $u = User::with(['employee.department', 'employee.designation', 'employee.reportingManager.user'])->find($employeeId);
            if ($u && $u->employee) {
                $employee = $u->employee;
            } else {
                abort(404, 'Employee not found');
            }
        }

        if (! $isHrOrAdmin) {
            $allowedIds = $isManager ? array_values(array_unique(array_filter(array_merge($teamEmpIds, array_filter([$ownEmpId]))))) : array_filter([$ownEmpId]);
            if (! in_array((int) $employee->id, $allowedIds, true)) {
                abort(403, 'Unauthorized to view this employee work reports.');
            }
        }

        $selectedMonth = $request->get('month');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $workMode = $request->get('work_mode');
        $perPage = $request->get('per_page', 25);

        // Default to current month if no specific dates or month passed
        if (!$request->has('month') && !$fromDate && !$toDate) {
            $selectedMonth = now()->format('Y-m');
        }

        $query = WorkLog::with(['user', 'employee.department', 'employee.designation', 'attendance.attendanceTime'])
            ->where('employee_id', $employee->id);

        if ($request->filled('work_mode')) {
            $query->whereHas('attendance', function ($q) use ($request) {
                $q->where('work_mode', $request->work_mode);
            });
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('work_date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->whereDate('work_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->whereDate('work_date', '<=', $toDate);
        } elseif ($selectedMonth && $selectedMonth !== 'all') {
            try {
                $dt = Carbon::createFromFormat('Y-m', $selectedMonth);
                $query->whereYear('work_date', $dt->year)
                      ->whereMonth('work_date', $dt->month);
            } catch (\Exception $e) {
                // fallback
            }
        }

        // Calculate summary metrics on full filtered dataset
        $statsLogs = (clone $query)->get();

        $totalSeconds = 0;
        $totalTasks = 0;
        $completedTasks = 0;
        $wfoCount = 0;
        $wfhCount = 0;
        $issuesCount = 0;

        foreach ($statsLogs as $log) {
            $attendance = $log->attendance;
            $gross = optional($attendance)->gross_duration;
            if ($gross && preg_match('/(?:(\d+)\s*h(?:ours?)?)?\s*(?:(\d+)\s*m(?:ins?)?)?/i', $gross, $m)) {
                $totalSeconds += ((int) ($m[1] ?? 0) * 3600) + ((int) ($m[2] ?? 0) * 60);
            }

            $mode = strtolower(optional($attendance)->work_mode ?? 'wfo');
            if ($mode === 'wfh') {
                $wfhCount++;
            } else {
                $wfoCount++;
            }

            $tasks = $log->work_summary_json;
            if (is_string($tasks)) {
                $tasks = json_decode($tasks, true);
            }
            if (is_array($tasks)) {
                if (isset($tasks['projects']) && is_array($tasks['projects'])) {
                    foreach ($tasks['projects'] as $p) {
                        if (isset($p['tasks']) && is_array($p['tasks'])) {
                            foreach ($p['tasks'] as $t) {
                                $totalTasks++;
                                $isDone = (isset($t['is_completed']) ? ($t['is_completed'] == 1 || $t['is_completed'] === true) : (isset($t['completed']) ? ($t['completed'] == 1 || $t['completed'] === true) : true));
                                if ($isDone) {
                                    $completedTasks++;
                                }
                            }
                        }
                    }
                } elseif (isset($tasks['requirements']) && is_array($tasks['requirements'])) {
                    foreach ($tasks['requirements'] as $req) {
                        $totalTasks++;
                        $isDone = is_array($req) ? (isset($req['done']) ? ($req['done'] === true || $req['done'] === 'true') : true) : true;
                        if ($isDone) {
                            $completedTasks++;
                        }
                    }
                } elseif (isset($tasks['tasks']) && is_array($tasks['tasks'])) {
                    foreach ($tasks['tasks'] as $t) {
                        $totalTasks++;
                        $isDone = is_array($t) ? (isset($t['is_completed']) ? ($t['is_completed'] == 1 || $t['is_completed'] === true) : true) : true;
                        if ($isDone) {
                            $completedTasks++;
                        }
                    }
                }

                $issues = $tasks['issues'] ?? [];
                $issuesArr = is_array($issues) ? $issues : (is_string($issues) ? [$issues] : []);
                $realIssues = array_filter($issuesArr, function ($item) {
                    if (!is_string($item)) return true;
                    $val = strtolower(trim($item));
                    return $val !== '' && $val !== 'no issues' && $val !== 'none';
                });
                if (!empty($realIssues)) {
                    $issuesCount++;
                }
            }
        }

        $hrsTotal = floor($totalSeconds / 3600);
        $minsTotal = floor(($totalSeconds % 3600) / 60);
        $formattedGross = $hrsTotal > 0 ? "{$hrsTotal} hrs {$minsTotal} mins" : "{$minsTotal} mins";

        $reportsCount = $statsLogs->count();
        $avgDailySeconds = $reportsCount > 0 ? floor($totalSeconds / $reportsCount) : 0;
        $avgHrs = floor($avgDailySeconds / 3600);
        $avgMins = floor(($avgDailySeconds % 3600) / 60);
        $formattedAvgDaily = $avgHrs > 0 ? "{$avgHrs}h {$avgMins}m / day" : "{$avgMins}m / day";

        $pendingTasks = max(0, $totalTasks - $completedTasks);
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 100;

        $dateRangeLabel = 'All Historical Records';
        if ($fromDate && $toDate) {
            $dateRangeLabel = Carbon::parse($fromDate)->format('d M Y') . ' – ' . Carbon::parse($toDate)->format('d M Y');
        } elseif ($fromDate) {
            $dateRangeLabel = 'From ' . Carbon::parse($fromDate)->format('d M Y');
        } elseif ($toDate) {
            $dateRangeLabel = 'Until ' . Carbon::parse($toDate)->format('d M Y');
        } elseif ($selectedMonth && $selectedMonth !== 'all') {
            try {
                $dateRangeLabel = Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y');
            } catch (\Exception $e) {
                // fallback
            }
        } elseif ($statsLogs->isNotEmpty()) {
            $minDate = $statsLogs->min('work_date');
            $maxDate = $statsLogs->max('work_date');
            if ($minDate && $maxDate) {
                $dateRangeLabel = Carbon::parse($minDate)->format('d M Y') . ' – ' . Carbon::parse($maxDate)->format('d M Y');
            }
        }

        $reportingManager = $employee->reportingManager;
        $reportingManagerName = $reportingManager ? (optional($reportingManager->user)->name ?? $reportingManager->employee_code) : 'Department Head / Admin';

        $summary = [
            'employee' => $employee,
            'user' => $employee->user,
            'employee_name' => optional($employee->user)->name ?? $employee->name ?? 'Employee',
            'employee_code' => $employee->employee_code ?? 'N/A',
            'employee_email' => optional($employee->user)->email ?? 'N/A',
            'department' => optional($employee->department)->name ?? 'Staff',
            'designation' => optional($employee->designation)->name ?? 'Member',
            'reporting_manager_name' => $reportingManagerName,
            'passport_photo_url' => resolveEmployeePassportPhoto($employee),
            'employee_initial' => resolveEmployeeInitials($employee),
            'total_reports' => $reportsCount,
            'total_gross_formatted' => $formattedGross,
            'total_seconds' => $totalSeconds,
            'avg_daily_formatted' => $formattedAvgDaily,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'completion_rate' => $completionRate,
            'wfo_count' => $wfoCount,
            'wfh_count' => $wfhCount,
            'issues_count' => $issuesCount,
            'date_range_label' => $dateRangeLabel,
            'selected_month' => $selectedMonth,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];

        // Months for dropdown
        $availableMonths = [];
        $currentMonthObj = now()->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $m = (clone $currentMonthObj)->subMonths($i);
            $availableMonths[$m->format('Y-m')] = $m->format('F Y');
        }

        // Handle Pagination vs Print All
        $isPrint = $request->routeIs('*print*');
        if ($isPrint || $perPage === 'all' || $perPage == -1) {
            $workLogs = $query->orderByDesc('work_date')->orderByDesc('id')->get();
        } else {
            $perPage = max(10, min(500, (int) $perPage));
            $workLogs = $query->orderByDesc('work_date')->orderByDesc('id')
                ->paginate($perPage)
                ->withQueryString();
        }

        return view('hrms.attendance.work-reports.history', compact('employee', 'workLogs', 'summary', 'availableMonths', 'selectedMonth'));
    }

    public function printEmployeeHistory(int|string $employeeId, Request $request)
    {
        $response = $this->employeeHistory($employeeId, $request);
        if ($response instanceof View) {
            $data = $response->getData();

            return view('hrms.attendance.work-reports.history-print', $data);
        }

        return $response;
    }

    public function printSingleWorkReport(int|string $id, Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $roleId = (int) ($user->system_role_id ?? $user->role_id ?? 0);
        $isSuperAdmin = method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $isHrOrAdmin = $isSuperAdmin
            || (method_exists($user, 'isHrAdmin') && $user->isHrAdmin())
            || (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole(['super_admin', 'admin', 'hr_admin']))
            || in_array($roleId, [1, 2, 3], true);

        $teamEmpIds = $this->teamEmployeeIds(false);
        $isManager = (! empty($teamEmpIds) || (method_exists($user, 'hasRole') && $user->hasRole('manager'))) && ! $isHrOrAdmin;
        $ownEmpId = $this->ownEmployeeId();

        abort_unless(
            $isHrOrAdmin || $isManager
            || $this->userHasPermission('attendance.work_reports.view_all')
            || $this->userHasPermission('attendance.work_reports.view_team')
            || $this->userHasPermission('attendance.work_reports.view_own'),
            403
        );

        $workLog = WorkLog::with([
            'user',
            'employee.department',
            'employee.designation',
            'employee.reportingManager.user',
            'attendance.attendanceTime'
        ])->findOrFail($id);

        $employee = $workLog->employee;
        if (! $employee && $workLog->user) {
            $employee = $workLog->user->employee;
        }

        if (! $isHrOrAdmin && $employee) {
            $allowedIds = $isManager ? array_values(array_unique(array_filter(array_merge($teamEmpIds, array_filter([$ownEmpId]))))) : array_filter([$ownEmpId]);
            if (! in_array((int) $employee->id, $allowedIds, true)) {
                abort(403, 'Unauthorized to view this work report.');
            }
        }

        $reportingManager = optional($employee)->reportingManager;
        $reportingManagerName = $reportingManager ? (optional($reportingManager->user)->name ?? $reportingManager->employee_code) : 'Department Head / Admin';

        return view('hrms.attendance.work-reports.print', compact('workLog', 'employee', 'reportingManagerName'));
    }
}
