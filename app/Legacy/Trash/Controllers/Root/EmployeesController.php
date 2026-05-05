<?php

namespace App\Legacy\Trash\Controllers\Root;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\HRMS\Employee\StoreEmployeeRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use App\Models\HRMS\Leave\EmployeeLeaveM as EmployeeLeave;
use App\Models\Core\LogM as Log;
use App\Models\HRMS\Employee\PositionM as Position;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmployeeCredentialMail;   
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PDF;

class EmployeesController extends Controller
{
    private $employees;

    public function __construct()
    {
        $this->middleware('auth');  
        $this->employees = resolve(Employee::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Employee::with(['employeeDetail', 'department', 'position', 'manager']);

        // 1. SEARCH by Name or Employee ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('employee_id', 'LIKE', "%{$search}%");
            });
        }

        // 2. Filter by Department or Status
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('employees.status', '=', $request->status);
        }

        $employees = $query->orderBy('employees.id', 'desc')->paginate(10)->withQueryString();
        $departments = Department::all();

        return view('pages.employees-data', compact('employees', 'departments'));
    }

    public function create()
    {
        $roles = Role::all();
        $departments = Department::all();
        $positions = Position::all();
        $managers = [];
        if (Schema::hasColumn('employees', 'status')) {
            $managers = Employee::where('employees.status', '=', 'Active')->get();
        } else {
            $managers = Employee::all(); // Fallback if column missing for some reason
        }

        // Predict next ID for UI
        $lastEmployee = Employee::orderBy('id', 'desc')->first();
        $nextId = 'OG-EMP-001';
        if ($lastEmployee && $lastEmployee->employee_id) {
            $lastNumber = intval(substr($lastEmployee->employee_id, 7));
            $nextId = 'OG-EMP-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return view('pages.employees-data_create', compact('roles', 'departments', 'positions', 'managers', 'nextId'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        DB::beginTransaction();
        try {
            // 1. AUTO GENERATE EMPLOYEE ID
            $lastEmployee = Employee::lockForUpdate()->orderBy('id', 'desc')->first();
            $employee_id = 'OG-EMP-001';
            if ($lastEmployee && $lastEmployee->employee_id) {
                $lastNumber = intval(substr($lastEmployee->employee_id, 7));
                $employee_id = 'OG-EMP-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            }

            // 2. CREATE USER
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'is_active' => 1
            ]);

            // 3. HANDLE PHOTO
            $photoPath = 'images/profile.png'; // Default
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = $employee_id . '_' . time() . '.' . $photo->getClientOriginalExtension();
                $destinationPath = public_path('uploads/employees/photo');
                
                // Ensure directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                
                $photo->move($destinationPath, $photoName);
                $photoPath = 'uploads/employees/photo/' . $photoName;
            }

            // 4. CREATE EMPLOYEE
            $employeeStatus = 'Active'; // Default
            if ($request->filled('status')) {
                $employeeStatus = $request->status;
            } else if ($request->employment_type == 'Intern' || $request->probation_status == 'Probation') {
                $employeeStatus = 'Probation';
            }
            
            $internshipType = null;
            $internshipDuration = null;
            $internshipEndDate = null;
            $probationStatus = null;
            $probationStartDate = null;
            $probationEndDate = null;

            if ($request->employment_type == 'Intern') {
                $internshipType = $request->internship_type ?? 'Unpaid Intern';
                $internshipDuration = $request->internship_duration ?? 3;
                $internshipEndDate = Carbon::parse($request->start_of_contract)->addMonths($internshipDuration)->format('Y-m-d');
            } else if ($request->employment_type == 'Full-Time') {
                $probationStatus = 'Probation';
                $probationStartDate = $request->start_of_contract;
                $probationEndDate = $request->filled('probation_end_date') ? $request->probation_end_date : Carbon::parse($request->start_of_contract)->addMonths(3)->format('Y-m-d');
            }

            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_id' => $employee_id,
                'name' => $request->name,
                'employment_type' => $request->employment_type,
                'status' => $employeeStatus, // Legacy status
                'employee_status' => $request->employee_status, // WFH/WFO (Work Mode)
                'employment_status' => $request->employment_status, // Active/Resigned/Terminated
                
                'actual_salary' => $request->actual_salary,
                'bank_name' => $request->bank_name ?? 'N/A',
                'account_number' => $request->account_number,
                'account_type' => $request->account_type,
                'holder_name' => $request->holder_name,
                'ifsc_code' => $request->ifsc,
                'branch_name' => $request->branch,

                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'manager_id' => $request->manager_id,
                'start_of_contract' => $request->start_of_contract,
                // 'end_of_contract' => $request->end_of_contract,
                'internship_type' => $internshipType,
                'internship_duration' => $internshipDuration,
                'internship_end_date' => $internshipEndDate,
                'probation_status' => $probationStatus,
                'probation_start_date' => $probationStartDate,
                'probation_end_date' => $probationEndDate,
            ]);

            // 5. CREATE EMPLOYEE DETAILS
            $cvPath = null;
            if ($request->hasFile('cv')) {
                $cv = $request->file('cv');
                $cvName = $employee_id . '_cv_' . time() . '.' . $cv->getClientOriginalExtension();
                $cvDestinationPath = public_path('uploads/employees/cv');
                
                if (!file_exists($cvDestinationPath)) {
                    mkdir($cvDestinationPath, 0777, true);
                }
                
                $cv->move($cvDestinationPath, $cvName);
                $cvPath = 'uploads/employees/cv/' . $cvName;
            }

            EmployeeDetail::create([
                'employee_id' => $employee->id,
                'identity_number' => $employee_id,
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'phone' => $request->phone,
                'emergency_contact_number' => $request->emergency_contact_number,
                'address' => $request->address,
                'photo' => $photoPath,
                'cv' => $cvPath,
                'last_education' => $request->last_education,
                'gpa' => $request->gpa, // CGPA / Percentage
                'work_experience_in_years' => $request->work_experience_in_years,
            ]);

            // 6. LEAVE ALLOCATION LOGIC (Admin Override + Centralized Engine Fallback)
            $year = date('Y');
            if ($request->filled('allocated_pl') || $request->filled('allocated_sl')) {
                $alloc = \App\Models\HRMS\Leave\LeaveAllocationM::firstOrNew([
                    'employee_id' => $employee->id,
                    'year' => $year
                ]);
                
                if (!$alloc->exists) {
                    $alloc->used_pl = 0;
                    $alloc->used_sl = 0;
                    $alloc->lwp_days = 0;
                }
                
                if ($request->filled('allocated_pl')) $alloc->total_pl = $request->allocated_pl;
                if ($request->filled('allocated_sl')) $alloc->total_sl = $request->allocated_sl;
                
                $alloc->save();
            } else {
                $allocationController = new \App\Http\Controllers\Web\HRMS\Leave\LeaveAllocationC();
                $allocationController->calculateAllocationForEmployee($employee, $year);
            }

            // 7. ASSET ALLOCATION
            if ($request->filled('allocate_asset') && $request->allocate_asset !== '') {
                \App\Models\HRMS\Employee\AssetAllocationM::create([
                    'employee_id' => $employee->id,
                    'asset_type' => $request->allocate_asset,
                    'asset_id_sn' => $request->asset_id_sn,
                    'brand_model' => $request->brand_model,
                    'issue_date' => $request->issue_date ?? Carbon::now()->format('Y-m-d'),
                    'condition' => $request->condition ?? 'New',
                    'mobile_sim_number' => $request->mobile_sim_number,
                    'id_card_options' => $request->has('id_card_options') ? json_encode($request->id_card_options) : null,
                    'has_charger' => $request->boolean('has_charger'),
                    'has_bag' => $request->boolean('has_bag'),
                    'plan_details' => $request->plan_details,
                    'status' => 'Active'
                ]);
            }

            DB::commit();

            // 8. SEND CREDENTIALS MAIL (Final Verified Integration)
            try {
                // Ensure plain text password from request is sent
                $plainPassword = $request->password;
                
                // Trigger Mailable
                Mail::to($user->email)->send(new \App\Mail\EmployeeCredentialMail(
                    $user->name,
                    $user->email,
                    $employee->employee_id,
                    $plainPassword
                ));
                
                FacadesLog::info("Onboarding: Credential mail successfully dispatched to {$user->email}");
            } catch (\Exception $mailEx) {
                // Log detailed error but keep employee record as transaction is already committed
                FacadesLog::error("Onboarding Mail Failure for {$user->email}: " . $mailEx->getMessage());
                
                return redirect()->route('employees-data')->with('status', "Employee created successfully (ID: {$employee->employee_id}), but the welcome email failed to send. Please verify SMTP settings in .env.");
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Employee Onboarded successfully! Credentials have been sent to {$user->email}",
                    'redirect' => route('employees-data')
                ]);
            }

            return redirect()->route('employees-data')->with('status', "Employee Onboarded successfully! Credentials have been sent to {$user->email}");

        } catch (\Exception $e) {
            DB::rollback();
            FacadesLog::error("Employee Creation Failed: " . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Process Interrupted: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Process Interrupted: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Employee $employee)
    {
        $employee->load(['employeeDetail', 'department', 'position', 'manager', 'subordinates', 'assetAllocations']);
        return view('pages.employees-data_show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $roles = Role::all();
        $departments = Department::all();
        $positions = Position::all();
        $managers = Employee::where('status', 'Active')->where('id', '!=', $employee->id)->get();

        return view('pages.employees-data_edit', compact('employee', 'roles', 'departments', 'positions', 'managers'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'employment_type' => 'required|in:Intern,Full-Time,Contract,Freelancer',
            'status' => 'required|in:Active,Inactive,Probation,Completed',
            'employee_status' => 'required|in:WFH,WFO',
            'employment_status' => 'required|in:Active,Resigned,Terminated',
            
            'actual_salary' => 'required|numeric',
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_type' => 'required|in:Savings,Current',
            'holder_name' => 'required|string',
            'ifsc' => 'required|string',
            'branch' => 'required|string',

            'phone' => 'required',
            'address' => 'required',
            'photo' => 'nullable|image|max:5120',
            'cv' => 'nullable|mimes:pdf,doc,docx|max:5120',
            'gender' => 'required|in:M,F,O',
            'date_of_birth' => 'required|date',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
        ]);

        DB::beginTransaction();
        try {
            // Update User
            $user = User::findOrFail($employee->user_id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $request->role_id ?? $user->role_id,
                'is_active' => $request->employment_status == 'Active' ? 1 : 0
            ]);

            // Asset Allocation
            if ($request->filled('allocate_asset') && in_array($request->allocate_asset, ['Laptop', 'Mobile', 'ID Card'])) {
                \App\Models\HRMS\Employee\AssetAllocationM::create([
                    'employee_id' => $employee->id,
                    'asset_type' => $request->allocate_asset,
                    'assigned_date' => \Carbon\Carbon::now(),
                    'status' => 'Active'
                ]);
            }

            $internshipType = $employee->internship_type;
            $internshipDuration = $employee->internship_duration;
            $internshipEndDate = $employee->internship_end_date;
            $probationStatus = $employee->probation_status;
            $probationExtension = $employee->probation_extension;
            $probationEndDate = $employee->probation_end_date;

            if ($request->employment_type == 'Intern') {
                $internshipType = $request->internship_type ?? $internshipType;
                $internshipDuration = $request->internship_duration ?? $internshipDuration;
                if ($request->filled('internship_end_date')) {
                    $internshipEndDate = $request->internship_end_date;
                } elseif ($internshipDuration && $employee->start_of_contract) {
                    $internshipEndDate = Carbon::parse($employee->start_of_contract)->addMonths($internshipDuration)->format('Y-m-d');
                }
            } else if ($request->employment_type == 'Full-Time') {
                $probationStatus = $request->probation_status ?? $probationStatus;
                $probationExtension = $request->probation_extension ?? $probationExtension;
                
                if ($request->filled('probation_end_date')) {
                    $probationEndDate = $request->probation_end_date;
                } elseif ($probationExtension) {
                    $probationEndDate = Carbon::parse($employee->probation_start_date ?? $employee->start_of_contract)
                        ->addMonths(3 + $probationExtension)->format('Y-m-d');
                }
            }

            // Update Employee
            $employee->update([
                'name' => $request->name,
                'employment_type' => $request->employment_type,
                'status' => $request->status,
                'employee_status' => $request->employee_status,
                'employment_status' => $request->employment_status,
                'actual_salary' => $request->actual_salary,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_type' => $request->account_type,
                'holder_name' => $request->holder_name,
                'ifsc_code' => $request->ifsc,
                'branch_name' => $request->branch,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'manager_id' => $request->manager_id,
                'internship_type' => $internshipType,
                'internship_duration' => $internshipDuration,
                'internship_end_date' => $internshipEndDate,
                'probation_status' => $request->probation_status ?? $employee->probation_status,
                'probation_extension' => $probationExtension,
                'probation_end_date' => $probationEndDate,
            ]);

            // Update Leave Allocation if status changed to Permanent
            if ($employee->wasChanged('probation_status') && $employee->probation_status == 'Permanent') {
                $allocationController = new \App\Http\Controllers\Web\HRMS\Leave\LeaveAllocationC();
                $allocationController->calculateAllocationForEmployee($employee, Carbon::now()->year);
            }

            // Update Detail & Photo
            $detail = EmployeeDetail::where('employee_id', $employee->id)->first();
            $detailData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'emergency_contact_number' => $request->emergency_contact_number,
                'address' => $request->address,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'last_education' => $request->last_education,
                'gpa' => $request->gpa,
                'work_experience_in_years' => $request->work_experience_in_years,
            ];

            // Password handling
            if ($request->filled('password')) {
                $user->update(['password' => \Hash::make($request->password)]);
            }

            if ($request->hasFile('photo')) {
                if ($detail->photo && !str_contains($detail->photo, 'profile.png')) {
                    $oldPhotoPath = public_path($detail->photo);
                    if (file_exists($oldPhotoPath) && is_file($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }
                $photo = $request->file('photo');
                $photoName = $employee->employee_id . '_' . time() . '.' . $photo->getClientOriginalExtension();
                $photoDestination = public_path('uploads/employees/photo');
                
                if (!file_exists($photoDestination)) {
                    mkdir($photoDestination, 0777, true);
                }
                
                $photo->move($photoDestination, $photoName);
                $detailData['photo'] = 'uploads/employees/photo/' . $photoName;
            }

            if ($request->hasFile('cv')) {
                if ($detail->cv) {
                    $oldCvPath = public_path($detail->cv);
                    if (file_exists($oldCvPath) && is_file($oldCvPath)) {
                        unlink($oldCvPath);
                    }
                }
                $cv = $request->file('cv');
                $cvName = $employee->employee_id . '_cv_' . time() . '.' . $cv->getClientOriginalExtension();
                $cvDestination = public_path('uploads/employees/cv');
                
                if (!file_exists($cvDestination)) {
                    mkdir($cvDestination, 0777, true);
                }
                
                $cv->move($cvDestination, $cvName);
                $detailData['cv'] = 'uploads/employees/cv/' . $cvName;
            }

            $detail->update($detailData);

            DB::commit();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Employee updated successfully.',
                    'redirect' => route('employees-data')
                ]);
            }

            return redirect()->route('employees-data')->with('status', 'Employee updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Update Error: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Update Error: ' . $e->getMessage());
        }
    }

    public function destroy(Employee $employee)
    {
        $user = User::findOrFail($employee->user_id);
        $detail = EmployeeDetail::where('employee_id', $employee->id)->first();

        if ($detail->photo && !str_contains($detail->photo, 'profile.png')) {
            $photoPath = public_path($detail->photo);
            if (file_exists($photoPath) && is_file($photoPath)) {
                unlink($photoPath);
            }
        }

        if ($detail->cv) {
            $cvPath = public_path($detail->cv);
            if (file_exists($cvPath) && is_file($cvPath)) {
                unlink($cvPath);
            }
        }

        $employee->delete();
        $user->delete();

        return redirect()->route('employees-data')->with('status', 'Employee deleted successfully.');
    }

    public function print()
    {
        $employees = Employee::with(['employeeDetail', 'department', 'position'])->get();
        return view('pages.employees-data_print', compact('employees'));
    }
}
