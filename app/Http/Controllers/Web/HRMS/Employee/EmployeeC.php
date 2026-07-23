<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Mail\EmployeeCredentialMail;
use App\Services\HRMS\Employee\EmployeeFileS;
use App\Services\HRMS\Employee\EmployeeExitProcessS;
use App\Services\HRMS\Employee\EmployeeLifecycleService;
use App\Services\HRMS\Employee\EmployeePermanentDeleteS;
use App\Services\HRMS\Employee\EmployeeSalaryHistoryService;
use App\Services\HRMS\Employee\EmployeeS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployeeC extends Controller
{
    private string $employeeTable = 'employees_new';
    private string $profileTable = 'employee_profiles';

    private EmployeeS $employeeService;
    private EmployeeLifecycleService $lifecycleService;
    private EmployeeSalaryHistoryService $salaryHistoryService;
    private EmployeePermanentDeleteS $permanentDeleteService;
    private EmployeeExitProcessS $exitProcessService;

    public function __construct(
        EmployeeS $employeeService,
        EmployeeLifecycleService $lifecycleService,
        EmployeeSalaryHistoryService $salaryHistoryService,
        EmployeePermanentDeleteS $permanentDeleteService,
        EmployeeExitProcessS $exitProcessService
    ) {
        $this->employeeService = $employeeService;
        $this->lifecycleService = $lifecycleService;
        $this->salaryHistoryService = $salaryHistoryService;
        $this->permanentDeleteService = $permanentDeleteService;
        $this->exitProcessService = $exitProcessService;
    }

    public function index(Request $request)
    {
        $employeeTable = $this->employeeTable;
        $profileTable = $this->profileTable;

        $userPhoneSelect = Schema::hasColumn('users', 'phone')
            ? 'users.phone'
            : DB::raw('NULL as phone');

        $hasAttendanceTime = Schema::hasTable('attendance_times')
            && Schema::hasColumn($employeeTable, 'attendance_time_id');

        $documentStats = null;

        if (Schema::hasTable('employee_documents_new')) {
            $documentStats = DB::table('employee_documents_new')
                ->select(
                    'employee_id',
                    DB::raw('COUNT(*) as uploaded_documents_count'),
                    DB::raw("SUM(CASE WHEN verification_status = 'verified' THEN 1 ELSE 0 END) as verified_documents_count"),
                    DB::raw("SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) as pending_documents_count"),
                    DB::raw("SUM(CASE WHEN verification_status = 'rejected' THEN 1 ELSE 0 END) as rejected_documents_count")
                )
                ->groupBy('employee_id');
        }

        $today = Carbon::now('Asia/Kolkata')->toDateString();
        $activeAssignmentsSub = DB::table('employee_policy_assignments')
            ->join('attendance_policy_rules', 'attendance_policy_rules.id', '=', 'employee_policy_assignments.policy_id')
            ->where('employee_policy_assignments.policy_type', 'attendance')
            ->where('employee_policy_assignments.is_active', 1)
            ->where(function($q) use ($today) {
                $q->whereNull('employee_policy_assignments.effective_from')
                  ->orWhereDate('employee_policy_assignments.effective_from', '<=', $today);
            })
            ->where(function($q) use ($today) {
                $q->whereNull('employee_policy_assignments.effective_to')
                  ->orWhereDate('employee_policy_assignments.effective_to', '>=', $today);
            })
            ->select('employee_policy_assignments.employee_id', 'attendance_policy_rules.policy_name');

        $baseQuery = DB::table($employeeTable)
            ->join('users', 'users.id', '=', $employeeTable . '.user_id')
            ->leftJoinSub($activeAssignmentsSub, 'active_attendance_policy', function ($join) use ($employeeTable) {
                $join->on('active_attendance_policy.employee_id', '=', $employeeTable . '.id');
            })
            ->leftJoin('departments', 'departments.id', '=', $employeeTable . '.department_id')
            ->leftJoin('designations', 'designations.id', '=', $employeeTable . '.designation_id')
            ->leftJoin($employeeTable . ' as manager', 'manager.id', '=', $employeeTable . '.reporting_manager_employee_id')
            ->leftJoin('users as manager_user', 'manager_user.id', '=', 'manager.user_id')
            ->leftJoin($profileTable, $profileTable . '.employee_id', '=', $employeeTable . '.id');

        if ($hasAttendanceTime) {
            $baseQuery->leftJoin('attendance_times', 'attendance_times.id', '=', $employeeTable . '.attendance_time_id');
        }

        if ($documentStats) {
            $baseQuery->leftJoinSub($documentStats, 'doc_stats', function ($join) use ($employeeTable) {
                $join->on('doc_stats.employee_id', '=', $employeeTable . '.id');
            });
        }

        $baseQuery
            ->where($employeeTable . '.employment_status', 'active')
            ->where($profileTable . '.is_profile_completed', 1)
            ->where($profileTable . '.profile_status', 'approved');

        if (Schema::hasColumn($employeeTable, 'is_active')) {
            $baseQuery->where($employeeTable . '.is_active', 1);
        }

        $baseQuery->select(
            $employeeTable . '.*',
            'users.name',
            'users.email',
            $userPhoneSelect,
            'departments.name as department_name',
            'designations.name as designation_name',
            'manager_user.name as manager_name',
            'manager.employee_code as manager_code',
            $profileTable . '.profile_image',
            $profileTable . '.profile_status',
            $profileTable . '.is_profile_completed',
            DB::raw("CASE 
                WHEN active_attendance_policy.policy_name = 'Default Attendance Policy' THEN 'General Shift'
                WHEN active_attendance_policy.policy_name = 'Part Time Attendance Policy' THEN 'Part Time Shift'
                WHEN active_attendance_policy.policy_name = 'Half Day Attendance Policy' THEN 'Half Day Shift'
                WHEN active_attendance_policy.policy_name = 'WFH Attendance Policy' THEN 'WFH Shift'
                WHEN active_attendance_policy.policy_name = 'Half Day Morning Policy' THEN 'Half Day Morning Shift'
                WHEN active_attendance_policy.policy_name = 'Half Day Evening Policy' THEN 'Half Day Evening Shift'
                ELSE 'General Shift'
            END as shift_name"),
            DB::raw($documentStats ? 'COALESCE(doc_stats.uploaded_documents_count, 0) as uploaded_documents_count' : '0 as uploaded_documents_count'),
            DB::raw($documentStats ? 'COALESCE(doc_stats.verified_documents_count, 0) as verified_documents_count' : '0 as verified_documents_count'),
            DB::raw($documentStats ? 'COALESCE(doc_stats.pending_documents_count, 0) as pending_documents_count' : '0 as pending_documents_count'),
            DB::raw($documentStats ? 'COALESCE(doc_stats.rejected_documents_count, 0) as rejected_documents_count' : '0 as rejected_documents_count')
        );

        if ($request->has('ajax_table')) {
            try {
                $query = clone $baseQuery;

                if ($request->filled('department')) {
                    $query->where($employeeTable . '.department_id', $request->department);
                }

                if ($request->filled('work_mode')) {
                    $query->where($employeeTable . '.work_mode', $request->work_mode);
                }

                if ($request->filled('employment_type')) {
                    $query->where($employeeTable . '.employment_type', $request->employment_type);
                }

                if ($request->filled('status')) {
                    $status = $request->status;

                    if (in_array($status, ['probation', 'internship', 'permanent'], true)) {
                        $query->where($employeeTable . '.employee_stage', $status);
                    } elseif ($status === 'notice') {
                        if (Schema::hasColumn($employeeTable, 'notice_status')) {
                            $query->where($employeeTable . '.notice_status', 'active');
                        } else {
                            $query->whereNotNull($employeeTable . '.relieving_date');
                        }
                    } else {
                        $query->where($employeeTable . '.employment_status', $status);
                    }
                }

                $totalQuery = clone $baseQuery;
                $recordsTotal = $totalQuery->count();

                $searchValue = $request->input('search.value');

                if (! empty($searchValue)) {
                    $query->where(function ($q) use ($searchValue, $employeeTable) {
                        $q->where('users.name', 'like', "%{$searchValue}%")
                            ->orWhere('users.email', 'like', "%{$searchValue}%")
                            ->orWhere($employeeTable . '.employee_code', 'like', "%{$searchValue}%")
                            ->orWhere('departments.name', 'like', "%{$searchValue}%")
                            ->orWhere('designations.name', 'like', "%{$searchValue}%")
                            ->orWhere('manager_user.name', 'like', "%{$searchValue}%")
                            ->orWhere($employeeTable . '.employment_type', 'like', "%{$searchValue}%")
                            ->orWhere($employeeTable . '.employee_stage', 'like', "%{$searchValue}%")
                            ->orWhere($employeeTable . '.work_mode', 'like', "%{$searchValue}%")
                            ->orWhere($employeeTable . '.employment_status', 'like', "%{$searchValue}%");

                        if (Schema::hasColumn('users', 'phone')) {
                            $q->orWhere('users.phone', 'like', "%{$searchValue}%");
                        }
                    });
                }

                $filteredQuery = clone $query;
                $recordsFiltered = $filteredQuery->count();

                $columns = [
                    0  => 'users.name',
                    1  => 'departments.name',
                    2  => 'designations.name',
                    3  => $employeeTable . '.employment_type',
                    4  => 'manager_user.name',
                    5  => $hasAttendanceTime ? 'attendance_times.name' : $employeeTable . '.id',
                    6  => $profileTable . '.profile_status',
                    7  => $employeeTable . '.employee_stage',
                    8  => $employeeTable . '.joining_date',
                    9  => $employeeTable . '.employment_status',
                    10 => $employeeTable . '.id',
                ];

                $orderColumnIndex = (int) $request->input('order.0.column', 11);
                $orderDirection = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
                $orderColumn = $columns[$orderColumnIndex] ?? $employeeTable . '.id';

                $employees = $query
                    ->orderBy($orderColumn, $orderDirection)
                    ->offset((int) $request->input('start', 0))
                    ->limit((int) $request->input('length', 10))
                    ->get();

                global $preloadedPassportPhotos;
                $preloadedPassportPhotos = [];
                $empIds = $employees->pluck('id')->toArray();
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

                $data = $employees->map(function ($employee) {
                    $name = $employee->name ?: '-';
                    $employeeCode = $employee->employee_code ?: 'EMP-' . $employee->id;
                    $initial = resolveEmployeeInitials($employee);
                    $passportPhotoUrl = resolveEmployeePassportPhoto($employee);

                    if ($passportPhotoUrl) {
                        $avatar = '<span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2"><img src="' . e($passportPhotoUrl) . '" alt="' . e($name) . '" class="hrms-emp-avatar-img" onerror="this.style.display=\'none\'; this.parentElement.querySelector(\'.hrms-emp-avatar-fallback\').classList.remove(\'is-hidden\'); this.parentElement.querySelector(\'.hrms-emp-avatar-fallback\').classList.add(\'is-visible\');"><span class="hrms-emp-avatar-fallback is-hidden">' . e($initial) . '</span></span>';
                    } else {
                        $avatar = '<span class="hrms-emp-avatar hrms-emp-avatar-sm mr-2"><span class="hrms-emp-avatar-fallback is-visible">' . e($initial) . '</span></span>';
                    }

                    $employmentType = ucfirst(str_replace('_', ' ', $employee->employment_type ?? '-'));
                    $workMode = strtoupper($employee->work_mode ?? 'WFO');
                    $stage = ucfirst(str_replace('_', ' ', $employee->employee_stage ?? 'Active'));
                    $status = ucfirst(str_replace('_', ' ', $employee->employment_status ?? 'Active'));

                    $verifiedDocs = (int) ($employee->verified_documents_count ?? 0);
                    $pendingDocs = (int) ($employee->pending_documents_count ?? 0);
                    $rejectedDocs = (int) ($employee->rejected_documents_count ?? 0);
                    $uploadedDocs = (int) ($employee->uploaded_documents_count ?? 0);

                    if ($rejectedDocs > 0) {
                        $verificationStatus = 'Rejected Docs';
                    } elseif ($pendingDocs > 0) {
                        $verificationStatus = 'Pending Docs';
                    } elseif ($uploadedDocs > 0 && $verifiedDocs >= $uploadedDocs) {
                        $verificationStatus = 'Verified';
                    } else {
                        $verificationStatus = 'Approved';
                    }

                    $manager = $employee->manager_name
                        ? e($employee->manager_name) . '<div class="eo-mini">' . e($employee->manager_code ?? '') . '</div>'
                        : '<span class="text-muted">Not assigned</span>';

                    $actions = '<div class="eo-actions-cell eo-action-menu dropdown">
                    <button class="dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">';

                    if (Route::has('hrms.employees.manage')) {
                        $actions .= '<a class="dropdown-item" href="' . route('hrms.employees.manage', $employee->id) . '">
                        <i class="fas fa-user-cog"></i> Manage
                    </a>';
                    }

                    if (Route::has('hrms.employees.show')) {
                        $actions .= '<a class="dropdown-item" href="' . route('hrms.employees.show', $employee->id) . '">
                        <i class="fas fa-eye"></i> View
                    </a>';
                    }

                    if (Route::has('hrms.employees.edit')) {
                        $actions .= '<a class="dropdown-item" href="' . route('hrms.employees.edit', $employee->id) . '">
                        <i class="fas fa-edit"></i> Edit
                    </a>';
                    }

                    if (Route::has('hrms.employees.profile.view')) {
                        $actions .= '<a class="dropdown-item" href="' . route('hrms.employees.profile.view', $employee->id) . '">
                        <i class="fas fa-id-card"></i> Profile / Docs
                    </a>';
                    }

                    $canInitiateExit = auth()->user() && method_exists(auth()->user(), 'hasPermission')
                        && (auth()->user()->hasPermission('employee_exit.initiate') || auth()->user()->hasPermission('employees.update'));
                    if ($canInitiateExit && Route::has('hrms.employees.exit.initiate')) {
                        $actions .= '
                        <form action="' . route('hrms.employees.exit.initiate', $employee->id) . '" method="POST" style="margin:0;">
                            ' . csrf_field() . '
                            <input type="hidden" name="exit_type" value="resignation">
                            <input type="hidden" name="last_working_day" value="' . e(now()->toDateString()) . '">
                            <button type="submit" class="dropdown-item text-warning" onclick="return confirm(\'Initiate exit process for this employee?\')">
                                <i class="fas fa-sign-out-alt"></i> Initiate Exit
                            </button>
                        </form>';
                    }

                    if (auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin() && Route::has('hrms.employees.destroy')) {
                        $actions .= '
                        <form action="' . route('hrms.employees.destroy', $employee->id) . '" method="POST" style="margin:0;" onsubmit="var c=prompt(\'Type DELETE EMPLOYEE to permanently delete this wrong/test/duplicate employee.\'); if(c===null){return false;} this.querySelector(\'input[name=confirm_text]\').value=c; return c===\'DELETE EMPLOYEE\';">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <input type="hidden" name="delete_mode" value="permanent">
                            <input type="hidden" name="confirm_text" value="">
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-trash"></i> Permanent Delete
                            </button>
                        </form>';
                    }

                    $actions .= '</div></div>';

                    return [
                        'employee' => '
                        <div class="eo-emp">
                            ' . $avatar . '
                            <div>
                                <div class="eo-name">' . e($name) . '</div>
                                <div class="eo-meta">' . e($employeeCode) . '</div>
                                <div class="eo-mini">' . e($employee->email ?? '-') . '</div>
                            </div>
                        </div>',
                        'department' => e($employee->department_name ?? 'General'),
                        'designation' => e($employee->designation_name ?? 'Executive'),
                        'employment_type' => e($employmentType),
                        'work_mode' => e($workMode),
                        'reporting_manager' => $manager,
                        'shift' => e($employee->shift_name ?? 'General Shift'),
                        'verification_status' => e($verificationStatus),
                        'employee_stage' => e($stage),
                        'joining_date' => ! empty($employee->joining_date)
                            ? Carbon::parse($employee->joining_date)->format('d M Y')
                            : '-',
                        'status' => e($status),
                        'actions' => $actions,
                    ];
                });

                return response()->json([
                    'draw' => (int) $request->draw,
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $data,
                ]);
            } catch (\Throwable $e) {
                FacadesLog::error('Employee DataTable Error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'draw' => (int) $request->draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        $employees = $baseQuery
            ->orderByDesc($employeeTable . '.id')
            ->paginate(15);

        $departments = DB::table('departments')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $statsBase = DB::table($employeeTable)
            ->leftJoin($profileTable, $profileTable . '.employee_id', '=', $employeeTable . '.id')
            ->where($employeeTable . '.employment_status', 'active')
            ->where($profileTable . '.is_profile_completed', 1)
            ->where($profileTable . '.profile_status', 'approved');

        if (Schema::hasColumn($employeeTable, 'is_active')) {
            $statsBase->where($employeeTable . '.is_active', 1);
        }

        $stats = [
            'total' => (clone $statsBase)->count(),
            'active' => (clone $statsBase)->count(),
            'probation' => Schema::hasColumn($employeeTable, 'employee_stage')
                ? (clone $statsBase)->where($employeeTable . '.employee_stage', 'probation')->count()
                : 0,
            'remote' => Schema::hasColumn($employeeTable, 'work_mode')
                ? (clone $statsBase)->whereIn($employeeTable . '.work_mode', ['wfh', 'hybrid'])->count()
                : 0,
            'docs_pending' => Schema::hasTable('employee_documents_new')
                ? DB::table('employee_documents_new')
                ->join($employeeTable, $employeeTable . '.id', '=', 'employee_documents_new.employee_id')
                ->leftJoin($profileTable, $profileTable . '.employee_id', '=', $employeeTable . '.id')
                ->where($employeeTable . '.employment_status', 'active')
                ->where($profileTable . '.profile_status', 'approved')
                ->whereIn('employee_documents_new.verification_status', ['pending', 'rejected'])
                ->distinct('employee_documents_new.employee_id')
                ->count('employee_documents_new.employee_id')
                : 0,
        ];

        return view('hrms.employee.index', compact('employees', 'departments', 'stats'));
    }

    public function create()
    {
        $formData = $this->employeeService->createFormData();

        $departments = $formData['departments'];
        $designations = $formData['designations'];
        $reportingManagers = $formData['reportingManagers'];
        $roles = $formData['roles'];
        $nextEmployeeCode = $this->employeeService->generateEmployeeCode($this->employeeTable);

        return view('hrms.employee.create', compact(
            'departments',
            'designations',
            'reportingManagers',
            'roles',
            'nextEmployeeCode'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required'],
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'intern', 'freelancer', 'contract'])],
            'work_mode' => ['required', Rule::in(['wfo', 'wfh', 'hybrid'])],
            'work_schedule_type' => ['nullable', Rule::in(['full_day', 'part_day', 'hourly', 'shift_based', 'general', 'wfh', 'part_time', 'half_day', 'half_day_morning', 'half_day_evening'])],
            'department_id' => ['required'],
            'designation_id' => ['required'],
            'system_role_id' => ['required'],
            'actual_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_effective_from' => ['nullable', 'date'],
            'salary_change_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $lifecyclePayload = $this->lifecycleService->buildLifecyclePayload($request->all());

        if ($lifecyclePayload['employee_stage'] !== 'internship' && ! $request->joining_date) {
            return back()->withErrors(['joining_date' => 'Joining date is required.'])->withInput();
        }

        if ($lifecyclePayload['employee_stage'] === 'internship') {
            if (! $request->internship_start_date || ! $request->internship_end_date || $request->is_paid_intern === null) {
                return back()->withErrors(['internship_start_date' => 'Internship details are required.'])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            $employeeCode = $this->employeeService->generateEmployeeCode($this->employeeTable);
            $plainPassword = 'Orbosis@' . now()->year;

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($plainPassword),
                'system_role_id' => $request->system_role_id,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'must_change_password' => true,
            ];

            if (Schema::hasColumn('users', 'phone')) {
                $userData['phone'] = $request->phone;
            }

            $userId = DB::table('users')->insertGetId($userData);
            $passwordSetupUrl = null;

            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $userId, 'role_id' => $request->system_role_id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // Phase 3 - Employee Role Auto Sync
            $employeeRoleId = DB::table('roles')->where('slug', 'employee')->value('id');
            if ($employeeRoleId && (int) $request->system_role_id !== (int) $employeeRoleId) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $userId, 'role_id' => (int) $employeeRoleId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            list($shift, $policyId) = $this->resolveShiftAndPolicyBySchedule($request->work_schedule_type, $request->work_mode, $request->employment_type);
            $dbScheduleType = $this->mapScheduleTypeForDb($request->work_schedule_type ?: ($request->work_mode === 'wfh' ? 'wfh' : ($request->employment_type === 'part_time' ? 'part_time' : 'general')));

            $employeeInsertData = [
                'user_id' => $userId,
                'employee_code' => $employeeCode,
                'system_role_id' => $request->system_role_id,
                'department_id' => $request->department_id,
                'designation_id' => $request->designation_id,
                'reporting_manager_employee_id' => $request->reporting_manager_employee_id,
                'employment_type' => $request->employment_type,
                'employee_stage' => $lifecyclePayload['employee_stage'],
                'work_mode' => $request->work_mode,
                'work_schedule_type' => $dbScheduleType,
                'attendance_policy_rule_id' => $policyId,
                'joining_date' => $lifecyclePayload['joining_date'],
                'employment_status' => 'active',
                'probation_months' => 3,
                'probation_start_date' => $lifecyclePayload['probation_start_date'],
                'probation_end_date' => $lifecyclePayload['probation_end_date'],
                'probation_status' => $lifecyclePayload['probation_status'],
                'internship_start_date' => $lifecyclePayload['internship_start_date'],
                'internship_end_date' => $lifecyclePayload['internship_end_date'],
                'is_paid_intern' => $lifecyclePayload['is_paid_intern'],
                'actual_salary' => $lifecyclePayload['actual_salary'],
                'is_active' => 1,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn($this->employeeTable, 'internship_status')) {
                $employeeInsertData['internship_status'] = $lifecyclePayload['employee_stage'] === 'internship' ? 'active' : null;
            }

            if (Schema::hasColumn($this->employeeTable, 'is_permanent')) {
                $employeeInsertData['is_permanent'] = 0;
            }

            if (Schema::hasColumn($this->employeeTable, 'permanent_at')) {
                $employeeInsertData['permanent_at'] = null;
            }

            $employeeId = DB::table($this->employeeTable)->insertGetId($employeeInsertData);

            if ($policyId) {
                DB::table('employee_policy_assignments')->insert([
                    'employee_id' => $employeeId,
                    'policy_type' => 'attendance',
                    'policy_id' => $policyId,
                    'effective_from' => $lifecyclePayload['joining_date'] ?: Carbon::now('Asia/Kolkata')->toDateString(),
                    'is_active' => 1,
                    'assigned_by_user_id' => auth()->id() ?: 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->salaryHistoryService->syncSalary(
                (int) $employeeId,
                $lifecyclePayload['employee_stage'],
                $lifecyclePayload['actual_salary'],
                $request->salary_effective_from ?: $this->salaryEffectiveDate($lifecyclePayload),
                $this->salaryHistoryReason($lifecyclePayload, $request->salary_change_reason, 'Initial salary'),
                auth()->id()
            );

            DB::table($this->profileTable)->insert([
                'employee_id' => $employeeId,
                'profile_status' => 'pending',
                'is_profile_completed' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->logLifecycle(
                $employeeId,
                'employee created',
                null,
                [
                    'employee_code' => $employeeCode,
                    'employee_stage' => $lifecyclePayload['employee_stage'],
                    'employment_status' => 'active',
                ],
                'Employee onboarding created'
            );

            $allocationEffectiveDate = $lifecyclePayload['employee_stage'] === 'internship'
                ? ($lifecyclePayload['internship_start_date'] ?: now()->toDateString())
                : ($lifecyclePayload['employee_stage'] === 'probation'
                    ? ($lifecyclePayload['probation_start_date'] ?: now()->toDateString())
                    : ($request->confirmation_date ?: ($lifecyclePayload['joining_date'] ?: now()->toDateString())));

            $this->lifecycleService->autoAllocateForStage(
                (int) $employeeId,
                $lifecyclePayload['employee_stage'],
                $allocationEffectiveDate,
                auth()->id()
            );

            DB::commit();

            $recipientEmail = trim((string) $request->email);
            if ($recipientEmail === '') {
                FacadesLog::warning('Employee created without credential email dispatch: missing email', [
                    'employee_code' => $employeeCode,
                    'user_id' => $userId,
                    'employee_id' => $employeeId,
                ]);
            } else {
                try {
                    FacadesLog::info('Sending employee credentials', [
                        'email' => $recipientEmail,
                        'employee_code' => $employeeCode,
                        'queue_connection' => config('queue.default'),
                        'mail_mailer' => config('mail.default'),
                    ]);

                    Mail::to($recipientEmail)->queue(new EmployeeCredentialMail(
                        $request->name,
                        $recipientEmail,
                        $employeeCode,
                        $plainPassword,
                        $passwordSetupUrl
                    ));

                    FacadesLog::info('Employee credential mail queued successfully', [
                        'email' => $recipientEmail,
                        'employee_code' => $employeeCode,
                    ]);
                } catch (\Throwable $mailEx) {
                    FacadesLog::error('Employee credential mail dispatch failed', [
                        'employee_code' => $employeeCode,
                        'email' => $recipientEmail,
                        'error' => $mailEx->getMessage(),
                    ]);
                }
            }

            return redirect()
                ->route('hrms.employees.index')
                ->with('success', 'Employee created. Login credentials sent to email.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function manage($employee)
    {
        $employeeData = $this->findEmployeeProfileRecord($employee);
        abort_if(! $employeeData, 404);

        $this->adjustWorkScheduleTypeForView($employeeData);

        $departments = DB::table('departments')->select('id', 'name')->orderBy('name')->get();

        $designationsQuery = DB::table('designations')
            ->select('id', 'name', 'department_id')
            ->where('department_id', $employeeData->department_id);

        if (Schema::hasColumn('designations', 'is_active')) {
            $designationsQuery->where('is_active', 1);
        }

        $designations = $designationsQuery->orderBy('name')->get();

        $rolesQuery = DB::table('roles')->select('id');

        if (Schema::hasColumn('roles', 'name')) {
            $rolesQuery->addSelect('name as display_name');
        } elseif (Schema::hasColumn('roles', 'title')) {
            $rolesQuery->addSelect('title as display_name');
        } else {
            $rolesQuery->addSelect(DB::raw("CONCAT('Role ', id) as display_name"));
        }

        if (Schema::hasColumn('roles', 'status')) {
            $rolesQuery->where('status', 1);
        }

        $roles = $rolesQuery->orderBy('id')->get();

        $currentManagerId = $employeeData->reporting_manager_employee_id;

        $reportingManagers = DB::table($this->employeeTable)
            ->join('users', 'users.id', '=', $this->employeeTable . '.user_id')
            ->leftJoin($this->profileTable, $this->profileTable . '.employee_id', '=', $this->employeeTable . '.id')
            ->select($this->employeeTable . '.id', 'users.name', $this->employeeTable . '.employee_code')
            ->where($this->employeeTable . '.id', '!=', $employee)
            ->where($this->employeeTable . '.employment_status', 'active')
            ->where(function ($query) use ($currentManagerId) {
                $query->where(function ($q) {
                    $q->where($this->profileTable . '.is_profile_completed', 1)
                      ->where($this->profileTable . '.profile_status', 'approved');
                });
                if ($currentManagerId) {
                    $query->orWhere($this->employeeTable . '.id', $currentManagerId);
                }
            })
            ->orderBy('users.name')
            ->get();

        $salaryHistories = DB::table('employee_salary_histories')
            ->leftJoin('users as creator', 'creator.id', '=', 'employee_salary_histories.created_by')
            ->where('employee_salary_histories.employee_id', $employee)
            ->select('employee_salary_histories.*', 'creator.name as creator_name')
            ->orderByDesc('employee_salary_histories.effective_from')
            ->orderByDesc('employee_salary_histories.id')
            ->get();

        // Load employee documents
        $expType = 'fresher';
        $expVal = strtolower(trim((string) ($employeeData->experience_type ?? '')));
        if (in_array($expVal, ['fresher', 'experienced'], true)) {
            $expType = $expVal;
        } else {
            $expText = strtolower(trim((string) ($employeeData->total_experience ?? '')));
            if ($expText !== '' && preg_match('/\d+(\.\d+)?/', $expText, $matches)) {
                $expType = ((float) $matches[0]) > 0 ? 'experienced' : 'fresher';
            }
        }

        $docTypes = collect();
        if (Schema::hasTable('document_types')) {
            $query = DB::table('document_types')->where('scope', 'employee');
            if (Schema::hasColumn('document_types', 'is_active')) {
                $query->where('is_active', 1);
            }
            if (Schema::hasColumn('document_types', 'applies_to')) {
                $query->whereIn('applies_to', ['all', $expType]);
            }
            $docTypes = $query->get();
        }

        $uploadedDocs = collect();
        if (Schema::hasTable('employee_documents_new')) {
            $uploadedDocs = DB::table('employee_documents_new')
                ->leftJoin('users as verifier', 'verifier.id', '=', 'employee_documents_new.verified_by_user_id')
                ->where('employee_documents_new.employee_id', $employee)
                ->select('employee_documents_new.*', 'verifier.name as verifier_name')
                ->get();
        }

        $documentsList = [];
        foreach ($uploadedDocs as $uploaded) {
            $type = $docTypes->firstWhere('id', $uploaded->document_type_id);
            if (!$type && $uploaded->document_type_id) {
                $type = DB::table('document_types')->where('id', $uploaded->document_type_id)->first();
            }

            $typeName = $type->name ?? $uploaded->title ?? 'Document';
            $typeCode = $type->code ?? null;
            $isMandatory = $type->is_mandatory ?? false;

            $documentsList[] = (object)[
                'id' => $uploaded->id,
                'document_type_id' => $uploaded->document_type_id,
                'document_type_name' => $typeName,
                'code' => $typeCode,
                'is_required' => $isMandatory,
                'title' => $uploaded->title ?: $typeName,
                'file_path' => $uploaded->file_path,
                'file_original_name' => $uploaded->file_original_name,
                'verification_status' => $uploaded->verification_status,
                'rejection_reason' => $uploaded->rejection_reason,
                'verified_at' => $uploaded->verified_at,
                'verified_by_user_id' => $uploaded->verified_by_user_id,
                'verifier_name' => $uploaded->verifier_name,
                'uploaded_at' => $uploaded->created_at ?? null,
                'created_at' => $uploaded->created_at ?? null,
                'is_uploaded' => true,
            ];
        }

        $employeeDocuments = collect($documentsList);

        return view('hrms.employee.manage', compact(
            'employeeData',
            'departments',
            'designations',
            'roles',
            'reportingManagers',
            'salaryHistories',
            'employeeDocuments'
        ));
    }

    public function manageUpdate(Request $request, $employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $employeeData->user_id],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation_id' => ['required', 'exists:designations,id'],
            'reporting_manager_employee_id' => ['nullable', 'exists:employees_new,id'],
            'system_role_id' => ['required', 'exists:roles,id'],

            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'intern', 'freelancer', 'contract'])],
            'work_mode' => ['required', Rule::in(['wfo', 'wfh', 'hybrid'])],
            'work_schedule_type' => ['nullable', Rule::in(['full_day', 'part_day', 'hourly', 'shift_based', 'general', 'wfh', 'part_time', 'half_day', 'half_day_morning', 'half_day_evening'])],
            'employment_status' => ['required', Rule::in(['active', 'resigned', 'terminated', 'inactive'])],

            'joining_date' => ['nullable', 'date'],
            'relieving_date' => ['nullable', 'date'],

            'internship_start_date' => ['nullable', 'date'],
            'internship_end_date' => ['nullable', 'date', 'after_or_equal:internship_start_date'],
            'is_paid_intern' => ['nullable', Rule::in(['0', '1', 0, 1])],

            'probation_months' => ['nullable', 'integer', 'min:1'],
            'probation_start_date' => ['nullable', 'date'],
            'probation_end_date' => ['nullable', 'date'],

            'actual_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_effective_from' => ['nullable', 'date'],
            'salary_change_reason' => ['nullable', 'string', 'max:255'],

            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'address' => ['nullable', 'string'],
            'highest_qualification' => ['nullable', 'string'],
            'cgpa_percentage' => ['nullable', 'string'],
            'total_experience' => ['nullable', 'string'],
            'emergency_contact_number' => ['nullable', 'string', 'max:255'],

            'bank_account_no' => ['nullable', 'string'],
            'bank_account_type' => ['nullable', 'string'],
            'bank_holder_name' => ['nullable', 'string'],
            'ifsc_code' => ['nullable', 'string'],
            'bank_branch' => ['nullable', 'string'],

            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'resume_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $lifecyclePayload = $this->lifecycleService->buildLifecyclePayload(
            $request->all(),
            $employeeData->probation_status,
            $employeeData->employee_stage ?? null,
            true
        );

        if ($lifecyclePayload['employee_stage'] !== 'internship' && ! $request->joining_date) {
            return back()->withErrors(['joining_date' => 'Joining date is required.'])->withInput();
        }

        if ($lifecyclePayload['employee_stage'] === 'internship') {
            if (! $request->internship_start_date || ! $request->internship_end_date || $request->is_paid_intern === null) {
                return back()->withErrors(['internship_start_date' => 'Internship details are required.'])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            $userUpdateData = [
                'name' => $request->name,
                'email' => $request->email,
                'system_role_id' => $request->system_role_id,
                'is_active' => $request->employment_status === 'active' ? 1 : 0,
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('users', 'phone')) {
                $userUpdateData['phone'] = $request->phone;
            }

            $oldSystemRoleId = (int) DB::table('users')->where('id', $employeeData->user_id)->value('system_role_id');

            DB::table('users')->where('id', $employeeData->user_id)->update($userUpdateData);

            // Phase 3 - Employee Role Auto Sync (additive, do not delete existing roles)
            $employeeRoleId = DB::table('roles')->where('slug', 'employee')->value('id');

            if ($oldSystemRoleId > 0 && $oldSystemRoleId !== (int) $request->system_role_id) {
                if (!$employeeRoleId || $oldSystemRoleId !== (int) $employeeRoleId) {
                    DB::table('user_roles')
                        ->where('user_id', $employeeData->user_id)
                        ->where('role_id', $oldSystemRoleId)
                        ->delete();
                }
            }

            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $employeeData->user_id, 'role_id' => (int) $request->system_role_id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            if ($employeeRoleId && (int) $request->system_role_id !== (int) $employeeRoleId) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $employeeData->user_id, 'role_id' => (int) $employeeRoleId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            list($oldShift, $oldPolicyId) = $this->resolveShiftAndPolicyBySchedule($employeeData->work_schedule_type, $employeeData->work_mode, $employeeData->employment_type);
            list($newShift, $newPolicyId) = $this->resolveShiftAndPolicyBySchedule($request->work_schedule_type, $request->work_mode, $request->employment_type);
            $dbScheduleType = $this->mapScheduleTypeForDb($request->work_schedule_type ?: ($request->work_mode === 'wfh' ? 'wfh' : ($request->employment_type === 'part_time' ? 'part_time' : 'general')));

            $employeeUpdateData = [
                'system_role_id' => $request->system_role_id,
                'department_id' => $request->department_id,
                'designation_id' => $request->designation_id,
                'employment_type' => $request->employment_type,
                'employee_stage' => $lifecyclePayload['employee_stage'],
                'work_mode' => $request->work_mode,
                'work_schedule_type' => $dbScheduleType,
                'attendance_policy_rule_id' => $newPolicyId,
                'employment_status' => $request->employment_status,
                'joining_date' => $lifecyclePayload['joining_date'],
                'relieving_date' => $lifecyclePayload['relieving_date'],
                'probation_start_date' => $lifecyclePayload['probation_start_date'],
                'probation_end_date' => $lifecyclePayload['probation_end_date'],
                'probation_status' => $lifecyclePayload['probation_status'],
                'internship_start_date' => $lifecyclePayload['internship_start_date'],
                'internship_end_date' => $lifecyclePayload['internship_end_date'],
                'is_paid_intern' => $lifecyclePayload['is_paid_intern'],
                'actual_salary' => $lifecyclePayload['actual_salary'],
                'is_active' => $request->employment_status === 'active' ? 1 : 0,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn($this->employeeTable, 'internship_status')) {
                $employeeUpdateData['internship_status'] = $lifecyclePayload['employee_stage'] === 'internship'
                    ? ($employeeData->internship_status ?: 'active')
                    : $employeeData->internship_status;
            }

            if (Schema::hasColumn($this->employeeTable, 'is_permanent')) {
                $employeeUpdateData['is_permanent'] = $lifecyclePayload['employee_stage'] === 'permanent'
                    ? 1
                    : ((int)($employeeData->is_permanent ?? 0));
            }

            if (
                Schema::hasColumn($this->employeeTable, 'permanent_at')
                && $lifecyclePayload['employee_stage'] === 'permanent'
                && empty($employeeData->permanent_at)
            ) {
                $employeeUpdateData['permanent_at'] = now()->toDateString();
            }

            if ($request->has('reporting_manager_employee_id')) {
                $employeeUpdateData['reporting_manager_employee_id'] = $request->filled('reporting_manager_employee_id')
                    ? $request->reporting_manager_employee_id
                    : null;
            }

            DB::table($this->employeeTable)->where('id', $employee)->update($employeeUpdateData);

            if (($oldPolicyId !== $newPolicyId || !DB::table('employee_policy_assignments')->where('employee_id', $employee)->where('policy_type', 'attendance')->where('is_active', 1)->exists()) && $newPolicyId) {
                $today = Carbon::now('Asia/Kolkata')->toDateString();
                $yesterday = Carbon::now('Asia/Kolkata')->subDay()->toDateString();

                // Deactivate/end existing active assignments
                $activeAssignments = DB::table('employee_policy_assignments')
                    ->where('employee_id', $employee)
                    ->where('policy_type', 'attendance')
                    ->where('is_active', 1)
                    ->get();

                foreach ($activeAssignments as $assignment) {
                    if ($assignment->effective_from && Carbon::parse($assignment->effective_from)->gte(Carbon::parse($today))) {
                        // Starts today or in future - delete it
                        DB::table('employee_policy_assignments')
                            ->where('id', $assignment->id)
                            ->delete();
                    } else {
                        // Ends yesterday
                        DB::table('employee_policy_assignments')
                            ->where('id', $assignment->id)
                            ->update([
                                'effective_to' => $yesterday,
                                'updated_at' => now(),
                            ]);
                    }
                }

                // Insert new assignment starting today
                DB::table('employee_policy_assignments')->insert([
                    'employee_id' => $employee,
                    'policy_type' => 'attendance',
                    'policy_id' => $newPolicyId,
                    'effective_from' => $today,
                    'is_active' => 1,
                    'assigned_by_user_id' => auth()->id() ?: 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $oldSalary = round((float)($employeeData->actual_salary ?? 0), 2);
            $newSalary = round((float)($lifecyclePayload['actual_salary'] ?? 0), 2);

            $shouldSyncSalary =
                $oldSalary !== $newSalary
                || $request->filled('salary_effective_from')
                || $request->filled('salary_change_reason');

            if ($shouldSyncSalary) {
                $this->salaryHistoryService->syncSalary(
                    (int) $employee,
                    $lifecyclePayload['employee_stage'],
                    $lifecyclePayload['actual_salary'],
                    $request->salary_effective_from ?: $this->salaryEffectiveDate($lifecyclePayload),
                    $this->salaryHistoryReason($lifecyclePayload, $request->salary_change_reason, 'Salary update'),
                    auth()->id()
                );
            }

            $oldProfile = DB::table($this->profileTable)->where('employee_id', $employee)->first();

            $oldStatus = $oldProfile->profile_status ?? 'pending';
            $oldCompleted = (int)($oldProfile->is_profile_completed ?? 0);

            $profileData = [
                'employee_id' => $employee,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'highest_qualification' => $request->highest_qualification,
                'cgpa_percentage' => $request->cgpa_percentage,
                'total_experience' => $request->total_experience,
                'emergency_contact_number' => $request->emergency_contact_number,
                'bank_account_no' => $request->bank_account_no,
                'bank_account_type' => $request->bank_account_type,
                'bank_holder_name' => $request->bank_holder_name,
                'ifsc_code' => $request->ifsc_code ? strtoupper($request->ifsc_code) : null,
                'bank_branch' => $request->bank_branch,

                // Preserve existing approval state on admin manage update.
                'profile_status' => $oldStatus,
                'is_profile_completed' => $oldCompleted,
                'profile_completed_at' => $oldProfile->profile_completed_at ?? null,
                'rejection_reason' => $oldProfile->rejection_reason ?? null,
                'updated_at' => now(),
            ];

            if ($oldProfile && $oldStatus === 'approved') {
                $profileData['approved_by_user_id'] = $oldProfile->approved_by_user_id ?? null;
                $profileData['approved_at'] = $oldProfile->approved_at ?? null;
            } else {
                $profileData['approved_by_user_id'] = null;
                $profileData['approved_at'] = null;
            }

            if (! $oldProfile) {
                $profileData['profile_status'] = 'submitted';
                $profileData['is_profile_completed'] = 0;
                $profileData['profile_completed_at'] = null;
                $profileData['approved_by_user_id'] = null;
                $profileData['approved_at'] = null;
                $profileData['rejection_reason'] = null;
                $profileData['created_at'] = now();
            }

            $fileService = app(EmployeeFileS::class);

            if ($request->hasFile('profile_image')) {
                $profileData['profile_image'] = $fileService->upload(
                    $request->file('profile_image'),
                    $employee,
                    $employeeData->employee_code,
                    'profile'
                );
            }

            if ($request->hasFile('resume_file')) {
                $profileData['resume_file'] = $fileService->upload(
                    $request->file('resume_file'),
                    $employee,
                    $employeeData->employee_code,
                    'resume'
                );
            }

            DB::table($this->profileTable)->updateOrInsert(
                ['employee_id' => $employee],
                $profileData
            );

            $this->logLifecycle(
                $employee,
                'employee updated',
                $employeeData,
                $employeeUpdateData,
                'Employee details updated'
            );

            DB::commit();

            return redirect()
                ->route('hrms.employees.manage', $employee)
                ->with('success', 'Employee details updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($employee)
    {
        $employeeData = $this->findEmployeeProfileRecord($employee);

        abort_if(! $employeeData, 404);

        $this->adjustWorkScheduleTypeForView($employeeData);

        $salaryHistories = DB::table('employee_salary_histories')
            ->leftJoin('users as creator', 'creator.id', '=', 'employee_salary_histories.created_by')
            ->where('employee_salary_histories.employee_id', $employee)
            ->select('employee_salary_histories.*', 'creator.name as creator_name')
            ->orderByDesc('employee_salary_histories.effective_from')
            ->orderByDesc('employee_salary_histories.id')
            ->get();

        // 1. Calculate experience type
        $expType = 'fresher';
        $expVal = strtolower(trim((string) ($employeeData->experience_type ?? '')));
        if (in_array($expVal, ['fresher', 'experienced'], true)) {
            $expType = $expVal;
        } else {
            $expText = strtolower(trim((string) ($employeeData->total_experience ?? '')));
            if ($expText !== '' && preg_match('/\d+(\.\d+)?/', $expText, $matches)) {
                $expType = ((float) $matches[0]) > 0 ? 'experienced' : 'fresher';
            }
        }

        // 2. Fetch all active document types for scope = employee
        $docTypes = collect();
        if (Schema::hasTable('document_types')) {
            $query = DB::table('document_types')->where('scope', 'employee');
            if (Schema::hasColumn('document_types', 'is_active')) {
                $query->where('is_active', 1);
            }
            if (Schema::hasColumn('document_types', 'applies_to')) {
                $query->whereIn('applies_to', ['all', $expType]);
            }
            $docTypes = $query->get();
        }

        // 3. Fetch uploaded documents
        $uploadedDocs = collect();
        if (Schema::hasTable('employee_documents_new')) {
            $uploadedDocs = DB::table('employee_documents_new')
                ->leftJoin('users as verifier', 'verifier.id', '=', 'employee_documents_new.verified_by_user_id')
                ->where('employee_documents_new.employee_id', $employee)
                ->select('employee_documents_new.*', 'verifier.name as verifier_name')
                ->get();
        }

        // 4. Map only uploaded documents
        $documentsList = [];
        foreach ($uploadedDocs as $uploaded) {
            // Find corresponding document type if it exists
            $type = $docTypes->firstWhere('id', $uploaded->document_type_id);
            if (!$type && $uploaded->document_type_id) {
                $type = DB::table('document_types')->where('id', $uploaded->document_type_id)->first();
            }

            $typeName = $type->name ?? $uploaded->title ?? 'Document';
            $typeCode = $type->code ?? null;
            $isMandatory = $type->is_mandatory ?? false;

            $documentsList[] = [
                'id' => $uploaded->id,
                'document_type_id' => $uploaded->document_type_id,
                'name' => $typeName,
                'code' => $typeCode,
                'is_mandatory' => $isMandatory,
                'title' => $uploaded->title ?: $typeName,
                'file_path' => $uploaded->file_path,
                'file_original_name' => $uploaded->file_original_name,
                'verification_status' => $uploaded->verification_status,
                'rejection_reason' => $uploaded->rejection_reason,
                'verified_at' => $uploaded->verified_at,
                'verified_by_user_id' => $uploaded->verified_by_user_id,
                'verifier_name' => $uploaded->verifier_name,
                'uploaded_at' => $uploaded->created_at ?? null,
                'is_uploaded' => true,
            ];
        }

        $documents = collect($documentsList);

        return view('hrms.employee.show', compact('employeeData', 'salaryHistories', 'documents'));
    }

    public function edit($employee)
    {
        $employeeData = $this->findEmployeeProfileRecord($employee);

        abort_if(! $employeeData, 404);

        $this->adjustWorkScheduleTypeForView($employeeData);

        $formData = $this->employeeService->createFormData($employeeData->reporting_manager_employee_id);

        $departments = $formData['departments'];
        $designations = $formData['designations'];
        $reportingManagers = $formData['reportingManagers'];
        $roles = $formData['roles'];

        return view('hrms.employee.edit', compact(
            'employeeData',
            'departments',
            'designations',
            'reportingManagers',
            'roles'
        ));
    }

    public function update(Request $request, $employee)
    {
        return $this->manageUpdate($request, $employee);
    }

    public function pendingProfiles()
    {
        $documentStats = DB::table('employee_documents_new')
            ->select(
                'employee_id',
                DB::raw('COUNT(*) as uploaded_documents_count'),
                DB::raw("SUM(CASE WHEN verification_status = 'verified' THEN 1 ELSE 0 END) as verified_documents_count"),
                DB::raw("SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) as pending_documents_count"),
                DB::raw("SUM(CASE WHEN verification_status = 'rejected' THEN 1 ELSE 0 END) as rejected_documents_count")
            )
            ->groupBy('employee_id');

        $employees = DB::table($this->employeeTable)
            ->join('users', 'users.id', '=', $this->employeeTable . '.user_id')
            ->leftJoin('departments', 'departments.id', '=', $this->employeeTable . '.department_id')
            ->leftJoin('designations', 'designations.id', '=', $this->employeeTable . '.designation_id')
            ->leftJoin($this->profileTable, $this->profileTable . '.employee_id', '=', $this->employeeTable . '.id')
            ->leftJoinSub($documentStats, 'doc_stats', function ($join) {
                $join->on('doc_stats.employee_id', '=', $this->employeeTable . '.id');
            })
            ->where($this->employeeTable . '.employment_status', 'active')
            ->where(function ($q) {
                $q->whereNull($this->profileTable . '.id')
                    ->orWhere($this->profileTable . '.is_profile_completed', 0)
                    ->orWhereIn($this->profileTable . '.profile_status', ['pending', 'submitted', 'rejected'])
                    ->orWhereRaw('COALESCE(doc_stats.pending_documents_count, 0) > 0')
                    ->orWhereRaw('COALESCE(doc_stats.rejected_documents_count, 0) > 0');
            })
            ->select(
                $this->employeeTable . '.id',
                $this->employeeTable . '.employee_code',
                'users.name',
                'users.email',
                'departments.name as department_name',
                'designations.name as designation_name',
                DB::raw("COALESCE({$this->profileTable}.profile_status, 'pending') as profile_status"),
                DB::raw("COALESCE({$this->profileTable}.is_profile_completed, 0) as is_profile_completed"),
                DB::raw('COALESCE(doc_stats.uploaded_documents_count, 0) as uploaded_documents_count'),
                DB::raw('COALESCE(doc_stats.verified_documents_count, 0) as verified_documents_count'),
                DB::raw('COALESCE(doc_stats.pending_documents_count, 0) as pending_documents_count'),
                DB::raw('COALESCE(doc_stats.rejected_documents_count, 0) as rejected_documents_count'),
                $this->profileTable . '.updated_at'
            )
            ->orderByDesc($this->employeeTable . '.id')
            ->get();

        $allCounts = DB::table($this->employeeTable)
            ->leftJoin($this->profileTable, $this->profileTable . '.employee_id', '=', $this->employeeTable . '.id')
            ->where($this->employeeTable . '.employment_status', 'active')
            ->select(
                DB::raw('COUNT(DISTINCT ' . $this->employeeTable . '.id) as total'),
                DB::raw("SUM(CASE WHEN {$this->profileTable}.profile_status IS NULL OR {$this->profileTable}.profile_status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN {$this->profileTable}.profile_status = 'submitted' THEN 1 ELSE 0 END) as submitted"),
                DB::raw("SUM(CASE WHEN {$this->profileTable}.profile_status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN {$this->profileTable}.profile_status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            )
            ->first();

        return view('hrms.employee.profile.pending', [
            'employees' => $employees,
            'total' => $allCounts->total ?? 0,
            'pending' => $allCounts->pending ?? 0,
            'submitted' => $allCounts->submitted ?? 0,
            'approved' => $allCounts->approved ?? 0,
            'rejected' => $allCounts->rejected ?? 0,
        ]);
    }

    public function viewProfile($id)
    {
        $profile = $this->findEmployeeProfileRecord($id);

        abort_if(! $profile, 404);

        $documents = collect();

        if (Schema::hasTable('employee_documents_new')) {
            $documents = DB::table('employee_documents_new')
                ->leftJoin('document_types', 'document_types.id', '=', 'employee_documents_new.document_type_id')
                ->where('employee_documents_new.employee_id', $id)
                ->select(
                    'employee_documents_new.*',
                    DB::raw("COALESCE(document_types.name, employee_documents_new.title, 'Document') as document_type_name"),
                    'document_types.code as document_type_code'
                )
                ->orderByDesc('employee_documents_new.is_required')
                ->orderBy('document_type_name')
                ->get();
        }

        return view('hrms.employee.profile.view', compact('profile', 'documents'));
    }

    public function editProfile($id)
    {
        $profile = $this->findEmployeeProfileRecord($id);

        abort_if(! $profile, 404);

        return view('hrms.employee.profile.edit', compact('profile'));
    }

    public function updateProfile(Request $request, $id)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $id)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'address' => ['required', 'string'],
            'highest_qualification' => ['required', 'string'],
            'cgpa_percentage' => ['required', 'string'],
            'experience_type' => ['required', 'string', 'in:fresher,experienced'],
            'total_experience' => [$request->experience_type === 'fresher' ? 'nullable' : 'required', 'string'],
            'bank_account_no' => ['required', 'string'],
            'bank_account_type' => ['required', 'string'],
            'bank_holder_name' => ['required', 'string'],
            'ifsc_code' => ['required', 'string'],
            'bank_branch' => ['required', 'string'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'resume_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        DB::beginTransaction();

        try {
            $oldProfile = DB::table($this->profileTable)->where('employee_id', $id)->first();

            $profileData = [
                'employee_id' => $id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'highest_qualification' => $request->highest_qualification,
                'cgpa_percentage' => $request->cgpa_percentage,
                'experience_type' => $request->experience_type,
                'total_experience' => $request->experience_type === 'fresher' ? '0' : $request->total_experience,
                'bank_account_no' => $request->bank_account_no,
                'bank_account_type' => $request->bank_account_type,
                'bank_holder_name' => $request->bank_holder_name,
                'ifsc_code' => strtoupper($request->ifsc_code),
                'bank_branch' => $request->bank_branch,
                'profile_status' => 'submitted',
                'is_profile_completed' => 0,
                'approved_by_user_id' => null,
                'approved_at' => null,
                'updated_at' => now(),
            ];

            if (! $oldProfile) {
                $profileData['created_at'] = now();
            }

            $fileService = app(EmployeeFileS::class);

            if ($request->hasFile('profile_image')) {
                $profileData['profile_image'] = $fileService->upload(
                    $request->file('profile_image'),
                    $id,
                    $employeeData->employee_code,
                    'profile'
                );
            }

            if ($request->hasFile('resume_file')) {
                $profileData['resume_file'] = $fileService->upload(
                    $request->file('resume_file'),
                    $id,
                    $employeeData->employee_code,
                    'resume'
                );
            }

            DB::table($this->profileTable)->updateOrInsert(
                ['employee_id' => $id],
                $profileData
            );

            $this->logLifecycle(
                $id,
                'profile submitted',
                $oldProfile,
                ['profile_status' => 'submitted', 'is_profile_completed' => 0],
                'Profile submitted for HR approval'
            );

            if (($oldProfile->profile_status ?? null) !== 'submitted') {
                $deptName = DB::table('departments')->where('id', $employeeData->department_id)->value('name') ?: 'N/A';
                $empCode = $employeeData->employee_code ?? 'N/A';
                $empName = DB::table('users')->where('id', $employeeData->user_id)->value('name') ?: $empCode;
                $subDate = now()->toFormattedDateString();

                app(\App\Services\HRMS\Notification\NotificationS::class)->notifyHrAndSuperAdmin(
                    'Employee Profile Submitted for Verification',
                    "Profile submitted for verification.\nEmployee: {$empName} ({$empCode})\nDepartment: {$deptName}\nDate: {$subDate}",
                    'profile_submitted',
                    'hrms.employees.profile.view',
                    ['employee' => $id],
                    [
                        'employee_id' => $id,
                        'user_id' => $employeeData->user_id,
                        'employee_code' => $employeeData->employee_code,
                        'redirect_type' => 'profile_view',
                        'notification_type' => 'profile_submitted',
                        'action_url' => route('hrms.employees.profile.view', ['employee' => $id]),
                        'route_name' => 'hrms.employees.profile.view',
                        'route_params' => ['employee' => $id],
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->route('hrms.employees.pending_profiles')
                ->with('success', 'Profile updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function approveProfile($employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        DB::beginTransaction();

        try {
            $oldProfile = DB::table($this->profileTable)
                ->where('employee_id', $employee)
                ->first();

            DB::table($this->profileTable)->updateOrInsert(
                ['employee_id' => $employee],
                [
                    'profile_status' => 'approved',
                    'is_profile_completed' => 1,
                    'profile_completed_at' => now(),
                    'approved_by_user_id' => auth()->id(),
                    'approved_at' => now(),
                    'rejection_reason' => null,
                    'updated_at' => now(),
                ]
            );

            DB::table('employee_documents_new')
                ->where('employee_id', $employee)
                ->update([
                    'verification_status' => 'verified',
                    'verified_by_user_id' => auth()->id(),
                    'verified_at' => now(),
                    'rejection_reason' => null,
                    'updated_at' => now(),
                ]);

            $this->logLifecycle(
                $employee,
                'profile approved',
                $oldProfile,
                [
                    'profile_status' => 'approved',
                    'is_profile_completed' => 1,
                    'documents_status' => 'verified',
                ],
                'Profile and all uploaded documents approved by HR'
            );

            app(\App\Services\HRMS\Notification\NotificationS::class)->notifyEmployee(
                'Profile Approved',
                'Your profile has been approved. You can now Punch In/Out and mark attendance.',
                'profile_approved',
                'punch_in_out',
                [],
                [
                    'employee_id' => $employeeData->id,
                    'user_id' => $employeeData->user_id,
                    'redirect_type' => 'attendance',
                ],
                $employeeData->user_id
            );

            DB::commit();

            app(\App\Services\HRMS\Employee\EmployeeProfileS::class)->checkAndSendAllDocumentsVerifiedEmail((int)$employee);

            return redirect()
                ->route('hrms.employees.pending_profiles')
                ->with('success', 'Profile approved and all uploaded documents verified successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectProfile(Request $request, $employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();

        try {
            $oldProfile = DB::table($this->profileTable)->where('employee_id', $employee)->first();

            DB::table($this->profileTable)->updateOrInsert(
                ['employee_id' => $employee],
                [
                    'profile_status' => 'rejected',
                    'is_profile_completed' => 0,
                    'approved_by_user_id' => null,
                    'approved_at' => null,
                    'rejection_reason' => $request->rejection_reason ?: 'Profile rejected by HR',
                    'profile_completed_at' => null,
                    'updated_at' => now(),
                ]
            );

            $this->logLifecycle(
                $employee,
                'profile rejected',
                $oldProfile,
                [
                    'profile_status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason ?: 'Profile rejected by HR',
                ],
                $request->rejection_reason ?: 'Profile rejected by HR'
            );

            app(\App\Services\HRMS\Notification\NotificationS::class)->notifyEmployee(
                'Profile Rejected',
                'Your profile/documents were rejected. Please update and resubmit.',
                'profile_rejected',
                'profile_completion',
                [],
                [
                    'employee_id' => $employeeData->id,
                    'user_id' => $employeeData->user_id,
                    'rejection_reason' => $request->rejection_reason ?: 'Profile rejected by HR',
                ],
                $employeeData->user_id
            );

            DB::commit();

            return back()->with('success', 'Profile rejected successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function completeProfile($employee)
    {
        return redirect()->route('hrms.employees.manage', $employee);
    }

    public function storeProfile(Request $request, $employee)
    {
        return $this->manageUpdate($request, $employee);
    }

    public function probationInternship()
    {
        $employees = DB::table($this->employeeTable)
            ->join('users', 'users.id', '=', $this->employeeTable . '.user_id')
            ->leftJoin('departments', 'departments.id', '=', $this->employeeTable . '.department_id')
            ->leftJoin('designations', 'designations.id', '=', $this->employeeTable . '.designation_id')
            ->select(
                $this->employeeTable . '.id',
                $this->employeeTable . '.employee_code',
                'users.name',
                'departments.name as department_name',
                'designations.name as designation_name',
                $this->employeeTable . '.employment_type',
                $this->employeeTable . '.employee_stage',
                $this->employeeTable . '.joining_date',
                $this->employeeTable . '.probation_start_date',
                $this->employeeTable . '.probation_end_date',
                $this->employeeTable . '.probation_status',
                $this->employeeTable . '.internship_start_date',
                $this->employeeTable . '.internship_end_date',
                $this->employeeTable . '.internship_extended_to',
                $this->employeeTable . '.internship_status',
                $this->employeeTable . '.is_paid_intern',
                $this->employeeTable . '.is_permanent',
                $this->employeeTable . '.permanent_at'
            )
            ->where($this->employeeTable . '.employment_status', 'active')
            ->where($this->employeeTable . '.is_active', 1)
            ->where(function ($q) {
                $q->where(function ($probation) {
                    $probation->where(function ($stage) {
                        $stage->where($this->employeeTable . '.employee_stage', 'probation')
                            ->orWhere(function ($legacy) {
                                $legacy->whereNull($this->employeeTable . '.employee_stage')
                                    ->where($this->employeeTable . '.employment_type', 'full_time');
                            });
                    })
                        ->where(function ($activeProbation) {
                            $activeProbation->whereNull($this->employeeTable . '.probation_status')
                                ->orWhereNotIn($this->employeeTable . '.probation_status', ['completed', 'confirmed']);
                        });
                })
                    ->orWhere(function ($internship) {
                        $internship->where(function ($stage) {
                            $stage->where($this->employeeTable . '.employee_stage', 'internship')
                                ->orWhere(function ($legacy) {
                                    $legacy->whereNull($this->employeeTable . '.employee_stage')
                                        ->where($this->employeeTable . '.employment_type', 'intern');
                                });
                        });
                    });
            })
            ->orderByDesc($this->employeeTable . '.id')
            ->get();

        $departments = DB::table('departments')->select('id', 'name')->orderBy('name')->get();

        return view('hrms.employee.probation_internship.index', compact('employees', 'departments'));
    }

    public function exitEmployees()
    {
        $employees = DB::table($this->employeeTable)
            ->join('users', 'users.id', '=', $this->employeeTable . '.user_id')
            ->leftJoin('departments', 'departments.id', '=', $this->employeeTable . '.department_id')
            ->leftJoin('designations', 'designations.id', '=', $this->employeeTable . '.designation_id')
            ->leftJoin('employee_exit_processes', 'employee_exit_processes.employee_id', '=', $this->employeeTable . '.id')
            ->select(
                $this->employeeTable . '.id',
                $this->employeeTable . '.employee_code',
                'users.name',
                'users.email',
                'departments.name as department_name',
                'designations.name as designation_name',
                $this->employeeTable . '.employment_status',
                $this->employeeTable . '.joining_date',
                $this->employeeTable . '.relieving_date',
                $this->employeeTable . '.is_active',
                'employee_exit_processes.id as exit_process_id',
                'employee_exit_processes.exit_type',
                'employee_exit_processes.status as exit_status',
                'employee_exit_processes.final_status',
                'employee_exit_processes.document_status',
                'employee_exit_processes.handover_status',
                'employee_exit_processes.asset_handover_status',
                'employee_exit_processes.fnf_status',
                'employee_exit_processes.last_working_day'
            )
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->whereNotNull('employee_exit_processes.id')
                        ->whereNotIn('employee_exit_processes.status', ['cancelled', 'rejected', 'rolled_back']);
                })
                ->orWhereIn($this->employeeTable . '.employment_status', ['terminated', 'absconded']);
            })
            ->orderByDesc($this->employeeTable . '.id')
            ->get();

        foreach ($employees as $emp) {
            if ($emp->exit_process_id) {
                // Fetch clearance list
                $clearanceList = DB::table('employee_exit_clearances')
                    ->where('exit_process_id', $emp->exit_process_id)
                    ->get();
                
                // If it's empty, initialize them dynamically for safety
                if ($clearanceList->isEmpty()) {
                    $this->exitProcessService->initializeClearances($emp->exit_process_id);
                    $clearanceList = DB::table('employee_exit_clearances')
                        ->where('exit_process_id', $emp->exit_process_id)
                        ->get();
                }
                
                $emp->clearances = $clearanceList->keyBy('department_key');
                
                // Fetch module summary for auto verification display
                $emp->module_summary = $this->exitProcessService->getModuleSummary($emp->id, $emp);
            } else {
                $emp->clearances = collect();
                $emp->module_summary = [];
            }
        }

        return view('hrms.employee.exit.index', compact('employees'));
    }

    public function reportingStructure(Request $request)
    {
        $employeeTable = $this->employeeTable;
        $profileTable = $this->profileTable;

        $query = DB::table($employeeTable)
            ->join('users', 'users.id', '=', $employeeTable . '.user_id')
            ->leftJoin('departments', 'departments.id', '=', $employeeTable . '.department_id')
            ->leftJoin('designations', 'designations.id', '=', $employeeTable . '.designation_id')
            ->leftJoin($profileTable, $profileTable . '.employee_id', '=', $employeeTable . '.id');

        $query->select(
            $employeeTable . '.id',
            $employeeTable . '.employee_code',
            $employeeTable . '.reporting_manager_employee_id',
            $employeeTable . '.employment_status',
            'users.name',
            'users.email',
            'departments.name as department_name',
            'designations.name as designation_name',
            $profileTable . '.profile_image'
        );

        if (Schema::hasColumn($employeeTable, 'is_active')) {
            $query->where($employeeTable . '.is_active', 1);
        }
        
        $query->where($employeeTable . '.employment_status', 'active');

        $query->where(function ($q) use ($profileTable) {
            $q->whereNull($profileTable . '.employee_id')
              ->orWhere($profileTable . '.profile_status', 'approved');
        });

        $employees = $query->get();

        $departments = $employees->pluck('department_name')->filter()->unique()->values();
        $designations = $employees->pluck('designation_name')->filter()->unique()->values();

        return view('hrms.employee.reporting_structure.index', compact('employees', 'departments', 'designations'));
    }

    public function markPermanent(Request $request, $employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'actual_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_change_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $probationEnd = $employeeData->probation_end_date
            ? \Carbon\Carbon::parse($employeeData->probation_end_date)
            : \Carbon\Carbon::now();

        $permanentEffectiveDate = $probationEnd->copy()->addDay()->toDateString();
        $isFuture = $employeeData->probation_end_date && \Carbon\Carbon::now()->lt(\Carbon\Carbon::parse($employeeData->probation_end_date));

        DB::beginTransaction();

        try {
            if ($isFuture) {
                // Future confirmation: Schedule it!
                $updateData = [
                    'probation_status' => 'scheduled_permanent',
                    'confirmation_effective_date' => $permanentEffectiveDate,
                    'permanent_scheduled_by_user_id' => auth()->id(),
                    'permanent_scheduled_at' => now(),
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ];

                DB::table($this->employeeTable)->where('id', $employee)->update($updateData);

                // Notify HR/Super Admin & Employee
                $empName = DB::table('users')->where('id', $employeeData->user_id)->value('name') ?: 'Employee';
                
                app(\App\Services\HRMS\Notification\NotificationS::class)
                    ->notifyEmployee(
                        'Permanent Confirmation Scheduled',
                        'Your permanent confirmation has been scheduled from ' . \Carbon\Carbon::parse($permanentEffectiveDate)->format('d M Y') . '.',
                        'permanent_scheduled',
                        null,
                        [],
                        [],
                        $employeeData->user_id
                    );

                app(\App\Services\HRMS\Notification\NotificationS::class)
                    ->notifyHrAndSuperAdmin(
                        'Permanent Confirmation Scheduled',
                        'Permanent confirmation has been scheduled for ' . $empName . ' from ' . \Carbon\Carbon::parse($permanentEffectiveDate)->format('d M Y') . '.',
                        'permanent_scheduled',
                        null,
                        [],
                        ['employee_id' => $employee]
                    );

                $this->logLifecycle(
                    $employee,
                    'scheduled permanent',
                    $employeeData,
                    $updateData,
                    'Employee scheduled for permanent status effective from ' . $permanentEffectiveDate
                );

                DB::commit();

                return redirect()
                    ->route('hrms.employees.probation_internship')
                    ->with('success', 'Permanent confirmation scheduled. Effective from ' . \Carbon\Carbon::parse($permanentEffectiveDate)->format('d M Y') . '.');
            } else {
                // Immediate confirmation: Activate now!
                $updateData = [
                    'probation_status' => 'completed',
                    'employee_stage' => 'permanent',
                    'confirmation_date' => today()->toDateString(),
                    'permanent_activated_at' => now(),
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn($this->employeeTable, 'is_permanent')) {
                    $updateData['is_permanent'] = 1;
                }

                if (Schema::hasColumn($this->employeeTable, 'permanent_at')) {
                    $updateData['permanent_at'] = today()->toDateString();
                }

                DB::table($this->employeeTable)->where('id', $employee)->update($updateData);

                // Run leave allocation immediately
                $empModel = \App\Models\HRMS\Employee\EmployeeM::find($employee);
                if ($empModel) {
                    $empModel->confirmation_date = $updateData['confirmation_date'];
                    $empModel->employee_stage = 'permanent';
                    app(\App\Services\HRMS\Leave\LeaveAllocationService::class)->generateForEmployee(
                        $empModel,
                        (int) now()->year,
                        auth()->id(),
                        'permanent',
                        \Carbon\Carbon::parse($updateData['confirmation_date'], 'Asia/Kolkata')
                    );
                }

                if ($request->filled('actual_salary')) {
                    $this->salaryHistoryService->syncSalary(
                        (int) $employee,
                        'permanent',
                        $request->actual_salary,
                        today()->toDateString(),
                        $request->salary_change_reason ?: 'Permanent salary update',
                        auth()->id()
                    );
                }

                app(\App\Services\HRMS\Notification\NotificationS::class)
                    ->markEmployeeLifecycleNotificationsResolved((int) $employee, ['probation_ending_soon', 'probation_ending_reminder']);

                // Notify HR/Super Admin & Employee
                $empName = DB::table('users')->where('id', $employeeData->user_id)->value('name') ?: 'Employee';
                
                app(\App\Services\HRMS\Notification\NotificationS::class)
                    ->notifyEmployee(
                        'Permanent Confirmation Activated',
                        'Your permanent confirmation has been activated successfully.',
                        'permanent_activated',
                        null,
                        [],
                        [],
                        $employeeData->user_id
                    );

                app(\App\Services\HRMS\Notification\NotificationS::class)
                    ->notifyHrAndSuperAdmin(
                        'Permanent Confirmation Activated',
                        'Permanent confirmation has been activated for ' . $empName . '.',
                        'permanent_activated',
                        null,
                        [],
                        ['employee_id' => $employee]
                    );

                $this->logLifecycle(
                    $employee,
                    'marked permanent',
                    $employeeData,
                    $updateData,
                    'Employee marked permanent immediately effective from ' . today()->toDateString()
                );

                DB::commit();

                return redirect()
                    ->route('hrms.employees.probation_internship')
                    ->with('success', 'Employee marked permanent successfully.');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function extendInternship(Request $request, $employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'internship_extended_to' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'actual_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_change_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $currentEndDate = $employeeData->internship_extended_to ?: $employeeData->internship_end_date;

        if (! empty($currentEndDate) && Carbon::parse($request->internship_extended_to)->lte(Carbon::parse($currentEndDate))) {
            return back()->with(
                'error',
                'Extension date must be after current internship end date ' . Carbon::parse($currentEndDate)->format('d M Y') . '.'
            );
        }

        $oldEndDate = $currentEndDate ?: now()->toDateString();
        $newEndDate = Carbon::parse($request->internship_extended_to)->toDateString();
        $salaryEffectiveFrom = Carbon::parse($oldEndDate)->addDay()->toDateString();

        DB::beginTransaction();

        try {
            if (Schema::hasTable('employee_internship_extensions')) {
                DB::table('employee_internship_extensions')->insert([
                    'employee_id' => $employee,
                    'old_end_date' => $oldEndDate,
                    'new_end_date' => $newEndDate,
                    'reason' => $request->reason,
                    'extended_by_user_id' => auth()->id(),
                    'extended_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $updateData = [
                'employee_stage' => 'internship',
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn($this->employeeTable, 'internship_extended_to')) {
                $updateData['internship_extended_to'] = $newEndDate;
            } else {
                $updateData['internship_end_date'] = $newEndDate;
            }

            if (Schema::hasColumn($this->employeeTable, 'internship_status')) {
                $updateData['internship_status'] = 'extended';
            }

            DB::table($this->employeeTable)->where('id', $employee)->update($updateData);

            if ($request->filled('actual_salary')) {
                $this->salaryHistoryService->syncSalary(
                    (int) $employee,
                    'internship',
                    $request->actual_salary,
                    $salaryEffectiveFrom,
                    $request->salary_change_reason ?: 'Internship stipend updated during extension',
                    auth()->id()
                );
            }

            app(\App\Services\HRMS\Notification\NotificationS::class)
                ->markEmployeeLifecycleNotificationsResolved((int) $employee, ['internship_ending_soon', 'internship_ending_reminder']);

            $this->logLifecycle(
                $employee,
                'internship extended',
                ['old_end_date' => $oldEndDate],
                ['new_end_date' => $newEndDate],
                $request->reason ?: 'Internship extended'
            );

            DB::commit();

            return redirect()
                ->route('hrms.employees.probation_internship')
                ->with('success', 'Internship extended successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function completeInternship(Request $request, $employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'next_stage' => ['required', Rule::in(['completed', 'probation'])],
            'actual_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_change_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $internshipEndDate = $employeeData->internship_extended_to ?: $employeeData->internship_end_date;

        $effectiveDate = $internshipEndDate
            ? Carbon::parse($internshipEndDate)->addDay()->toDateString()
            : now()->toDateString();

        DB::beginTransaction();

        try {
            $updateData = [
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn($this->employeeTable, 'internship_completed_at')) {
                $updateData['internship_completed_at'] = now();
            }

            if ($request->next_stage === 'completed') {
                if (Schema::hasColumn($this->employeeTable, 'internship_status')) {
                    $updateData['internship_status'] = 'completed';
                }
            }

            if ($request->next_stage === 'probation') {
                $probationMonths = (int) ($employeeData->probation_months ?: 3);
                $probationMonths = $probationMonths > 0 ? $probationMonths : 3;

                $updateData['employment_type'] = 'full_time';
                $updateData['employee_stage'] = 'probation';
                $updateData['joining_date'] = $effectiveDate;
                $updateData['probation_start_date'] = $effectiveDate;
                $updateData['probation_end_date'] = Carbon::parse($effectiveDate)->addMonthsNoOverflow($probationMonths)->subDay()->toDateString();
                $updateData['probation_status'] = 'active';

                if (Schema::hasColumn($this->employeeTable, 'internship_status')) {
                    $updateData['internship_status'] = 'converted_to_probation';
                }
            }

            DB::table($this->employeeTable)->where('id', $employee)->update($updateData);

            if ($request->next_stage === 'probation') {
                $this->lifecycleService->autoAllocateForStage(
                    (int) $employee,
                    'probation',
                    $effectiveDate,
                    auth()->id()
                );
            }

            if ($request->filled('actual_salary') && $request->next_stage === 'probation') {
                $this->salaryHistoryService->syncSalary(
                    (int) $employee,
                    'probation',
                    $request->actual_salary,
                    $effectiveDate,
                    $request->salary_change_reason ?: 'Internship converted to probation',
                    auth()->id()
                );
            }

            app(\App\Services\HRMS\Notification\NotificationS::class)
                ->markEmployeeLifecycleNotificationsResolved((int) $employee, ['internship_ending_soon', 'internship_ending_reminder']);

            $this->logLifecycle(
                $employee,
                'internship completed',
                $employeeData,
                $updateData,
                'Internship action applied effective from ' . $effectiveDate
            );

            DB::commit();

            return redirect()
                ->route('hrms.employees.probation_internship')
                ->with('success', 'Internship action completed. Effective from ' . Carbon::parse($effectiveDate)->format('d M Y') . '.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
    public function markExit(Request $request, $employee)
    {
        $actor = auth()->user();
        abort_if(! $actor, 401);

        $isSuperAdmin = method_exists($actor, 'isSuperAdmin') && $actor->isSuperAdmin();

        $request->validate([
            'exit_type' => ['required', Rule::in(['resignation', 'termination', 'retirement', 'contract_end', 'mutual_separation', 'layoff_redundancy', 'absconding', 'deceased', 'other', 'internship_completed', 'internship_exit'])],
            'resignation_date' => ['nullable', 'date'],
            'termination_date' => ['nullable', 'date'],
            'last_working_day' => ['nullable', 'date'],
            'notice_period_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'notice_waived' => ['nullable', 'boolean'],
            'immediate_exit' => ['nullable', 'boolean'],
            'buyout_recovery' => ['nullable', 'boolean'],
            'immediate_disable_login' => ['nullable', 'boolean'],
        ]);

        try {
            $this->exitProcessService->initiate(
                (int) $employee,
                [
                    'exit_type' => $request->exit_type,
                    'resignation_date' => $request->resignation_date,
                    'termination_date' => $request->termination_date,
                    'last_working_day' => $request->last_working_day,
                    'notice_period_days' => $request->notice_period_days,
                    'reason' => $request->reason,
                    'remarks' => $request->remarks,
                    'notice_waived' => (bool) $request->boolean('notice_waived'),
                    'immediate_exit' => (bool) $request->boolean('immediate_exit'),
                    'buyout_recovery' => (bool) $request->boolean('buyout_recovery'),
                    'actor_is_super_admin' => $isSuperAdmin,
                    'immediate_disable_login' => (bool) $request->boolean('immediate_disable_login'),
                ],
                (int) auth()->id()
            );

            return redirect()
                ->route('hrms.employees.exit')
                ->with('success', 'Employee exit process initiated successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function completeExit(Request $request, $employee)
    {
        $request->validate([
            'exit_process_id' => ['required', 'integer'],
            'waive_incomplete' => ['nullable', 'boolean'],
        ]);

        $this->exitProcessService->complete(
            (int) $request->exit_process_id,
            (int) auth()->id(),
            (bool) $request->boolean('waive_incomplete')
        );

        return back()->with('success', 'Exit completed successfully.');
    }

    public function cancelExit(Request $request, $employee)
    {
        $request->validate([
            'exit_process_id' => ['required', 'integer'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->exitProcessService->cancel(
            (int) $request->exit_process_id,
            (int) auth()->id(),
            $request->remarks
        );

        return back()->with('success', 'Exit process cancelled.');
    }

    public function updateClearanceDept(Request $request, $employee)
    {
        $request->validate([
            'exit_process_id' => ['required', 'integer'],
            'department_key' => ['required', 'string'],
            'status' => ['required', 'in:pending,approved,rejected'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'checklist' => ['nullable', 'array'],
        ]);

        $actor = auth()->user();
        abort_if(! $actor, 401);

        $dept = $request->department_key;

        // Check Permissions
        $isSuperAdmin = method_exists($actor, 'isSuperAdmin') && $actor->isSuperAdmin();
        $isHrAdmin = method_exists($actor, 'hasRole') && $actor->hasRole('hr_admin');

        $canApprove = false;
        if ($isSuperAdmin || $isHrAdmin) {
            $canApprove = true;
        } else {
            // Check dynamic reporting manager authorization
            if ($dept === 'manager') {
                $empRecord = DB::table('employees_new')->where('id', $employee)->first();
                if ($empRecord && $actor->employee && $empRecord->reporting_manager_employee_id == $actor->employee->id) {
                    $canApprove = true;
                }
            }

            // Check department-specific permissions
            if (!$canApprove) {
                $permissionMap = [
                    'hr' => 'employee_exit.clearance.hr',
                    'manager' => 'employee_exit.clearance.manager',
                    'it' => 'employee_exit.clearance.it',
                    'admin' => 'employee_exit.clearance.admin',
                    'finance' => 'employee_exit.clearance.finance',
                    'asset' => 'employee_exit.clearance.asset',
                    'security' => 'employee_exit.clearance.security',
                    'accounts' => 'employee_exit.clearance.accounts',
                ];

                if (isset($permissionMap[$dept]) && $actor->hasPermission($permissionMap[$dept])) {
                    $canApprove = true;
                }
            }

            // Fallback: check matching department by name
            if (!$canApprove && $actor->employee && !empty($actor->employee->department_id)) {
                $userDeptName = DB::table('departments')->where('id', $actor->employee->department_id)->value('name');
                if ($userDeptName) {
                    $userDeptNameLower = strtolower($userDeptName);
                    if ($dept === 'it' && (str_contains($userDeptNameLower, 'it') || str_contains($userDeptNameLower, 'infrastructure') || str_contains($userDeptNameLower, 'devops'))) {
                        $canApprove = true;
                    } elseif ($dept === 'finance' && (str_contains($userDeptNameLower, 'finance') || str_contains($userDeptNameLower, 'account'))) {
                        $canApprove = true;
                    } elseif ($dept === 'accounts' && (str_contains($userDeptNameLower, 'finance') || str_contains($userDeptNameLower, 'account'))) {
                        $canApprove = true;
                    }
                }
            }
        }

        abort_if(! $canApprove, 403, 'You do not have permission to approve/reject clearance for this department.');

        // Reformat checklist to standard array of items
        $checklistItems = null;
        if ($request->has('checklist')) {
            $checklistItems = [];
            foreach ($request->input('checklist') as $itemText => $completedVal) {
                $checklistItems[] = [
                    'item' => $itemText,
                    'completed' => (bool)$completedVal,
                ];
            }
        }

        $this->exitProcessService->updateDepartmentClearance(
            (int) $request->exit_process_id,
            $dept,
            $request->status,
            $request->remarks,
            $checklistItems,
            (int) auth()->id()
        );

        return back()->with('success', strtoupper($dept) . ' clearance status updated successfully.');
    }

    public function refreshExit(Request $request, $employee)
    {
        $request->validate([
            'exit_process_id' => ['required', 'integer'],
        ]);

        $this->exitProcessService->refreshStatus((int) $request->exit_process_id);

        return back()->with('success', 'Exit checklist refreshed.');
    }

    public function updateExitClearance(Request $request, $employee)
    {
        $request->validate([
            'exit_process_id' => ['required', 'integer'],
            'asset_status' => ['nullable', Rule::in(['pending', 'cleared', 'waived'])],
            'fnf_status' => ['nullable', Rule::in(['pending', 'processing', 'approved', 'paid', 'completed', 'waived'])],
            'document_status' => ['nullable', Rule::in(['pending', 'generated', 'sent', 'completed', 'waived'])],
            'handover_status' => ['nullable', Rule::in(['pending', 'cleared', 'completed', 'waived'])],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $actor = auth()->user();
        abort_if(! $actor, 401);

        $assetStatus = $request->input('asset_status');
        $fnfStatus = $request->input('fnf_status');
        $documentStatus = $request->input('document_status');
        $handoverStatus = $request->input('handover_status');

        $isSuperAdmin = method_exists($actor, 'isSuperAdmin') && $actor->isSuperAdmin();
        $canFnf = $isSuperAdmin || (method_exists($actor, 'hasPermission') && $actor->hasPermission('employee_exit.fnf_process'));
        $canAsset = $isSuperAdmin || (method_exists($actor, 'hasPermission') && $actor->hasPermission('employee_exit.asset_clearance'));
        $canDocument = $isSuperAdmin || (method_exists($actor, 'hasPermission') && $actor->hasPermission('employee_exit.document_generate'));
        $canUpdate = $isSuperAdmin || (method_exists($actor, 'hasPermission') && $actor->hasPermission('employee_exit.update'));

        abort_if(! $canUpdate, 403, 'You are not allowed to update exit clearance.');
        abort_if($assetStatus !== null && ! $canAsset, 403, 'You are not allowed to update asset clearance.');
        abort_if($documentStatus !== null && ! $canDocument, 403, 'You are not allowed to update document clearance.');
        abort_if($fnfStatus !== null && ! $canFnf, 403, 'You are not allowed to update FnF clearance.');

        $this->exitProcessService->updateClearance(
            (int) $request->exit_process_id,
            [
                'asset_status' => $assetStatus,
                'fnf_status' => $fnfStatus,
                'document_status' => $documentStatus,
                'handover_status' => $handoverStatus,
                'remarks' => $request->remarks,
            ],
            (int) auth()->id()
        );

        return back()->with('success', 'Exit clearance updated successfully.');
    }

    public function destroy(Request $request, $employee)
    {
        $actor = auth()->user();
        abort_if(! $actor, 401);

        $mode = strtolower((string) $request->input('delete_mode', ''));
        abort_if($mode !== 'permanent', 422, 'Invalid delete mode. Use Exit Process for separation.');

        try {
            $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
            abort_if(! $employeeData, 404);

            abort_if((int) ($employeeData->user_id ?? 0) === (int) $actor->id, 403, 'Self-delete is not allowed.');

            $isSuperAdmin = method_exists($actor, 'isSuperAdmin') && $actor->isSuperAdmin();
            abort_if(! $isSuperAdmin, 403, 'Only Super Admin can permanently delete employee.');

            $confirmation = (string) $request->input('confirm_text', '');
            abort_if(trim($confirmation) !== 'DELETE EMPLOYEE', 422, 'Typed confirmation mismatch.');

            $impact = $this->permanentDeleteService->impactReport((int) $employee);
            if ($request->boolean('impact_only')) {
                return back()->with('success', 'Impact report generated.')->with('delete_impact_report', $impact);
            }

            $result = $this->permanentDeleteService->permanentDelete(
                (int) $employee,
                (int) $actor->id,
                (bool) $request->boolean('force_locked_records')
            );

            return redirect()->route('hrms.employees.index')
                ->with('success', 'Employee permanently deleted successfully.')
                ->with('delete_result', $result);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function salaryEffectiveDate(array $lifecyclePayload): string
    {
        return $lifecyclePayload['joining_date']
            ?: ($lifecyclePayload['internship_start_date'] ?: now()->toDateString());
    }

    private function salaryHistoryReason(array $lifecyclePayload, ?string $submittedReason, string $defaultReason): string
    {
        $reason = trim((string) $submittedReason);

        if ($reason !== '') {
            return $reason;
        }

        if (($lifecyclePayload['employee_stage'] ?? null) === 'internship') {
            return (int) ($lifecyclePayload['is_paid_intern'] ?? 0) === 0
                ? 'Unpaid internship'
                : 'Internship stipend';
        }

        return $defaultReason;
    }

    private function findEmployeeProfileRecord($employee)
    {
        $phoneSelect = Schema::hasColumn('users', 'phone')
            ? 'users.phone'
            : DB::raw('NULL as phone');

        $roleNameExpression = Schema::hasColumn('roles', 'name')
            ? 'roles.name'
            : (Schema::hasColumn('roles', 'title') ? 'roles.title' : DB::raw("CONCAT('Role ', roles.id)"));

        return DB::table($this->employeeTable)
            ->join('users', 'users.id', '=', $this->employeeTable . '.user_id')
            ->leftJoin('departments', 'departments.id', '=', $this->employeeTable . '.department_id')
            ->leftJoin('designations', 'designations.id', '=', $this->employeeTable . '.designation_id')
            ->leftJoin('roles', 'roles.id', '=', $this->employeeTable . '.system_role_id')
            ->leftJoin($this->employeeTable . ' as manager', 'manager.id', '=', $this->employeeTable . '.reporting_manager_employee_id')
            ->leftJoin('users as manager_user', 'manager_user.id', '=', 'manager.user_id')
            ->leftJoin($this->profileTable, $this->profileTable . '.employee_id', '=', $this->employeeTable . '.id')
            ->where($this->employeeTable . '.id', $employee)
            ->select(
                $this->employeeTable . '.*',
                $this->employeeTable . '.id as employee_id',
                'users.name',
                'users.email',
                $phoneSelect,
                'departments.name as department_name',
                'designations.name as designation_name',
                DB::raw($roleNameExpression . ' as role_name'),
                'manager_user.name as manager_name',
                'manager.employee_code as manager_code',
                $this->profileTable . '.date_of_birth',
                $this->profileTable . '.gender',
                $this->profileTable . '.address',
                $this->profileTable . '.highest_qualification',
                $this->profileTable . '.cgpa_percentage',
                $this->profileTable . '.total_experience',
                $this->profileTable . '.experience_type',
                $this->profileTable . '.emergency_contact_number',
                $this->profileTable . '.bank_account_no',
                $this->profileTable . '.bank_account_type',
                $this->profileTable . '.bank_holder_name',
                $this->profileTable . '.ifsc_code',
                $this->profileTable . '.bank_branch',
                $this->profileTable . '.profile_image',
                $this->profileTable . '.resume_file',
                $this->profileTable . '.profile_status',
                $this->profileTable . '.is_profile_completed',
                $this->profileTable . '.profile_completed_at',
                $this->profileTable . '.approved_by_user_id',
                $this->profileTable . '.approved_at',
                $this->profileTable . '.rejection_reason'
            )
            ->first();
    }

    private function logLifecycle($employeeId, string $action, $oldValue = null, $newValue = null, ?string $remarks = null): void
    {
        if (! Schema::hasTable('employee_lifecycle_logs')) {
            return;
        }

        DB::table('employee_lifecycle_logs')->insert([
            'employee_id' => $employeeId,
            'action' => $action,
            'old_value' => $oldValue ? json_encode($oldValue) : null,
            'new_value' => $newValue ? json_encode($newValue) : null,
            'remarks' => $remarks,
            'performed_by_user_id' => auth()->id(),
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getDesignationsByDepartment($department)
    {
        $query = DB::table('designations')
            ->where('department_id', $department)
            ->select('id', 'name');

        if (Schema::hasColumn('designations', 'is_active')) {
            $query->where('is_active', 1);
        }

        return response()->json($query->orderBy('name')->get());
    }




    public function inlineUpdateProfile(Request $request, $employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],

            'highest_qualification' => ['nullable', 'string', 'max:255'],
            'cgpa_percentage' => ['nullable', 'string', 'max:50'],
            'experience_type' => ['nullable', 'string', 'in:fresher,experienced'],
            'total_experience' => ['nullable', 'string', 'max:50'],

            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'bank_account_type' => ['nullable', 'string', 'max:50'],
            'bank_holder_name' => ['nullable', 'string', 'max:255'],
            'ifsc_code' => ['nullable', 'string', 'max:30'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();

        try {
            if (Schema::hasColumn('users', 'phone')) {
                DB::table('users')->where('id', $employeeData->user_id)->update([
                    'phone' => $request->phone,
                    'updated_at' => now(),
                ]);
            }

            DB::table($this->profileTable)->updateOrInsert(
                ['employee_id' => $employee],
                [
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'address' => $request->address,
                    'highest_qualification' => $request->highest_qualification,
                    'cgpa_percentage' => $request->cgpa_percentage,
                    'experience_type' => $request->experience_type,
                    'total_experience' => $request->experience_type === 'fresher' ? '0' : $request->total_experience,
                    'bank_account_no' => $request->bank_account_no,
                    'bank_account_type' => $request->bank_account_type,
                    'bank_holder_name' => $request->bank_holder_name,
                    'ifsc_code' => $request->ifsc_code ? strtoupper($request->ifsc_code) : null,
                    'bank_branch' => $request->bank_branch,
                    'updated_at' => now(),
                ]
            );

            DB::commit();

            return redirect()
                ->route('hrms.employees.profile.view', $employee)
                ->with('success', 'Profile details updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }






    public function verifyDocument($document)
    {
        $doc = DB::table('employee_documents_new')->where('id', $document)->first();
        abort_if(! $doc, 404);

        DB::table('employee_documents_new')
            ->where('id', $document)
            ->update([
                'verification_status' => 'verified',
                'verified_by_user_id' => auth()->id(),
                'verified_at' => now(),
                'rejection_reason' => null,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Document verified successfully.');
    }

    public function rejectDocument(Request $request, $document)
    {
        $doc = DB::table('employee_documents_new')->where('id', $document)->first();
        abort_if(! $doc, 404);

        $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::table('employee_documents_new')
            ->where('id', $document)
            ->update([
                'verification_status' => 'rejected',
                'verified_by_user_id' => null,
                'verified_at' => null,
                'rejection_reason' => $request->rejection_reason ?: 'Document rejected by HR',
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Document rejected successfully.');
    }

    private function getOrCreatePolicyForRule(string $policyName, string $shiftCode): ?int
    {
        $policy = DB::table('attendance_policy_rules')->where('policy_name', $policyName)->first();
        if ($policy) {
            // Ensure timings are correct/synchronized with shift if they are mismatched
            $shift = DB::table('attendance_times')->where('code', $shiftCode)->first();
            if ($shift) {
                $updates = [];
                foreach (['shift_start_time', 'shift_end_time', 'required_work_minutes', 'half_day_min_minutes', 'absent_below_minutes', 'lunch_break_minutes'] as $field) {
                    if (isset($shift->{$field}) && (!isset($policy->{$field}) || $policy->{$field} != $shift->{$field})) {
                        $updates[$field] = $shift->{$field};
                    }
                }
                if (!empty($updates)) {
                    DB::table('attendance_policy_rules')->where('id', $policy->id)->update($updates);
                }
            }
            return $policy->id;
        }

        // Try with "Attendance Policy" suffix if not found and not already suffix
        if (!str_contains($policyName, 'Attendance Policy')) {
            $altName = str_replace(' Policy', ' Attendance Policy', $policyName);
            $policy = DB::table('attendance_policy_rules')->where('policy_name', $altName)->first();
            if ($policy) {
                return $policy->id;
            }
        }

        // If still not found, check the shift and create a corresponding policy rule dynamically
        $shift = DB::table('attendance_times')->where('code', $shiftCode)->first();
        if ($shift) {
            $policyData = [
                'policy_name' => $policyName,
                'punch_allowed_from' => $shift->punch_allowed_from ?? '09:00:00',
                'shift_start_time' => $shift->shift_start_time ?? '10:00:00',
                'late_after_time' => $shift->late_after_time ?? '11:05:00',
                'warning_after_time' => $shift->warning_after_time ?? '11:06:00',
                'block_after_time' => $shift->block_after_time ?? '11:15:00',
                'shift_end_time' => $shift->shift_end_time ?? '19:00:00',
                'required_work_minutes' => $shift->required_work_minutes ?? 480,
                'half_day_min_minutes' => $shift->half_day_min_minutes ?? 270,
                'absent_below_minutes' => $shift->absent_below_minutes ?? 270,
                'lunch_break_minutes' => $shift->lunch_break_minutes ?? 60,
                'allowed_missed_punches' => 2,
                'combined_violation_limit' => 3,
                'late_violation_limit' => 3,
                'early_violation_limit' => 3,
                'auto_block_enabled' => 1,
                'auto_absent_enabled' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            // Filter fields to match columns in database
            $policyData = collect($policyData)->filter(fn ($value, $column) => Schema::hasColumn('attendance_policy_rules', $column))->all();
            return DB::table('attendance_policy_rules')->insertGetId($policyData);
        }

        return null;
    }

    private function resolveShiftAndPolicyBySchedule(?string $schedule, string $workMode, string $employmentType): array
    {
        $shiftCode = 'general_shift';
        $policyName = 'Default Attendance Policy';

        $config = config('hrms.work_schedule_shifts');

        // Normalize legacy schedule keys to the new format
        $scheduleKey = $schedule;
        if ($scheduleKey === 'full_day') {
            $scheduleKey = 'general';
        } elseif ($scheduleKey === 'part_day') {
            $scheduleKey = 'part_time';
        } elseif ($scheduleKey === 'hourly') {
            $scheduleKey = 'half_day';
        } elseif ($scheduleKey === 'shift_based' || $scheduleKey === 'shift_based_morning') {
            $scheduleKey = 'half_day_morning';
        } elseif ($scheduleKey === 'shift_based_evening') {
            $scheduleKey = 'half_day_evening';
        }

        // Determine schedule key based on input or defaults
        if (empty($scheduleKey)) {
            if ($workMode === 'wfh') {
                $scheduleKey = 'wfh';
            } elseif ($employmentType === 'part_time') {
                $scheduleKey = 'part_time';
            } else {
                $scheduleKey = 'general';
            }
        }

        if ($config && isset($config[$scheduleKey])) {
            $shiftCode = $config[$scheduleKey]['shift_code'];
            $policyName = $config[$scheduleKey]['policy_name'];
        } else {
            // Fallback mappings if config is not available
            if ($workMode === 'wfh' || $scheduleKey === 'wfh') {
                $shiftCode = 'wfh_shift';
                $policyName = 'WFH Attendance Policy';
            } elseif ($scheduleKey === 'part_time') {
                $shiftCode = 'part_time_shift';
                $policyName = 'Part Time Attendance Policy';
            } elseif ($scheduleKey === 'half_day') {
                $shiftCode = 'half_day_shift';
                $policyName = 'Half Day Attendance Policy';
            } elseif ($scheduleKey === 'half_day_morning') {
                $shiftCode = 'half_day_morning';
                $policyName = 'Half Day Morning Policy';
            } elseif ($scheduleKey === 'half_day_evening') {
                $shiftCode = 'half_day_evening';
                $policyName = 'Half Day Evening Policy';
            }
        }

        $shift = DB::table('attendance_times')->where('code', $shiftCode)->first();
        if (!$shift) {
            $shift = DB::table('attendance_times')->where('name', 'like', '%' . str_replace('_', ' ', $shiftCode) . '%')->first();
        }

        $policyId = null;
        if ($shift) {
            $policyId = $this->getOrCreatePolicyForRule($policyName, $shift->code);
        }

        return [$shift, $policyId];
    }

    private function mapScheduleTypeForDb(?string $schedule): ?string
    {
        if ($schedule === 'shift_based_morning' || $schedule === 'shift_based_evening' || $schedule === 'half_day_morning' || $schedule === 'half_day_evening') {
            return 'shift_based';
        }
        if ($schedule === 'part_time') {
            return 'part_day';
        }
        if ($schedule === 'half_day') {
            return 'hourly';
        }
        if ($schedule === 'general') {
            return 'full_day';
        }
        return $schedule;
    }

    private function adjustWorkScheduleTypeForView($employeeData): void
    {
        if (!$employeeData) {
            return;
        }

        $activePolicy = DB::table('employee_policy_assignments')
            ->join('attendance_policy_rules', 'attendance_policy_rules.id', '=', 'employee_policy_assignments.policy_id')
            ->where('employee_policy_assignments.employee_id', $employeeData->id)
            ->where('employee_policy_assignments.policy_type', 'attendance')
            ->where('employee_policy_assignments.is_active', 1)
            ->orderByDesc('employee_policy_assignments.effective_from')
            ->orderByDesc('employee_policy_assignments.id')
            ->first();

        if ($activePolicy) {
            $policyName = $activePolicy->policy_name;
            if (str_contains($policyName, 'Morning')) {
                $employeeData->work_schedule_type = 'half_day_morning';
            } elseif (str_contains($policyName, 'Evening')) {
                $employeeData->work_schedule_type = 'half_day_evening';
            } elseif (str_contains($policyName, 'Part Time')) {
                $employeeData->work_schedule_type = 'part_time';
            } elseif (str_contains($policyName, 'Half Day')) {
                $employeeData->work_schedule_type = 'half_day';
            } elseif (str_contains($policyName, 'WFH')) {
                $employeeData->work_schedule_type = 'wfh';
            } elseif (str_contains($policyName, 'Default') || str_contains($policyName, 'General')) {
                $employeeData->work_schedule_type = 'general';
            }
        } else {
            // Fallback from db columns if no assignment exists
            if ($employeeData->work_schedule_type === 'full_day') {
                $employeeData->work_schedule_type = 'general';
            } elseif ($employeeData->work_schedule_type === 'part_day') {
                $employeeData->work_schedule_type = 'part_time';
            } elseif ($employeeData->work_schedule_type === 'hourly') {
                $employeeData->work_schedule_type = 'half_day';
            } elseif ($employeeData->work_schedule_type === 'shift_based') {
                $employeeData->work_schedule_type = 'half_day_morning';
            }
        }
    }
}
