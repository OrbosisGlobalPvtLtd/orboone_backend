<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Mail\EmployeeCredentialMail;
use App\Services\HRMS\Employee\EmployeeFileS;
use App\Services\HRMS\Employee\EmployeeLifecycleService;
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

    public function __construct(
        EmployeeS $employeeService,
        EmployeeLifecycleService $lifecycleService,
        EmployeeSalaryHistoryService $salaryHistoryService
    ) {
        $this->employeeService = $employeeService;
        $this->lifecycleService = $lifecycleService;
        $this->salaryHistoryService = $salaryHistoryService;
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

        $baseQuery = DB::table($employeeTable)
            ->join('users', 'users.id', '=', $employeeTable . '.user_id')
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
            DB::raw($hasAttendanceTime ? 'attendance_times.name as shift_name' : "'General Shift' as shift_name"),
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
                    4  => $employeeTable . '.work_mode',
                    5  => 'manager_user.name',
                    6  => $hasAttendanceTime ? 'attendance_times.name' : $employeeTable . '.id',
                    7  => $profileTable . '.profile_status',
                    8  => $employeeTable . '.employee_stage',
                    9  => $employeeTable . '.joining_date',
                    10 => $employeeTable . '.employment_status',
                    11 => $employeeTable . '.id',
                ];

                $orderColumnIndex = (int) $request->input('order.0.column', 11);
                $orderDirection = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
                $orderColumn = $columns[$orderColumnIndex] ?? $employeeTable . '.id';

                $employees = $query
                    ->orderBy($orderColumn, $orderDirection)
                    ->offset((int) $request->input('start', 0))
                    ->limit((int) $request->input('length', 10))
                    ->get();

                $data = $employees->map(function ($employee) {
                    $name = $employee->name ?: '-';
                    $initial = strtoupper(substr($name, 0, 1));
                    $employeeCode = $employee->employee_code ?: 'EMP-' . $employee->id;

                    $avatar = '<div class="eo-avatar">' . e($initial) . '</div>';

                    if (! empty($employee->profile_image)) {
                        $imageUrl = Route::has('hrms.documents.file')
                            ? route('hrms.documents.file', $employee->profile_image)
                            : asset('storage/' . $employee->profile_image);

                        $avatar = '<div class="eo-avatar"><img src="' . e($imageUrl) . '" alt="' . e($name) . '"></div>';
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

                    if (Route::has('hrms.employees.destroy')) {
                        $actions .= '
                        <form action="' . route('hrms.employees.destroy', $employee->id) . '" method="POST" style="margin:0;" onsubmit="return confirm(\'Are you sure you want to delete this employee?\')">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-trash"></i> Delete
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
            'work_schedule_type' => ['nullable', Rule::in(['full_day', 'part_day', 'hourly', 'shift_based'])],
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

            if (Schema::hasTable('employee_password_setup_tokens') && Route::has('employee.password.setup')) {
                $plainToken = Str::random(64);

                DB::table('employee_password_setup_tokens')->insert([
                    'user_id' => $userId,
                    'token_hash' => hash('sha256', $plainToken),
                    'expires_at' => now()->addHours(48),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $passwordSetupUrl = route('employee.password.setup', ['token' => $plainToken]);
            }

            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $userId, 'role_id' => $request->system_role_id],
                ['created_at' => now(), 'updated_at' => now()]
            );

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
                'work_schedule_type' => $lifecyclePayload['work_schedule_type'],
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

            $this->lifecycleService->autoAllocateLeaveAfterProbationIfEligible(
                (int) $employeeId,
                $lifecyclePayload['probation_end_date']
            );

            DB::commit();

            try {
                Mail::to($request->email)->send(new EmployeeCredentialMail(
                    $request->name,
                    $request->email,
                    $employeeCode,
                    $plainPassword,
                    $passwordSetupUrl
                ));
            } catch (\Exception $mailEx) {
                FacadesLog::error('Mail failed: ' . $mailEx->getMessage());
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
        $phoneSelect = Schema::hasColumn('users', 'phone')
            ? 'users.phone'
            : DB::raw('NULL as phone');

        $roleNameExpression = Schema::hasColumn('roles', 'name')
            ? 'roles.name'
            : (Schema::hasColumn('roles', 'title') ? 'roles.title' : DB::raw("CONCAT('Role ', roles.id)"));

        $employeeData = DB::table($this->employeeTable)
            ->join('users', 'users.id', '=', $this->employeeTable . '.user_id')
            ->leftJoin('departments', 'departments.id', '=', $this->employeeTable . '.department_id')
            ->leftJoin('designations', 'designations.id', '=', $this->employeeTable . '.designation_id')
            ->leftJoin('roles', 'roles.id', '=', $this->employeeTable . '.system_role_id')
            ->leftJoin($this->profileTable, $this->profileTable . '.employee_id', '=', $this->employeeTable . '.id')
            ->where($this->employeeTable . '.id', $employee)
            ->select(
                $this->employeeTable . '.*',
                'users.name',
                'users.email',
                $phoneSelect,
                'departments.name as department_name',
                'designations.name as designation_name',
                DB::raw($roleNameExpression . ' as role_name'),
                $this->profileTable . '.date_of_birth',
                $this->profileTable . '.gender',
                $this->profileTable . '.address',
                $this->profileTable . '.highest_qualification',
                $this->profileTable . '.cgpa_percentage',
                $this->profileTable . '.total_experience',
                $this->profileTable . '.bank_account_no',
                $this->profileTable . '.bank_account_type',
                $this->profileTable . '.bank_holder_name',
                $this->profileTable . '.ifsc_code',
                $this->profileTable . '.bank_branch',
                $this->profileTable . '.profile_image',
                $this->profileTable . '.resume_file',
                $this->profileTable . '.profile_status',
                $this->profileTable . '.is_profile_completed',
                $this->profileTable . '.approved_by_user_id',
                $this->profileTable . '.approved_at',
                $this->profileTable . '.rejection_reason'
            )
            ->first();

        abort_if(! $employeeData, 404);

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
        $salaryHistories = DB::table('employee_salary_histories')
            ->where('employee_id', $employee)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();
        return view('hrms.employee.manage', compact(
            'employeeData',
            'departments',
            'designations',
            'roles',
            'salaryHistories'
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
            'work_schedule_type' => ['nullable', Rule::in(['full_day', 'part_day', 'hourly', 'shift_based'])],
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

            DB::table('users')->where('id', $employeeData->user_id)->update($userUpdateData);

            DB::table('user_roles')->where('user_id', $employeeData->user_id)->delete();

            DB::table('user_roles')->insert([
                'user_id' => $employeeData->user_id,
                'role_id' => $request->system_role_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $employeeUpdateData = [
                'system_role_id' => $request->system_role_id,
                'department_id' => $request->department_id,
                'designation_id' => $request->designation_id,
                'employment_type' => $request->employment_type,
                'employee_stage' => $lifecyclePayload['employee_stage'],
                'work_mode' => $request->work_mode,
                'work_schedule_type' => $lifecyclePayload['work_schedule_type'],
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

        $salaryHistories = DB::table('employee_salary_histories')
            ->where('employee_id', $employee)
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->get();

        return view('hrms.employee.show', compact('employeeData', 'salaryHistories'));
    }

    public function edit($employee)
    {
        $employeeData = $this->findEmployeeProfileRecord($employee);

        abort_if(! $employeeData, 404);

        $formData = $this->employeeService->createFormData();

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
            'total_experience' => ['required', 'string'],
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
                'total_experience' => $request->total_experience,
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
                $employeeName = DB::table('users')->where('id', $employeeData->user_id)->value('name') ?: $employeeData->employee_code;

                app(\App\Services\HRMS\Notification\NotificationS::class)->notifyHrAndSuperAdmin(
                    'Profile Verification Request',
                    $employeeName . ' submitted profile for verification.',
                    'profile_submitted',
                    'hrms.employees.profile.view',
                    ['employee' => $id],
                    [
                        'employee_id' => $id,
                        'user_id' => $employeeData->user_id,
                        'employee_code' => $employeeData->employee_code,
                        'redirect_type' => 'profile_view'
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
                'employee_exit_processes.exit_type',
                'employee_exit_processes.final_status',
                'employee_exit_processes.asset_handover_status',
                'employee_exit_processes.fnf_status'
            )
            ->where(function ($q) {
                $q->whereIn($this->employeeTable . '.employment_status', ['resigned', 'terminated', 'inactive'])
                    ->orWhereNotNull($this->employeeTable . '.relieving_date')
                    ->orWhere($this->employeeTable . '.is_active', 0);
            })
            ->orderByDesc($this->employeeTable . '.id')
            ->get();

        return view('hrms.employee.exit.index', compact('employees'));
    }

    public function reportingStructure()
    {
        $teamCounts = DB::table($this->employeeTable)
            ->select('reporting_manager_employee_id', DB::raw('COUNT(*) as team_members_count'))
            ->whereNotNull('reporting_manager_employee_id')
            ->groupBy('reporting_manager_employee_id');

        $employees = DB::table($this->employeeTable . ' as e')
            ->join('users as u', 'u.id', '=', 'e.user_id')
            ->leftJoin('departments as d', 'd.id', '=', 'e.department_id')
            ->leftJoin('designations as dg', 'dg.id', '=', 'e.designation_id')
            ->leftJoin($this->employeeTable . ' as rm', 'rm.id', '=', 'e.reporting_manager_employee_id')
            ->leftJoin('users as ru', 'ru.id', '=', 'rm.user_id')
            ->leftJoinSub($teamCounts, 'team_counts', function ($join) {
                $join->on('team_counts.reporting_manager_employee_id', '=', 'e.id');
            })
            ->select(
                'e.id',
                'e.employee_code',
                'u.name as employee_name',
                'd.name as department_name',
                'dg.name as designation_name',
                'ru.name as manager_name',
                'rm.employee_code as manager_code',
                DB::raw('COALESCE(team_counts.team_members_count, 0) as team_members_count')
            )
            ->orderBy('u.name')
            ->get();

        return view('hrms.employee.reporting.index', compact('employees'));
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
            ? Carbon::parse($employeeData->probation_end_date)
            : now();

        $permanentEffectiveDate = $probationEnd->copy()->addDay()->toDateString();

        DB::beginTransaction();

        try {
            $updateData = [
                'probation_status' => 'completed',
                'employee_stage' => 'permanent',
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn($this->employeeTable, 'is_permanent')) {
                $updateData['is_permanent'] = 1;
            }

            if (Schema::hasColumn($this->employeeTable, 'permanent_at')) {
                $updateData['permanent_at'] = $permanentEffectiveDate;
            }

            DB::table($this->employeeTable)->where('id', $employee)->update($updateData);

            if ($request->filled('actual_salary')) {
                $this->salaryHistoryService->syncSalary(
                    (int) $employee,
                    'permanent',
                    $request->actual_salary,
                    $permanentEffectiveDate,
                    $request->salary_change_reason ?: 'Permanent salary update',
                    auth()->id()
                );
            }

            app(\App\Services\HRMS\Notification\NotificationS::class)
                ->markEmployeeLifecycleNotificationsResolved((int) $employee, ['probation_ending_soon']);

            $this->logLifecycle(
                $employee,
                'marked permanent',
                $employeeData,
                $updateData,
                'Employee marked permanent effective from ' . $permanentEffectiveDate
            );

            DB::commit();

            return redirect()
                ->route('hrms.employees.probation_internship')
                ->with('success', 'Employee marked permanent. Effective from ' . Carbon::parse($permanentEffectiveDate)->format('d M Y') . '.');
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
                ->markEmployeeLifecycleNotificationsResolved((int) $employee, ['internship_ending_soon']);

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
                $updateData['probation_end_date'] = Carbon::parse($effectiveDate)->addMonths($probationMonths)->toDateString();
                $updateData['probation_status'] = 'active';

                if (Schema::hasColumn($this->employeeTable, 'internship_status')) {
                    $updateData['internship_status'] = 'converted_to_probation';
                }
            }

            DB::table($this->employeeTable)->where('id', $employee)->update($updateData);

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
                ->markEmployeeLifecycleNotificationsResolved((int) $employee, ['internship_ending_soon']);

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
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'employment_status' => ['required', Rule::in(['resigned', 'terminated', 'inactive'])],
            'relieving_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::beginTransaction();

        try {
            $relievingDate = $request->relieving_date ?: now()->toDateString();

            $employeeUpdateData = [
                'employment_status' => $request->employment_status,
                'relieving_date' => $relievingDate,
                'is_active' => 0,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ];

            if (($employeeData->employee_stage ?? null) === 'internship' && Schema::hasColumn($this->employeeTable, 'internship_status')) {
                $employeeUpdateData['internship_status'] = 'exited';
            }

            DB::table($this->employeeTable)->where('id', $employee)->update($employeeUpdateData);

            if (! empty($employeeData->user_id) && Schema::hasColumn('users', 'is_active')) {
                DB::table('users')->where('id', $employeeData->user_id)->update([
                    'is_active' => 0,
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasTable('employee_exit_processes')) {
                $exitType = match ($request->employment_status) {
                    'terminated' => 'termination',
                    default => (($employeeData->employee_stage ?? null) === 'internship' ? 'internship_completed_exit' : 'resignation'),
                };

                DB::table('employee_exit_processes')->updateOrInsert(
                    [
                        'employee_id' => $employee,
                        'final_status' => 'pending',
                    ],
                    [
                        'exit_type' => $exitType,
                        'resignation_date' => now()->toDateString(),
                        'last_working_date' => $relievingDate,
                        'reason' => $request->reason,
                        'asset_handover_status' => 'pending',
                        'fnf_status' => 'pending',
                        'experience_letter_status' => 'pending',
                        'relieving_letter_status' => 'pending',
                        'initiated_by_user_id' => auth()->id(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            if (Schema::hasTable('asset_allocations') && Schema::hasColumn('asset_allocations', 'handover_status')) {
                DB::table('asset_allocations')
                    ->where('employee_id', $employee)
                    ->whereIn('handover_status', ['allocated'])
                    ->update([
                        'handover_status' => 'pending_return',
                        'updated_at' => now(),
                    ]);
            }

            $this->logLifecycle(
                $employee,
                'resignation initiated',
                [
                    'employment_status' => $employeeData->employment_status,
                    'is_active' => $employeeData->is_active ?? null,
                ],
                $employeeUpdateData,
                $request->reason
            );

            DB::commit();

            return redirect()
                ->route('hrms.employees.exit')
                ->with('success', 'Employee exit status updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($employee)
    {
        DB::beginTransaction();

        try {
            $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
            abort_if(! $employeeData, 404);

            DB::table($this->profileTable)->where('employee_id', $employee)->delete();
            DB::table('user_roles')->where('user_id', $employeeData->user_id)->delete();
            DB::table($this->employeeTable)->where('id', $employee)->delete();
            DB::table('users')->where('id', $employeeData->user_id)->delete();

            DB::commit();

            return redirect()
                ->route('hrms.employees.index')
                ->with('success', 'Employee deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

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
                    'total_experience' => $request->total_experience,
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
}
