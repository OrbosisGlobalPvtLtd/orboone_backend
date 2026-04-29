<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Mail\EmployeeCredentialMail;
use App\Services\HRMS\Employee\EmployeeFileS;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class EmployeeC extends Controller
{
    private string $employeeTable = 'employees_new';
    private string $profileTable = 'employee_profiles';

    public function index(Request $request)
    {
        $userPhoneSelect = Schema::hasColumn('users', 'phone')
            ? 'users.phone'
            : DB::raw('NULL as phone');

        $baseQuery = DB::table('employees_new')
            ->join('users', 'users.id', '=', 'employees_new.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id')
            ->leftJoin('designations', 'designations.id', '=', 'employees_new.designation_id')
            ->select(
                'employees_new.*',
                'users.name',
                'users.email',
                $userPhoneSelect,
                'departments.name as department_name',
                'designations.name as designation_name'
            );

        if ($request->has('ajax_table')) {
            try {
                $query = clone $baseQuery;

                if ($request->filled('department')) {
                    $query->where('employees_new.department_id', $request->department);
                }

                if ($request->filled('status')) {
                    $query->where('employees_new.employment_status', $request->status);
                }

                if ($request->filled('work_mode')) {
                    $query->where('employees_new.work_mode', $request->work_mode);
                }

                $recordsTotal = DB::table('employees_new')->count();

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
                    });

                    if (Schema::hasColumn('users', 'phone')) {
                        $query->orWhere('users.phone', 'like', "%{$searchValue}%");
                    }
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

                    if (Route::has('employees.profile.complete')) {
                        $actions .= '<a href="'.route('employees.profile.complete', $employee->id).'" class="eo-icon-btn eo-icon-profile"><i class="fas fa-user-check"></i></a>';
                    }

                    if (Route::has('employees-data.show')) {
                        $actions .= '<a href="'.route('employees-data.show', $employee->id).'" class="eo-icon-btn eo-icon-view"><i class="fas fa-eye"></i></a>';
                    }

                    if (Route::has('employees-data.edit')) {
                        $actions .= '<a href="'.route('employees-data.edit', $employee->id).'" class="eo-icon-btn eo-icon-edit"><i class="fas fa-edit"></i></a>';
                    }

                    $actions .= '</div>';

                    return [
                        'employee' => '
                        <div class="eo-emp">
                            <div class="eo-avatar">'.e($initial).'</div>
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
            ->orderByDesc('employees_new.id')
            ->paginate(15);

        $departments = DB::table('departments')
            ->orderBy('name')
            ->get();

        return view('hrms.employee.index', compact('employees', 'departments'));
    }

     /* =========================
           CREATE FORM
        ========================= */
     public function create()
     {
         $departments = DB::table('departments')->orderBy('name')->get();

         $designations = DB::table('designations')
             ->where('is_active', 1)
             ->orderBy('name')
             ->get();

         $reportingManagers = DB::table('employees_new')
             ->join('users', 'users.id', '=', 'employees_new.user_id')
             ->where('employees_new.is_active', 1)
             ->select('employees_new.id', 'employees_new.employee_code', 'users.name')
             ->get();

         $roles = DB::table('roles')
             ->where('status', 1)
             ->get();

         $nextEmployeeCode = $this->generateEmployeeCode();

         return view('hrms.employee.create', compact(
             'departments',
             'designations',
             'reportingManagers',
             'roles',
             'nextEmployeeCode'
         ));
     }

     /* =========================
        STORE EMPLOYEE
     ========================= */
     public function store(Request $request)
     {
         $request->validate([
             'name' => ['required'],
             'email' => ['required', 'email', 'unique:users,email'],
             'phone' => ['required'],
             'employment_type' => ['required', Rule::in(['full_time', 'intern', 'freelancer', 'contract'])],
             'work_mode' => ['required', Rule::in(['wfo', 'wfh'])],
             'department_id' => ['required'],
             'designation_id' => ['required'],
             'system_role_id' => ['required'],
         ]);

         DB::beginTransaction();

         try {
             /* =========================
                GENERATE EMPLOYEE CODE
             ========================= */
             $employeeCode = $this->generateEmployeeCode();

             /* =========================
                DEFAULT PASSWORD
                Example:
                2026 => Orbosis@2026
                2027 => Orbosis@2027
             ========================= */
             $plainPassword = 'Orbosis@'.now()->year;

             /* =========================
                CREATE USER
             ========================= */
             $userId = DB::table('users')->insertGetId([
                 'name' => $request->name,
                 'email' => $request->email,
                 'password' => Hash::make($plainPassword),
                 'system_role_id' => $request->system_role_id,
                 'is_active' => 1,
                 'created_at' => now(),
                 'updated_at' => now(),
             ]);

             /* =========================
                USER ROLE MAP
             ========================= */
             DB::table('user_roles')->updateOrInsert(
                 [
                     'user_id' => $userId,
                     'role_id' => $request->system_role_id,
                 ],
                 [
                     'created_at' => now(),
                     'updated_at' => now(),
                 ]
             );

             /* =========================
                EMPLOYEE LOGIC
             ========================= */
             $joiningDate = $request->joining_date ? Carbon::parse($request->joining_date) : null;

             $probationStart = null;
             $probationEnd = null;
             $probationStatus = 'pending';

             if ($request->employment_type === 'full_time' && $joiningDate) {
                 $probationStart = $joiningDate;
                 $probationEnd = $joiningDate->copy()->addMonths(3);
                 $probationStatus = 'ongoing';
             }

             $salary = $request->actual_salary ?? 0;

             if ($request->employment_type === 'intern' && $request->is_paid_intern == 0) {
                 $salary = 0;
             }

             /* =========================
                INSERT EMPLOYEE
             ========================= */
             $employeeId = DB::table($this->employeeTable)->insertGetId([
                 'user_id' => $userId,
                 'employee_code' => $employeeCode,
                 'system_role_id' => $request->system_role_id,
                 'department_id' => $request->department_id,
                 'designation_id' => $request->designation_id,
                 'reporting_manager_employee_id' => $request->reporting_manager_employee_id,
                 'employment_type' => $request->employment_type,
                 'work_mode' => $request->work_mode,
                 'joining_date' => $request->joining_date,
                 'employment_status' => 'active',
                 'probation_months' => 3,
                 'probation_start_date' => $probationStart,
                 'probation_end_date' => $probationEnd,
                 'probation_status' => $probationStatus,
                 'internship_start_date' => $request->internship_start_date,
                 'internship_end_date' => $request->internship_end_date,
                 'is_paid_intern' => $request->is_paid_intern,
                 'actual_salary' => $salary,
                 'is_active' => 1,
                 'created_by' => auth()->id(),
                 'updated_by' => auth()->id(),
                 'created_at' => now(),
                 'updated_at' => now(),
             ]);

             /* =========================
                CREATE EMPTY PROFILE
             ========================= */
             DB::table($this->profileTable)->insert([
                 'employee_id' => $employeeId,
                 'is_profile_completed' => 0,
                 'created_at' => now(),
                 'updated_at' => now(),
             ]);

             DB::commit();

             /* =========================
                SEND EMAIL
             ========================= */
             try {
                 Mail::to($request->email)->send(new EmployeeCredentialMail(
                     $request->name,
                     $request->email,
                     $employeeCode,
                     $plainPassword
                 ));

                 FacadesLog::info("Mail sent to {$request->email}");
             } catch (\Exception $mailEx) {
                 FacadesLog::error('Mail failed: '.$mailEx->getMessage());
             }

             return redirect()
                 ->route('employees-data')
                 ->with('success', 'Employee created. Login credentials sent to email.');
         } catch (\Throwable $e) {
             DB::rollBack();

             return back()
                 ->withInput()
                 ->with('error', $e->getMessage());
         }
     }

     /* =========================
            GENERATE EMP CODE
         ========================= */
     private function generateEmployeeCode(): string
     {
         $prefix = 'OG-EMP-';

         $last = DB::table($this->employeeTable)
             ->where('employee_code', 'like', $prefix.'%')
             ->orderByDesc('id')
             ->value('employee_code');

         $next = 1;

         if ($last) {
             $num = (int) str_replace($prefix, '', $last);
             $next = $num + 1;
         }

         return $prefix.str_pad($next, 3, '0', STR_PAD_LEFT);
     }

public function edit($employee)
{
    $employeeData = DB::table('employees_new')
        ->join('users', 'users.id', '=', 'employees_new.user_id')
        ->where('employees_new.id', $employee)
        ->select(
            'employees_new.*',
            'users.name',
            'users.email',
            'users.phone',
            'users.system_role_id as user_system_role_id'
        )
        ->first();

    abort_if(! $employeeData, 404);

    $departments = DB::table('departments')->orderBy('name')->get();

    $designations = DB::table('designations')
        ->where('is_active', 1)
        ->orderBy('name')
        ->get();

    $reportingManagers = DB::table('employees_new')
        ->join('users', 'users.id', '=', 'employees_new.user_id')
        ->where('employees_new.is_active', 1)
        ->where('employees_new.id', '!=', $employee)
        ->select('employees_new.id', 'employees_new.employee_code', 'users.name')
        ->orderBy('users.name')
        ->get();

    $roles = DB::table('roles')
        ->where('status', 1)
        ->orderBy('id')
        ->get();

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
    $employeeData = DB::table('employees_new')->where('id', $employee)->first();
    abort_if(! $employeeData, 404);

    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$employeeData->user_id],
        'phone' => ['required', 'string', 'max:20'],
        'employment_type' => ['required', Rule::in(['full_time', 'intern', 'freelancer', 'contract'])],
        'work_mode' => ['required', Rule::in(['wfo', 'wfh'])],
        'department_id' => ['required', 'exists:departments,id'],
        'designation_id' => ['required', 'exists:designations,id'],
        'system_role_id' => ['required', 'exists:roles,id'],
        'reporting_manager_employee_id' => ['nullable', 'exists:employees_new,id'],
        'joining_date' => ['nullable', 'date'],
        'relieving_date' => ['nullable', 'date'],
        'employment_status' => ['required', Rule::in(['active', 'resigned', 'terminated'])],
        'internship_start_date' => ['nullable', 'date'],
        'internship_end_date' => ['nullable', 'date', 'after_or_equal:internship_start_date'],
        'is_paid_intern' => ['nullable', Rule::in(['0', '1'])],
        'actual_salary' => ['nullable', 'numeric', 'min:0'],
    ]);

    if ($request->employment_type !== 'intern' && ! $request->joining_date) {
        return back()->withErrors(['joining_date' => 'Joining date is required.'])->withInput();
    }

    if ($request->employment_type === 'intern') {
        if (! $request->internship_start_date || ! $request->internship_end_date || $request->is_paid_intern === null) {
            return back()->withErrors(['internship_start_date' => 'Internship details are required.'])->withInput();
        }
    }

    DB::beginTransaction();

    try {
        $salary = $request->actual_salary ?? 0;

        if ($request->employment_type === 'intern' && $request->is_paid_intern == '0') {
            $salary = 0;
        }

        $probationStartDate = null;
        $probationEndDate = null;
        $probationStatus = 'pending';

        if ($request->employment_type === 'full_time' && $request->joining_date) {
            $probationStartDate = $request->joining_date;
            $probationEndDate = date('Y-m-d', strtotime($request->joining_date.' +3 months'));
            $probationStatus = $employeeData->probation_status ?: 'ongoing';
        }

        DB::table('users')->where('id', $employeeData->user_id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'system_role_id' => $request->system_role_id,
            'is_active' => $request->employment_status === 'active' ? 1 : 0,
            'updated_at' => now(),
        ]);

        DB::table('user_roles')->where('user_id', $employeeData->user_id)->delete();

        DB::table('user_roles')->insert([
            'user_id' => $employeeData->user_id,
            'role_id' => $request->system_role_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employees_new')->where('id', $employee)->update([
            'system_role_id' => $request->system_role_id,
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'reporting_manager_employee_id' => $request->reporting_manager_employee_id,
            'employment_type' => $request->employment_type,
            'work_mode' => $request->work_mode,
            'joining_date' => $request->employment_type === 'intern' ? null : $request->joining_date,
            'relieving_date' => $request->relieving_date,
            'employment_status' => $request->employment_status,
            'probation_start_date' => $probationStartDate,
            'probation_end_date' => $probationEndDate,
            'probation_status' => $probationStatus,
            'internship_start_date' => $request->employment_type === 'intern' ? $request->internship_start_date : null,
            'internship_end_date' => $request->employment_type === 'intern' ? $request->internship_end_date : null,
            'is_paid_intern' => $request->employment_type === 'intern' ? (int) $request->is_paid_intern : null,
            'actual_salary' => $salary,
            'is_active' => $request->employment_status === 'active' ? 1 : 0,
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        DB::commit();

        return redirect()->route('employees')->with('success', 'Employee updated successfully.');
    } catch (\Throwable $e) {
        DB::rollBack();

        return back()->withInput()->with('error', $e->getMessage());
    }
}

