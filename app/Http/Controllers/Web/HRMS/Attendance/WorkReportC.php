<?php

namespace App\Http\Controllers\Web\HRMS\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\HRMS\Concerns\HrmsCrudPage;
use App\Models\Core\UserM as User;
use App\Models\HRMS\Attendance\AttendanceWorkLogM as WorkLog;
use Illuminate\Http\Request;

class WorkReportC extends Controller
{
    use HrmsCrudPage;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        abort_unless(
            $this->userHasPermission('attendance.work_reports.view_all')
            || $this->userHasPermission('attendance.work_reports.view_team')
            || $this->userHasPermission('attendance.work_reports.view_own'),
            403
        );

        $query = WorkLog::with([
            'user',
            'employee.department',
            'employee.designation',
            'attendance.attendanceTime'
        ]);

        // Role-based scoping of employee visibility
        $allPermission = 'attendance.work_reports.view_all';
        $teamPermission = 'attendance.work_reports.view_team';
        
        $query = $this->scopeEmployeeVisibility($query, $allPermission, $teamPermission, 'employee_id');

        // Apply request filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($qu) use ($search) {
                    $qu->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('employee', function ($qe) use ($search) {
                    $qe->where('employee_code', 'like', "%{$search}%");
                })->orWhere('work_summary', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('work_mode')) {
            $workMode = strtolower($request->work_mode);
            $query->whereHas('attendance', function ($qa) use ($workMode) {
                $qa->where('work_mode', $workMode);
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('work_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('work_date', '<=', $request->to_date);
        }

        // Retrieve work logs
        $workLogs = $query->orderByDesc('work_date')
            ->orderByDesc('id')
            ->get();

        // Batch preload passport photos to eliminate N+1 queries
        $empIds = $workLogs->pluck('employee_id')->filter()->unique()->values()->toArray();
        global $preloadedPassportPhotos;
        $preloadedPassportPhotos = [];
        if (!empty($empIds) && \Illuminate\Support\Facades\Schema::hasTable('employee_documents_new') && \Illuminate\Support\Facades\Schema::hasTable('document_types')) {
            try {
                $photos = \Illuminate\Support\Facades\DB::table('employee_documents_new')
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

        // Build Employee Summaries grouping for Employee Cards View
        $employeeSummaries = $workLogs->groupBy(function ($log) {
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

        // Calculate aggregate KPI statistics
        $totalSecondsAll = 0;
        foreach ($workLogs as $log) {
            $gross = optional($log->attendance)->gross_duration;
            if ($gross && preg_match('/(?:(\d+)\s*h(?:ours?)?)?\s*(?:(\d+)\s*m(?:ins?)?)?/i', $gross, $m)) {
                $totalSecondsAll += ((int)($m[1] ?? 0) * 3600) + ((int)($m[2] ?? 0) * 60);
            }
        }
        $hrsAll = floor($totalSecondsAll / 3600);
        $minsAll = floor(($totalSecondsAll % 3600) / 60);
        $formattedTotalGross = $hrsAll > 0 ? "{$hrsAll} hrs {$minsAll} mins" : "{$minsAll} mins";

        $statsSummary = [
            'total_reports' => $workLogs->count(),
            'unique_employees' => $employeeSummaries->count(),
            'total_tasks' => $employeeSummaries->sum('total_tasks'),
            'total_gross_formatted' => $formattedTotalGross,
            'wfo_count' => $workLogs->filter(fn($l) => strtolower(optional($l->attendance)->work_mode ?? 'wfo') !== 'wfh')->count(),
            'wfh_count' => $workLogs->filter(fn($l) => strtolower(optional($l->attendance)->work_mode ?? '') === 'wfh')->count(),
        ];

        // Get employees dropdown depending on role visibility
        $employees = $this->attendanceEmployees();

        // Check if admin / manager
        $isAdminOrManager = $this->userHasPermission('attendance.work_reports.view_all') 
            || $this->userHasPermission('attendance.work_reports.view_team');

        return view('hrms.attendance.work-reports', compact('workLogs', 'employeeSummaries', 'employees', 'isAdminOrManager', 'statsSummary'));
    }

    private function attendanceEmployees()
    {
        $query = User::whereHas('employee')->with('employee')->orderBy('name');
        if (! $this->canViewAll('attendance.work_reports.view_all')) {
            $ids = $this->userHasPermission('attendance.work_reports.view_team')
                ? $this->teamEmployeeIds(true)
                : array_filter([$this->ownEmployeeId()]);
            $query->whereHas('employee', fn ($employeeQuery) => $employeeQuery->whereIn('id', $ids));
        }

        return $query->get();
    }

    public function employeeHistory($employeeId, Request $request)
    {
        abort_unless(
            $this->userHasPermission('attendance.work_reports.view_all')
            || $this->userHasPermission('attendance.work_reports.view_team')
            || $this->userHasPermission('attendance.work_reports.view_own'),
            403
        );

        $employee = \App\Models\HRMS\Employee\EmployeeM::with(['user', 'department', 'designation'])->find($employeeId);
        if (! $employee) {
            $user = User::with(['employee.department', 'employee.designation'])->find($employeeId);
            if ($user && $user->employee) {
                $employee = $user->employee;
            } else {
                abort(404, 'Employee not found');
            }
        }

        $query = WorkLog::with(['user', 'employee.department', 'employee.designation', 'attendance.attendanceTime'])
            ->where('employee_id', $employee->id);

        if ($request->filled('from_date')) {
            $query->whereDate('work_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('work_date', '<=', $request->to_date);
        }

        $workLogs = $query->orderByDesc('work_date')->orderByDesc('id')->get();

        $totalSeconds = 0;
        $totalTasks = 0;
        foreach ($workLogs as $log) {
            $gross = optional($log->attendance)->gross_duration;
            if ($gross && preg_match('/(?:(\d+)\s*h(?:ours?)?)?\s*(?:(\d+)\s*m(?:ins?)?)?/i', $gross, $m)) {
                $totalSeconds += ((int) ($m[1] ?? 0) * 3600) + ((int) ($m[2] ?? 0) * 60);
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

        $summary = [
            'employee' => $employee,
            'user' => $employee->user,
            'employee_name' => optional($employee->user)->name ?? $employee->name ?? 'Employee',
            'employee_code' => $employee->employee_code ?? 'N/A',
            'department' => optional($employee->department)->name ?? 'Staff',
            'designation' => optional($employee->designation)->name ?? 'Member',
            'passport_photo_url' => resolveEmployeePassportPhoto($employee),
            'employee_initial' => resolveEmployeeInitials($employee),
            'total_reports' => $workLogs->count(),
            'total_gross_formatted' => $formattedGross,
            'total_tasks' => $totalTasks,
        ];

        return view('hrms.attendance.work-report-employee-history', compact('employee', 'workLogs', 'summary'));
    }

    public function printEmployeeHistory($employeeId, Request $request)
    {
        $response = $this->employeeHistory($employeeId, $request);
        if ($response instanceof \Illuminate\View\View) {
            $data = $response->getData();

            return view('hrms.attendance.work-report-employee-history-print', $data);
        }

        return $response;
    }
}
