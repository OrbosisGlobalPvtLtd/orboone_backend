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
        $userPhoneSelect = Schema::hasColumn('users', 'phone')
            ? 'users.phone'
            : DB::raw('NULL as phone');

        $baseQuery = DB::table($this->employeeTable)
            ->join('users', 'users.id', '=', $this->employeeTable.'.user_id')
            ->leftJoin('departments', 'departments.id', '=', $this->employeeTable.'.department_id')
            ->leftJoin('designations', 'designations.id', '=', $this->employeeTable.'.designation_id')
            ->leftJoin($this->profileTable, $this->profileTable.'.employee_id', '=', $this->employeeTable.'.id')
            ->select(
                $this->employeeTable.'.*',
                'users.name',
                'users.email',
                $userPhoneSelect,
                'departments.name as department_name',
                'designations.name as designation_name',
                $this->profileTable.'.profile_image'
            );

        $baseQuery->where($this->employeeTable.'.employment_status', 'active');

        if (Schema::hasColumn($this->employeeTable, 'is_active')) {
            $baseQuery->where($this->employeeTable.'.is_active', 1);
        }

        if ($request->has('ajax_table')) {
            try {
                $query = clone $baseQuery;

                if ($request->filled('department')) {
                    $query->where($this->employeeTable.'.department_id', $request->department);
                }

                if ($request->filled('status')) {
                    $query->where($this->employeeTable.'.employment_status', $request->status);
                }

                if ($request->filled('work_mode')) {
                    $query->where($this->employeeTable.'.work_mode', $request->work_mode);
                }

                $recordsTotalQuery = DB::table($this->employeeTable)
                    ->where($this->employeeTable.'.employment_status', 'active');

                if (Schema::hasColumn($this->employeeTable, 'is_active')) {
                    $recordsTotalQuery->where($this->employeeTable.'.is_active', 1);
                }

                $recordsTotal = $recordsTotalQuery->count();

                $searchValue = $request->input('search.value');

                if (! empty($searchValue)) {
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('users.name', 'like', "%{$searchValue}%")
                            ->orWhere('users.email', 'like', "%{$searchValue}%")
                            ->orWhere('employees_new.employee_code', 'like', "%{$searchValue}%")
                            ->orWhere('departments.name', 'like', "%{$searchValue}%")
                            ->orWhere('designations.name', 'like', "%{$searchValue}%")
                            ->orWhere('employees_new.employment_type', 'like', "%{$searchValue}%")
                            ->orWhere('employees_new.work_mode', 'like', "%{$searchValue}%")
                            ->orWhere('employees_new.employment_status', 'like', "%{$searchValue}%");

                        if (Schema::hasColumn('users', 'phone')) {
                            $q->orWhere('users.phone', 'like', "%{$searchValue}%");
                        }
                    });
                }

                $recordsFiltered = $query->count();

                $columns = [
                    0 => 'users.name',
                    1 => 'employees_new.employee_code',
                    2 => 'users.email',
                    3 => 'departments.name',
                    4 => 'designations.name',
                    5 => 'employees_new.employment_type',
                    6 => 'employees_new.work_mode',
                    7 => 'employees_new.employment_status',
                    8 => 'employees_new.id',
                ];

                $orderColumnIndex = (int) $request->input('order.0.column', 8);
                $orderDirection = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
                $orderColumn = $columns[$orderColumnIndex] ?? 'employees_new.id';

                $employees = $query
                    ->orderBy($orderColumn, $orderDirection)
                    ->offset((int) $request->input('start', 0))
                    ->limit((int) $request->input('length', 10))
                    ->get();

                $data = $employees->map(function ($employee) {
                    $name = $employee->name ?? '-';
                    $initial = strtoupper(substr($name, 0, 1));
                    $avatar = '<div class="eo-avatar">'.e($initial).'</div>';

                    if (! empty($employee->profile_image)) {
                        $imageUrl = Route::has('hrms.documents.file')
                            ? route('hrms.documents.file', $employee->profile_image)
                            : asset('storage/'.$employee->profile_image);

                        $avatar = '<div class="eo-avatar"><img src="'.e($imageUrl).'" alt="'.e($name).'"></div>';
                    }

                    $status = strtolower($employee->employment_status ?? 'active');
                    $statusClass = match ($status) {
                        'active' => 'eo-pill-active',
                        'resigned' => 'eo-pill-resigned',
                        'terminated' => 'eo-pill-terminated',
                        default => 'eo-pill-default',
                    };

                    $workMode = strtolower($employee->work_mode ?? 'wfo');
                    $workModeClass = match ($workMode) {
                        'wfh' => 'eo-pill-wfh',
                        'hybrid' => 'eo-pill-hybrid',
                        default => 'eo-pill-wfo',
                    };

                    $actions = '<div class="eo-actions-cell">';

                    if (Route::has('hrms.employees.manage')) {
                        $actions .= '
                            <a href="'.route('hrms.employees.manage', $employee->id).'" class="eo-icon-btn eo-icon-profile" title="Manage Employee">
                                <i class="fas fa-user-cog"></i>
                            </a>
                        ';
                    }

                    if (Route::has('hrms.employees.show')) {
                        $actions .= '
                            <a href="'.route('hrms.employees.show', $employee->id).'" class="eo-icon-btn eo-icon-view" title="View Employee">
                                <i class="fas fa-eye"></i>
                            </a>
                        ';
                    }

                    if (Route::has('hrms.employees.edit')) {
                        $actions .= '
                            <a href="'.route('hrms.employees.edit', $employee->id).'" class="eo-icon-btn eo-icon-edit" title="Edit Employee">
                                <i class="fas fa-edit"></i>
                            </a>
                        ';
                    }

                    if (Route::has('hrms.employees.destroy')) {
                        $actions .= '
                            <form action="'.route('hrms.employees.destroy', $employee->id).'" method="POST" style="display:inline-block;margin:0;" onsubmit="return confirm(\'Are you sure you want to delete this employee?\')">
                                '.csrf_field().'
                                '.method_field('DELETE').'
                                <button type="submit" class="eo-icon-btn eo-icon-delete" title="Delete Employee">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        ';
                    }

                    $actions .= '</div>';

                    return [
                        'employee' => '
                            <div class="eo-emp">
                                '.$avatar.'
                                <div>
                                    <div class="eo-name">'.e($name).'</div>
                                    <div class="eo-meta">Joined: '.(! empty($employee->joining_date) ? Carbon::parse($employee->joining_date)->format('d M Y') : 'Not added').'</div>
                                </div>
                            </div>
                        ',
                        'employee_code' => '<span class="eo-code">'.e($employee->employee_code ?? 'EMP-'.$employee->id).'</span>',
                        'contact' => '<div>'.e($employee->email ?? '-').'</div><div class="eo-meta">'.e($employee->phone ?? '-').'</div>',
                        'department' => e($employee->department_name ?? 'General'),
                        'designation' => e($employee->designation_name ?? 'Executive'),
                        'employment_type' => e(ucfirst(str_replace('_', ' ', $employee->employment_type ?? '-'))),
                        'work_mode' => '<span class="eo-pill '.$workModeClass.'"><span class="eo-dot"></span>'.e(strtoupper($workMode)).'</span>',
                        'status' => '<span class="eo-pill '.$statusClass.'"><span class="eo-dot"></span>'.e(ucfirst($status)).'</span>',
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
                FacadesLog::error('Employee DataTable Error: '.$e->getMessage());

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
            ->orderByDesc($this->employeeTable.'.id')
            ->paginate(15);

        $departments = DB::table('departments')->orderBy('name')->get();

        return view('hrms.employee.index', compact('employees', 'departments'));
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
            $plainPassword = 'Orbosis@'.now()->year;

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($plainPassword),
                'system_role_id' => $request->system_role_id,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
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

            $employeeId = DB::table($this->employeeTable)->insertGetId([
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
            ]);

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
                FacadesLog::error('Mail failed: '.$mailEx->getMessage());
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
            ->join('users', 'users.id', '=', $this->employeeTable.'.user_id')
            ->leftJoin('departments', 'departments.id', '=', $this->employeeTable.'.department_id')
            ->leftJoin('designations', 'designations.id', '=', $this->employeeTable.'.designation_id')
            ->leftJoin('roles', 'roles.id', '=', $this->employeeTable.'.system_role_id')
            ->leftJoin($this->profileTable, $this->profileTable.'.employee_id', '=', $this->employeeTable.'.id')
            ->where($this->employeeTable.'.id', $employee)
            ->select(
                $this->employeeTable.'.*',
                'users.name',
                'users.email',
                $phoneSelect,
                'departments.name as department_name',
                'designations.name as designation_name',
                DB::raw($roleNameExpression.' as role_name'),
                $this->profileTable.'.date_of_birth',
                $this->profileTable.'.gender',
                $this->profileTable.'.address',
                $this->profileTable.'.highest_qualification',
                $this->profileTable.'.cgpa_percentage',
                $this->profileTable.'.total_experience',
                $this->profileTable.'.bank_account_no',
                $this->profileTable.'.bank_account_type',
                $this->profileTable.'.bank_holder_name',
                $this->profileTable.'.ifsc_code',
                $this->profileTable.'.bank_branch',
                $this->profileTable.'.profile_image',
                $this->profileTable.'.resume_file',
                $this->profileTable.'.profile_status',
                $this->profileTable.'.is_profile_completed'
            )
            ->first();

        abort_if(! $employeeData, 404);

        $departments = DB::table('departments')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

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

        return view('hrms.employee.manage', compact(
            'employeeData',
            'departments',
            'designations',
            'roles'
        ));
    }

    public function manageUpdate(Request $request, $employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$employeeData->user_id],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation_id' => ['required', 'exists:designations,id'],
            'reporting_manager_employee_id' => ['nullable', 'exists:employees_new,id'],
            'system_role_id' => ['required', 'exists:roles,id'],
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'intern', 'freelancer', 'contract'])],
            'work_mode' => ['required', Rule::in(['wfo', 'wfh', 'hybrid'])],
            'work_schedule_type' => ['nullable', Rule::in(['full_day', 'part_day', 'hourly', 'shift_based'])],
            'employment_status' => ['required', Rule::in(['active', 'resigned', 'terminated'])],
            'joining_date' => ['nullable', 'date'],
            'relieving_date' => ['nullable', 'date'],
            'internship_start_date' => ['nullable', 'date'],
            'internship_end_date' => ['nullable', 'date', 'after_or_equal:internship_start_date'],
            'is_paid_intern' => ['nullable', Rule::in(['0', '1', 0, 1])],
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

            if ($request->has('reporting_manager_employee_id')) {
                $employeeUpdateData['reporting_manager_employee_id'] = $request->filled('reporting_manager_employee_id')
                    ? $request->reporting_manager_employee_id
                    : null;
            }

            DB::table($this->employeeTable)->where('id', $employee)->update($employeeUpdateData);

            $this->salaryHistoryService->syncSalary(
                (int) $employee,
                $lifecyclePayload['employee_stage'],
                $lifecyclePayload['actual_salary'],
                $request->salary_effective_from ?: $this->salaryEffectiveDate($lifecyclePayload),
                $this->salaryHistoryReason($lifecyclePayload, $request->salary_change_reason, 'Salary update'),
                auth()->id()
            );

            $oldProfile = DB::table($this->profileTable)
                ->where('employee_id', $employee)
                ->first();

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
                'profile_status' => 'submitted',
                'is_profile_completed' => 0,
                'updated_at' => now(),
            ];

            if (! $oldProfile) {
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

        return view('hrms.employee.show', compact('employeeData'));
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
        $employees = DB::table($this->employeeTable)
            ->join('users', 'users.id', '=', $this->employeeTable.'.user_id')
            ->leftJoin('departments', 'departments.id', '=', $this->employeeTable.'.department_id')
            ->leftJoin('designations', 'designations.id', '=', $this->employeeTable.'.designation_id')
            ->leftJoin($this->profileTable, $this->profileTable.'.employee_id', '=', $this->employeeTable.'.id')
            ->where(function ($q) {
                $q->whereNull('employee_profiles.profile_status')
                    ->orWhereIn('employee_profiles.profile_status', ['pending', 'submitted', 'rejected']);
            })
            ->where(function ($q) {
                $q->whereNull('employee_profiles.is_profile_completed')
                    ->orWhere('employee_profiles.is_profile_completed', 0);
            })
            ->select(
                $this->employeeTable.'.id',
                $this->employeeTable.'.employee_code',
                'users.name',
                'users.email',
                'departments.name as department_name',
                'designations.name as designation_name',
                DB::raw("COALESCE(employee_profiles.profile_status, 'pending') as profile_status"),
                DB::raw("COALESCE(employee_profiles.is_profile_completed, 0) as is_profile_completed"),
                'employee_profiles.updated_at'
            )
            ->orderByDesc($this->employeeTable.'.id')
            ->get();

        $allCounts = DB::table($this->employeeTable)
            ->leftJoin($this->profileTable, $this->profileTable.'.employee_id', '=', $this->employeeTable.'.id')
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN employee_profiles.profile_status IS NULL OR employee_profiles.profile_status = 'pending' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN employee_profiles.profile_status = 'submitted' THEN 1 ELSE 0 END) as submitted"),
                DB::raw("SUM(CASE WHEN employee_profiles.profile_status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN employee_profiles.profile_status = 'rejected' THEN 1 ELSE 0 END) as rejected")
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

        return view('hrms.employee.profile.view', compact('profile'));
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
    abort_if(!$employeeData, 404);

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
        $oldProfile = DB::table($this->profileTable)
            ->where('employee_id', $id)
            ->first();

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

            // important: status flow
            'profile_status' => 'submitted',
            'is_profile_completed' => 0,

            'updated_at' => now(),
        ];

        if (!$oldProfile) {
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
        DB::table($this->profileTable)->updateOrInsert(
            ['employee_id' => $employee],
            [
                'profile_status' => 'approved',
                'is_profile_completed' => 1,
                'profile_completed_at' => now(),
                'rejection_reason' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()
            ->route('hrms.employees.pending_profiles')
            ->with('success', 'Profile completed and locked successfully ✅');
    }

    public function rejectProfile(Request $request, $employee)
    {
        DB::table($this->profileTable)
            ->where('employee_id', $employee)
            ->update([
                'profile_status' => 'rejected',
                'is_profile_completed' => 0,
                'rejection_reason' => $request->rejection_reason ?? 'Profile rejected by HR',
                'profile_completed_at' => null,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Profile rejected successfully.');
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
        ->join('users', 'users.id', '=', $this->employeeTable.'.user_id')
        ->leftJoin('departments', 'departments.id', '=', $this->employeeTable.'.department_id')
        ->leftJoin('designations', 'designations.id', '=', $this->employeeTable.'.designation_id')
        ->select(
            $this->employeeTable.'.id',
            $this->employeeTable.'.employee_code',
            'users.name',
            'departments.name as department_name',
            'designations.name as designation_name',
            $this->employeeTable.'.employment_type',
            $this->employeeTable.'.employee_stage',
            $this->employeeTable.'.joining_date',
            $this->employeeTable.'.probation_start_date',
            $this->employeeTable.'.probation_end_date',
            $this->employeeTable.'.probation_status',
            $this->employeeTable.'.internship_start_date',
            $this->employeeTable.'.internship_end_date',
            $this->employeeTable.'.internship_extended_to',
            $this->employeeTable.'.is_paid_intern'
        )
        ->where($this->employeeTable.'.employment_status', 'active')
        ->where($this->employeeTable.'.is_active', 1)
        ->where(function ($q) {
            $q->where(function ($probation) {
                $probation->where(function ($stage) {
                    $stage->where($this->employeeTable.'.employee_stage', 'probation')
                        ->orWhere(function ($legacy) {
                            $legacy->whereNull($this->employeeTable.'.employee_stage')
                                ->where($this->employeeTable.'.employment_type', 'full_time');
                        });
                })
                    ->where(function ($activeProbation) {
                        $activeProbation->whereNull($this->employeeTable.'.probation_status')
                            ->orWhereNotIn($this->employeeTable.'.probation_status', ['completed', 'confirmed']);
                    });
            })
            ->orWhere(function ($internship) {
                $internship->where(function ($stage) {
                    $stage->where($this->employeeTable.'.employee_stage', 'internship')
                        ->orWhere(function ($legacy) {
                            $legacy->whereNull($this->employeeTable.'.employee_stage')
                                ->where($this->employeeTable.'.employment_type', 'intern');
                        });
                    });
            });
        })
        ->orderByDesc($this->employeeTable.'.id')
        ->get();

    $departments = DB::table('departments')
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    return view('hrms.employee.probation-internship', compact('employees', 'departments'));
}

    public function exitEmployees()
    {
        $employees = DB::table($this->employeeTable)
            ->join('users', 'users.id', '=', $this->employeeTable.'.user_id')
            ->leftJoin('departments', 'departments.id', '=', $this->employeeTable.'.department_id')
            ->leftJoin('designations', 'designations.id', '=', $this->employeeTable.'.designation_id')
            ->select(
                $this->employeeTable.'.id',
                $this->employeeTable.'.employee_code',
                'users.name',
                'users.email',
                'departments.name as department_name',
                'designations.name as designation_name',
                $this->employeeTable.'.employment_status',
                $this->employeeTable.'.joining_date',
                $this->employeeTable.'.relieving_date',
                $this->employeeTable.'.is_active'
            )
            ->where(function ($q) {
                $q->whereIn($this->employeeTable.'.employment_status', ['resigned', 'terminated', 'inactive'])
                    ->orWhereNotNull($this->employeeTable.'.relieving_date')
                    ->orWhere($this->employeeTable.'.is_active', 0);
            })
            ->orderByDesc($this->employeeTable.'.id')
            ->get();

        return view('hrms.employee.exit', compact('employees'));
    }

    public function reportingStructure()
    {
        $teamCounts = DB::table($this->employeeTable)
            ->select('reporting_manager_employee_id', DB::raw('COUNT(*) as team_members_count'))
            ->whereNotNull('reporting_manager_employee_id')
            ->groupBy('reporting_manager_employee_id');

        $employees = DB::table($this->employeeTable.' as e')
            ->join('users as u', 'u.id', '=', 'e.user_id')
            ->leftJoin('departments as d', 'd.id', '=', 'e.department_id')
            ->leftJoin('designations as dg', 'dg.id', '=', 'e.designation_id')
            ->leftJoin($this->employeeTable.' as rm', 'rm.id', '=', 'e.reporting_manager_employee_id')
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

        return view('hrms.employee.reporting-structure', compact('employees'));
    }

    public function markPermanent($employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $updateData = [
            'probation_status' => 'completed',
            'employee_stage' => 'permanent',
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn($this->employeeTable, 'is_permanent')) {
            $updateData['is_permanent'] = 1;
        }

        DB::table($this->employeeTable)->where('id', $employee)->update($updateData);

        return redirect()
            ->route('hrms.employees.probation_internship')
            ->with('success', 'Employee marked permanent successfully.');
    }

    public function extendInternship(Request $request, $employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'internship_extended_to' => ['required', 'date'],
        ]);

        $updateData = [
            'employee_stage' => 'internship',
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn($this->employeeTable, 'internship_extended_to')) {
            $updateData['internship_extended_to'] = $request->internship_extended_to;
        } else {
            $updateData['internship_end_date'] = $request->internship_extended_to;
        }

        DB::table($this->employeeTable)->where('id', $employee)->update($updateData);

        return redirect()
            ->route('hrms.employees.probation_internship')
            ->with('success', 'Internship extended successfully.');
    }

    public function completeInternship(Request $request, $employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'next_stage' => ['required', Rule::in(['probation', 'permanent'])],
            'joining_date' => ['nullable', 'date'],
            'actual_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_effective_from' => ['nullable', 'date'],
            'salary_change_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $joiningDate = $request->joining_date
            ?: ($employeeData->joining_date ?: now()->toDateString());

        $updateData = [
            'employee_stage' => $request->next_stage,
            'joining_date' => $joiningDate,
            'internship_end_date' => $employeeData->internship_end_date ?: now()->toDateString(),
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ];

        if ($request->next_stage === 'probation') {
            $updateData['probation_start_date'] = $joiningDate;
            $updateData['probation_end_date'] = Carbon::parse($joiningDate)->addMonths(3)->toDateString();
            $updateData['probation_status'] = 'ongoing';
        } else {
            $updateData['probation_status'] = 'completed';

            if (Schema::hasColumn($this->employeeTable, 'is_permanent')) {
                $updateData['is_permanent'] = 1;
            }
        }

        DB::table($this->employeeTable)->where('id', $employee)->update($updateData);

        if ($request->filled('actual_salary')) {
            $this->salaryHistoryService->syncSalary(
                (int) $employee,
                $request->next_stage,
                $request->actual_salary,
                $request->salary_effective_from ?: $joiningDate,
                $request->salary_change_reason ?: 'Internship completed',
                auth()->id()
            );
        }

        return redirect()
            ->route('hrms.employees.probation_internship')
            ->with('success', 'Internship lifecycle updated successfully.');
    }

    public function markExit(Request $request, $employee)
    {
        $employeeData = DB::table($this->employeeTable)->where('id', $employee)->first();
        abort_if(! $employeeData, 404);

        $request->validate([
            'employment_status' => ['required', Rule::in(['resigned', 'terminated'])],
            'relieving_date' => ['nullable', 'date'],
        ]);

        DB::table($this->employeeTable)->where('id', $employee)->update([
            'employment_status' => $request->employment_status,
            'relieving_date' => $request->relieving_date ?: now()->toDateString(),
            'is_active' => 0,
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        if (! empty($employeeData->user_id) && Schema::hasColumn('users', 'is_active')) {
            DB::table('users')->where('id', $employeeData->user_id)->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('hrms.employees.exit')
            ->with('success', 'Employee exit status updated successfully.');
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

    private function findEmployeeProfileRecord($employee)
    {
        $phoneSelect = Schema::hasColumn('users', 'phone')
            ? 'users.phone'
            : DB::raw('NULL as phone');

        $roleNameExpression = Schema::hasColumn('roles', 'name')
            ? 'roles.name'
            : (Schema::hasColumn('roles', 'title') ? 'roles.title' : DB::raw("CONCAT('Role ', roles.id)"));

        return DB::table($this->employeeTable)
            ->join('users', 'users.id', '=', $this->employeeTable.'.user_id')
            ->leftJoin('departments', 'departments.id', '=', $this->employeeTable.'.department_id')
            ->leftJoin('designations', 'designations.id', '=', $this->employeeTable.'.designation_id')
            ->leftJoin('roles', 'roles.id', '=', $this->employeeTable.'.system_role_id')
            ->leftJoin($this->employeeTable.' as manager', 'manager.id', '=', $this->employeeTable.'.reporting_manager_employee_id')
            ->leftJoin('users as manager_user', 'manager_user.id', '=', 'manager.user_id')
            ->leftJoin($this->profileTable, $this->profileTable.'.employee_id', '=', $this->employeeTable.'.id')
            ->where($this->employeeTable.'.id', $employee)
            ->select(
                $this->employeeTable.'.*',
                $this->employeeTable.'.id as employee_id',
                'users.name',
                'users.email',
                $phoneSelect,
                'departments.name as department_name',
                'designations.name as designation_name',
                DB::raw($roleNameExpression.' as role_name'),
                'manager_user.name as manager_name',
                'manager.employee_code as manager_code',
                $this->profileTable.'.date_of_birth',
                $this->profileTable.'.gender',
                $this->profileTable.'.address',
                $this->profileTable.'.highest_qualification',
                $this->profileTable.'.cgpa_percentage',
                $this->profileTable.'.total_experience',
                $this->profileTable.'.bank_account_no',
                $this->profileTable.'.bank_account_type',
                $this->profileTable.'.bank_holder_name',
                $this->profileTable.'.ifsc_code',
                $this->profileTable.'.bank_branch',
                $this->profileTable.'.profile_image',
                $this->profileTable.'.resume_file',
                $this->profileTable.'.profile_status',
                $this->profileTable.'.is_profile_completed',
                $this->profileTable.'.profile_completed_at',
                $this->profileTable.'.rejection_reason'
            )
            ->first();
    }

  public function getDesignationsByDepartment($department)
    {
        $query = DB::table('designations')
            ->where('department_id', $department)
            ->select('id', 'name');

        if (Schema::hasColumn('designations', 'is_active')) {
            $query->where('is_active', 1);
        }

        return response()->json(
            $query->orderBy('name')->get()
        );
    }
}