public function pendingProfiles()
{
    $employees = DB::table('employees_new')
        ->join('users', 'users.id', '=', 'employees_new.user_id')
        ->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id')
        ->leftJoin('designations', 'designations.id', '=', 'employees_new.designation_id')
        ->leftJoin('employee_profiles', 'employee_profiles.employee_id', '=', 'employees_new.id')
        ->where(function ($q) {
            $q->whereNull('employee_profiles.profile_status')
              ->orWhereIn('employee_profiles.profile_status', ['pending', 'submitted', 'rejected']);
        })
        ->select(
            'employees_new.id',
            'employees_new.employee_code',
            'users.name',
            'users.email',
            'departments.name as department_name',
            'designations.name as designation_name',
            DB::raw("COALESCE(employee_profiles.profile_status, 'pending') as profile_status"),
            DB::raw("COALESCE(employee_profiles.is_profile_completed, 0) as is_profile_completed"),
            'employee_profiles.updated_at'
        )
        ->orderByDesc('employees_new.id')
        ->get();

    $allCounts = DB::table('employees_new')
        ->leftJoin('employee_profiles', 'employee_profiles.employee_id', '=', 'employees_new.id')
        ->select(
            DB::raw("COUNT(*) as total"),
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
    $profile = DB::table('employee_profiles')
        ->join('employees_new', 'employees_new.id', '=', 'employee_profiles.employee_id')
        ->join('users', 'users.id', '=', 'employees_new.user_id')
        ->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id')
        ->leftJoin('designations', 'designations.id', '=', 'employees_new.designation_id')
        ->where('employee_profiles.employee_id', $id)
        ->select(
            'employee_profiles.*',
            'employees_new.employee_code',
            'employees_new.employment_type',
            'employees_new.employment_status',
            'users.name',
            'users.email',
            'users.phone',
            'departments.name as department_name',
            'designations.name as designation_name'
        )
        ->first();

    abort_if(!$profile, 404);

    return view('hrms.employee.profile.view', compact('profile'));
}

public function editProfile($id)
{
    $profile = DB::table('employee_profiles')
        ->where('employee_id', $id)
        ->first();

    return view('hrms.employee.profile.edit', compact('profile'));
}

public function updateProfile(Request $request, $id)
{
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
        $employee = DB::table('employees_new')->where('id', $id)->first();
        abort_if(! $employee, 404);

        $oldProfile = DB::table('employee_profiles')
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

            // Employee/HR ne data fill kiya hai, ab HR review ke liye submitted rakho
            'profile_status' => 'submitted',
            'is_profile_completed' => 0,
            'profile_completed_at' => null,
            'rejection_reason' => null,

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
                $employee->employee_code,
                'profile'
            );
        }

        if ($request->hasFile('resume_file')) {
            $profileData['resume_file'] = $fileService->upload(
                $request->file('resume_file'),
                $id,
                $employee->employee_code,
                'resume'
            );
        }

        DB::table('employee_profiles')->updateOrInsert(
            ['employee_id' => $id],
            $profileData
        );

        DB::commit();

        return redirect()
            ->route('employees.pending-profiles')
            ->with('success', 'Profile submitted successfully. HR review pending ✅');
    } catch (\Throwable $e) {
        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}

public function approveProfile($employee)
{
    DB::table('employee_profiles')->updateOrInsert(
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
        ->route('employees.pending-profiles')
        ->with('success', 'Profile completed and locked successfully ✅');
}

public function rejectProfile(Request $request, $employee)
{
    DB::table('employee_profiles')
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
    $employeeData = DB::table('employees_new')
        ->join('users', 'users.id', '=', 'employees_new.user_id')
        ->leftJoin('employee_profiles', 'employee_profiles.employee_id', '=', 'employees_new.id')
        ->where('employees_new.id', $employee)
        ->select(
            'employees_new.*',
            'users.name',
            'users.email',
            'employee_profiles.*'
        )
        ->first();

    abort_if(! $employeeData, 404);

    return view('hrms.employee.profile.complete', compact('employeeData'));
}

public function storeProfile(Request $request, $employee)
{
    $request->validate([
        'date_of_birth' => ['required', 'date'],
        'gender' => ['required'],
        'address' => ['required'],
        'highest_qualification' => ['required'],
        'cgpa_percentage' => ['required'],
        'total_experience' => ['required'],
        'bank_account_no' => ['required'],
        'bank_account_type' => ['required'],
        'bank_holder_name' => ['required'],
        'ifsc_code' => ['required'],
        'bank_branch' => ['required'],
    ]);

    DB::beginTransaction();

    try {
        $employeeData = DB::table('employees_new')->where('id', $employee)->first();
        abort_if(! $employeeData, 404);
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
            'ifsc_code' => $request->ifsc_code,
            'bank_branch' => $request->bank_branch,
            'is_profile_completed' => 1,
            'updated_at' => now(),
            'created_at' => now(),
        ];

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

        DB::table('employee_profiles')->updateOrInsert(
            ['employee_id' => $employee],
            $profileData
        );

        DB::commit();

        return redirect()->route('employees-data')
            ->with('success', 'Profile Completed Successfully ✅');
    } catch (\Throwable $e) {
        DB::rollBack();

        return back()->withInput()->with('error', $e->getMessage());
    }
}

public function probationInternship()
{
    $employees = DB::table('employees_new')
        ->join('users', 'users.id', '=', 'employees_new.user_id')
        ->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id')
        ->select(
            'employees_new.id',
            'employees_new.employee_code',
            'users.name',
            'departments.name as department_name',
            'employees_new.employment_type',
            'employees_new.probation_start_date',
            'employees_new.probation_end_date',
            'employees_new.probation_status',
            'employees_new.internship_start_date',
            'employees_new.internship_end_date',
            'employees_new.is_paid_intern'
        )
        ->where(function ($q) {
            $q->where('employment_type', 'full_time')
              ->orWhere('employment_type', 'intern');
        })
        ->orderByDesc('employees_new.id')
        ->get();

    return view('hrms.employee.probation.index', compact('employees'));
}
public function exitEmployees()
{
    $employees = DB::table('employees_new')
        ->join('users', 'users.id', '=', 'employees_new.user_id')
        ->leftJoin('departments', 'departments.id', '=', 'employees_new.department_id')
        ->select(
            'employees_new.id',
            'employees_new.employee_code',
            'users.name',
            'users.email',
            'departments.name as department_name',
            'employees_new.employment_status',
            'employees_new.relieving_date'
        )
        ->whereIn('employees_new.employment_status', ['resigned', 'terminated'])
        ->orderByDesc('employees_new.id')
        ->get();

    return view('hrms.employee.exit.index', compact('employees'));
}
public function reportingStructure()
{
    $employees = DB::table('employees_new as e')
        ->join('users as u', 'u.id', '=', 'e.user_id')
        ->leftJoin('employees_new as rm', 'rm.id', '=', 'e.reporting_manager_employee_id')
        ->leftJoin('users as ru', 'ru.id', '=', 'rm.user_id')
        ->select(
            'e.id',
            'e.employee_code',
            'u.name as employee_name',
            'ru.name as manager_name'
        )
        ->orderBy('u.name')
        ->get();

    return view('hrms.employee.reporting.index', compact('employees'));
}

}
