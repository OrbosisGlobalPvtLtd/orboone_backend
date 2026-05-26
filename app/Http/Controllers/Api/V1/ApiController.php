<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectManagement\TaskmanagementModel;
use App\Models\HRMS\Attendance\AttendanceM as Attendance;
use App\Models\HRMS\Leave\EmployeeLeaveM as EmployeeLeave;
use App\Models\Core\NotificationM as Notification;
use App\Models\Core\UserM as User;
use App\Models\HRMS\Employee\EmployeeM as Employee;
use App\Models\HRMS\Document\EmployeeDocumentM as EmployeeDocument;
use App\Models\HRMS\Payroll\PayrollM as Payroll;
use App\Models\HRMS\Payroll\PayslipM as Payslip;
use App\Models\HRMS\Employee\EmployeeProfileM as EmployeeDetail;
use App\Models\HRMS\Department\DepartmentM as Department;
use App\Models\HRMS\Employee\PositionM as Position;
use App\Models\HRMS\Leave\EmployeeLeaveRequestM as EmployeeLeaveRequest;
use App\Models\HRMS\Leave\HolidayM as Holiday;
use App\Models\HRMS\Leave\LeaveTypeM as LeaveType;
use App\Models\HRMS\Document\CompanyDocumentM as CompanyDocumentModal;
use App\Models\HRMS\Payroll\ClaimM as Claim;
use App\Models\HRMS\Payroll\FnFM as FnF;
use App\Models\HRMS\Payroll\SalaryStructureM as SalaryStructure;
use App\Models\HRMS\Document\EmployeeDocumentM as EmployeeDocumentModal;
use App\Models\HRMS\Document\DocumentTypeM as DocumentTypeModal;
use App\Services\HRMS\Storage\HrmsFileResolverS;
use App\Services\HRMS\Storage\HrmsStoragePathS;

use App\Services\Shared\AttendanceService;

class ApiController extends Controller
{
    protected $attendanceService;
    private HrmsStoragePathS $paths;
    private HrmsFileResolverS $resolver;

    public function __construct(
        AttendanceService $attendanceService,
        HrmsStoragePathS $paths,
        HrmsFileResolverS $resolver
    )
    {
        $this->attendanceService = $attendanceService;
        $this->paths = $paths;
        $this->resolver = $resolver;
    }
    // ------------------------------------------------
    // 1. LOGIN
    // ------------------------------------------------
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid Email or Password'], 401);
        }

        $user = Auth::user()->load('role'); // load role relation
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login Success',
            'token'   => $token,
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'is_active'  => $user->is_active,
                'role'       => $user->role ? $user->role->name : null, // return role name
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    // ------------------------------------------------
    // 2. LOGOUT
    // ------------------------------------------------
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'Logout Success']);
    }

    // ------------------------------------------------
    // 3. CLOCK-IN
    // ------------------------------------------------
    public function clockIn(Request $request)
    {
        $request->validate([
            'work_type'     => 'required|in:WFH,WFO',
            'note'          => 'nullable|string',
            'latitude'      => 'nullable|string',
            'longitude'     => 'nullable|string',
            'clock_in_time' => 'nullable|string' // Format: HH:mm or HH:mm:ss for testing
        ]);

        $userId = auth()->id();
        $result = $this->attendanceService->processPunchIn(
            $userId, 
            $request->work_type, 
            $request->note, 
            $request->latitude, 
            $request->longitude, 
            $request->clock_in_time
        );

        return response()->json([
            'status'  => $result['status'],
            'message' => $result['message'],
        ], $result['status'] === false ? 403 : 200);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'note'           => 'nullable|string',
            'latitude'       => 'nullable|string',
            'longitude'      => 'nullable|string',
            'clock_out_time' => 'nullable|string'
        ]);

        $userId = auth()->id();
        $result = $this->attendanceService->processPunchOut(
            $userId, 
            $request->note, 
            $request->latitude, 
            $request->longitude, 
            $request->clock_out_time
        );

        return response()->json([
            'status'  => $result['status'],
            'message' => $result['message'],
        ], $result['status'] === false && isset($result['message']) && str_contains($result['message'], 'error') ? 400 : ($result['status'] === false ? 403 : 200));
    }
    // ------------------------------------------------
    // 5. GET PROFILE
    // ------------------------------------------------
    // GET USER PROFILE (USER + EMPLOYEE + EMPLOYEE DETAIL + IMAGE)
    // ------------------------------------------------
    public function getProfile()
    {
        $user = auth()->user();

        // Load relations
        $user->load(['employee.employeeDetail']);

        //-----------------------------
        // EMPLOYEE RECORD
        //-----------------------------
        $employee = $user->employee;

        //-----------------------------
        // EMPLOYEE DETAIL RECORD
        //-----------------------------
        $detail = $employee?->employeeDetail;

        //-----------------------------
        // BUILD IMAGE URL
        //-----------------------------
        $imageUrl = null;

        if ($detail && $detail->image) {
            $imageUrl = $this->resolver->secureFileUrl($detail->image);
        }

        //-----------------------------
        // TODAY STATUS
        //-----------------------------
        $today = \Carbon\Carbon::now();
        
        $holidays = \App\Models\HRMS\Leave\HolidayM::whereDate('date', $today->format('Y-m-d'))->get();
        $nationalHolidays = \App\Models\HRMS\Leave\NationalHolidayM::whereDate('holiday_date', $today->format('Y-m-d'))->get();
        
        $allHolidays = $holidays->pluck('name')->merge($nationalHolidays->pluck('name'));
        
        $rawBirthdays = \App\Models\HRMS\Employee\EmployeeM::whereHas('employeeDetail', function ($query) use ($today) {
            $query->whereMonth('date_of_birth', $today->month)
                  ->whereDay('date_of_birth', $today->day);
        })
        ->with(['user', 'employeeDetail', 'department'])
        ->get();
            
        $birthdays = $rawBirthdays->map(function ($emp) {
            $empImageUrl = null;
            if ($emp->employeeDetail && $emp->employeeDetail->image) {
                $empImageUrl = $this->resolver->secureFileUrl($emp->employeeDetail->image);
            }

            return [
                'employee_id' => $emp->employee_id,
                'name'        => $emp->user->name ?? 'Unknown',
                'image_url'   => $empImageUrl,
                'department'  => $emp->department->name ?? null,
            ];
        });

        $todayStatus = [
            'is_holiday' => $allHolidays->isNotEmpty(),
            'holidays'   => $allHolidays,
            'festivals'  => $allHolidays,
            'birthdays'  => $birthdays,
            'events'     => [] 
        ];

        //-----------------------------
        // RETURN API RESPONSE
        //-----------------------------
        return response()->json([
            'name'          => $user->name,
            'email'         => $user->email,
            'employee'      => $employee ?? (object)[],
            'details'       => $detail ? [
                'id'                       => $detail->id,
                'identity_number'          => $detail->identity_number,
                'name'                     => $detail->name,
                'email'                    => $detail->email,
                'phone'                    => $detail->phone,
                'emergency_contact_number' => $detail->emergency_contact_number,
                'address'                  => $detail->address,
                'gender'                   => $detail->gender,
                'date_of_birth'            => $detail->date_of_birth,
                'last_education'           => $detail->last_education,
                'gpa'                      => $detail->gpa,
                'work_experience_in_years' => $detail->work_experience_in_years,
                'education_history'        => $detail->education_history ? json_decode($detail->education_history, true) : [],
                'experience_history'       => $detail->experience_history ? json_decode($detail->experience_history, true) : [],
                'photo_url'                => $this->resolver->secureFileUrl($detail->photo),
                'cv_url'                   => $this->resolver->secureFileUrl($detail->cv),
            ] : (object)[],
            'image_url'     => $imageUrl,
            'today_status'  => $todayStatus
        ]);
    }

    // ------------------------------------------------
    // 6. UPDATE PROFILE
    // ------------------------------------------------
      public function updateProfile(Request $request)
    {
        try {
            $user     = auth()->user();
            $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $user->id)->first();

            if (!$employee) {
                return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $detail = \App\Models\HRMS\Employee\EmployeeProfileM::where('employee_id', $employee->id)->first();

            // -----------------------------------------------
            // 1. VALIDATION
            // -----------------------------------------------
            $request->validate([
                // users table
                'name'                     => 'nullable|string|max:255',
                'email'                    => 'nullable|email|max:255|unique:users,email,' . $user->id,
                // employee_details table
                'phone'                    => 'nullable|string|max:20',
                'emergency_contact_number' => 'nullable|string|max:20',
                'address'                  => 'nullable|string|max:500',
                'gender'                   => 'nullable|in:M,F,O,male,female,other',
                'date_of_birth'            => 'nullable|date',
                'identity_number'          => 'nullable|string|max:50',
                'last_education'           => 'nullable|string|max:100',
                'gpa'                      => 'nullable|string|max:10',
                'work_experience_in_years' => 'nullable|integer|min:0',
                // JSON history
                'education_history'        => 'nullable',
                'experience_history'       => 'nullable',
                // File uploads
                'image'                    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'photo'                    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'cv'                       => 'nullable|mimes:pdf,doc,docx|max:5120',
                // employees bank details
                'bank_name'                => 'nullable|string|max:100',
                'account_number'           => 'nullable|string|max:50',
                'account_type'             => 'nullable|string|max:50',
                'holder_name'              => 'nullable|string|max:100',
                'ifsc_code'                => 'nullable|string|max:20',
                'branch_name'              => 'nullable|string|max:100',
            ]);

            DB::beginTransaction();

            // -----------------------------------------------
            // 2. UPDATE users TABLE  (name, email)
            // -----------------------------------------------
            $userChanges = [];
            if ($request->filled('name'))  $userChanges['name']  = $request->name;
            if ($request->filled('email')) $userChanges['email'] = $request->email;
            if (!empty($userChanges)) {
                $user->update($userChanges);
            }

            // -----------------------------------------------
            // 3. UPDATE employees TABLE
            //    Editable: name, bank fields, image, cv
            // -----------------------------------------------
            $empChanges  = [];
            $bankChanged = false;

            // Keep employees.name in sync with users.name
            if ($request->filled('name')) {
                $empChanges['name'] = $request->name;
            }

            // Bank details (live only in employees table)
            foreach (['bank_name', 'account_number', 'account_type', 'holder_name', 'ifsc_code', 'branch_name'] as $bankField) {
                if ($request->has($bankField)) {
                    $newVal = $request->input($bankField);
                    if ($employee->{$bankField} != $newVal) {
                        $bankChanged = true;
                    }
                    $empChanges[$bankField] = $newVal;
                }
            }

            // -----------------------------------------------
            // 4. UPDATE employee_details TABLE
            //    Columns: name, email, phone, address, gender,
            //             date_of_birth, identity_number,
            //             last_education, gpa,
            //             work_experience_in_years,
            //             education_history, experience_history,
            //             photo, cv
            // -----------------------------------------------
            $detailChanges = [];

            // Text fields direct to employee_details
            foreach (['phone', 'emergency_contact_number', 'address', 'identity_number', 'last_education', 'gpa', 'work_experience_in_years'] as $f) {
                if ($request->has($f)) {
                    $detailChanges[$f] = $request->input($f);
                }
            }

            // Sync name & email into employee_details too
            if ($request->filled('name'))  $detailChanges['name']  = $request->name;
            if ($request->filled('email')) $detailChanges['email'] = $request->email;

            // Gender → normalise to single char (M / F / O)
            if ($request->has('gender')) {
                $g = strtolower($request->gender);
                $detailChanges['gender'] = match(true) {
                    in_array($g, ['m', 'male'])   => 'M',
                    in_array($g, ['f', 'female']) => 'F',
                    default                        => 'O',
                };
            }

            // Date of birth
            if ($request->has('date_of_birth')) {
                $detailChanges['date_of_birth'] = $request->date_of_birth;
            }

            // Education history (JSON)
            if ($request->has('education_history')) {
                $edu = is_string($request->education_history)
                    ? json_decode($request->education_history, true)
                    : $request->education_history;
                $detailChanges['education_history'] = json_encode($edu);
            }

            // Experience history (JSON)
            if ($request->has('experience_history')) {
                $exp = is_string($request->experience_history)
                    ? json_decode($request->experience_history, true)
                    : $request->experience_history;
                $detailChanges['experience_history'] = json_encode($exp);
            }

            // -----------------------------------------------
            // 5. FILE UPLOADS
            // -----------------------------------------------

            // Profile photo  →  employees.image  &  employee_details.photo
            $photoFile = $request->file('image') ?: $request->file('photo');
            if ($photoFile) {
                $photoName = $employee->id . '_photo_' . time() . '.' . $photoFile->getClientOriginalExtension();
                $photoPath = $photoFile->storeAs($this->paths->employeeProfile($employee->id, 'avatar'), $photoName, 'private');

                $empChanges['image']     = $photoPath;   // employees.image
                $detailChanges['photo']  = $photoPath;   // employee_details.photo
            }

            // CV  →  employees.cv  &  employee_details.cv
            if ($request->hasFile('cv')) {
                $cvFile = $request->file('cv');
                $cvName = $employee->id . '_cv_' . time() . '.' . $cvFile->getClientOriginalExtension();
                $cvPath = $cvFile->storeAs($this->paths->employeeOnboarding($employee->id, 'resume'), $cvName, 'private');

                $empChanges['cv']    = $cvPath;   // employees.cv
                $detailChanges['cv'] = $cvPath;   // employee_details.cv
            }

            // -----------------------------------------------
            // 6. PERSIST CHANGES
            // -----------------------------------------------
            if (!empty($empChanges)) {
                $employee->update($empChanges);
            }

            if ($detail) {
                if (!empty($detailChanges)) {
                    $detail->update($detailChanges);
                }
            } elseif (!empty($detailChanges)) {
                // Auto-create employee_details row if missing
                $detailChanges['employee_id'] = $employee->id;
                $detail = \App\Models\HRMS\Employee\EmployeeProfileM::create($detailChanges);
            }

            DB::commit();

            // -----------------------------------------------
            // 7. NOTIFICATIONS
            // -----------------------------------------------
            $responseMessage = 'Profile Updated Successfully.';

            $admin = User::whereHas('role', fn($q) => $q->where('name', 'Admin'))->first();
            if ($admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title'   => 'Employee Profile Updated',
                    'message' => "Employee {$user->name} has updated their profile details.",
                ]);
            }

            if ($bankChanged) {
                $responseMessage .= ' Bank details were changed — please inform HR if needed.';
            }

            // -----------------------------------------------
            // 8. REFRESH & BUILD RESPONSE
            // -----------------------------------------------
            $user->refresh();
            $employee->refresh();
            $employee->load(['employeeDetail', 'department', 'position', 'user']);
            $detail = $employee->employeeDetail;

            return response()->json([
                'status'  => true,
                'message' => $responseMessage,

                // ── users TABLE ─────────────────────────────────
                'user' => [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'is_active' => $user->is_active,
                    'role'      => $user->role?->name,
                ],

                // ── employees TABLE ──────────────────────────────
                'employee' => [
                    'id'               => $employee->id,
                    'employee_id'      => $employee->employee_id,
                    'name'             => $employee->name,
                    'employment_type'  => $employee->employment_type,
                    'status'           => $employee->status,
                    'employee_status'  => $employee->employee_status,   // WFH / WFO
                    'employment_status'=> $employee->employment_status, // Active / Resigned
                    'department'       => $employee->department?->name,
                    'position'         => $employee->position?->name,
                    'start_of_contract'=> $employee->start_of_contract,
                    'end_of_contract'  => $employee->end_of_contract,
                    // Bank details
                    'bank_name'        => $employee->bank_name,
                    'account_number'   => $employee->account_number,
                    'account_type'     => $employee->account_type,
                    'holder_name'      => $employee->holder_name,
                    'ifsc_code'        => $employee->ifsc_code,
                    'branch_name'      => $employee->branch_name,
                    // File URLs
                    'image_url'        => $this->resolver->secureFileUrl($employee->image),
                    'cv_url'           => $this->resolver->secureFileUrl($employee->cv),
                ],

                // ── employee_details TABLE ───────────────────────
                'details' => [
                    'id'                       => $detail?->id,
                    'identity_number'          => $detail?->identity_number,
                    'name'                     => $detail?->name,
                    'email'                    => $detail?->email,
                    'phone'                    => $detail?->phone,
                    'emergency_contact_number' => $detail?->emergency_contact_number,
                    'address'                  => $detail?->address,
                    'gender'                   => $detail?->gender,
                    'date_of_birth'            => $detail?->date_of_birth,
                    'last_education'           => $detail?->last_education,
                    'gpa'                      => $detail?->gpa,
                    'work_experience_in_years' => $detail?->work_experience_in_years,
                    'education_history'        => $detail?->education_history
                                                    ? json_decode($detail->education_history, true)
                                                    : [],
                    'experience_history'       => $detail?->experience_history
                                                    ? json_decode($detail->experience_history, true)
                                                    : [],
                    'photo_url'                => $this->resolver->secureFileUrl($detail?->photo),
                    'cv_url'                   => $this->resolver->secureFileUrl($detail?->cv),
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation Error',
                'errors'  => $ve->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Update Error: ' . $e->getMessage(),
            ], 500);
        }
    }

       public function listHolidays()
    {
        $holidays = Holiday::orderBy('date', 'ASC')->get();
        return response()->json([
            'status'  => true,
            'message' => 'Holidays list fetched successfully',
            'data'    => $holidays
        ]);
    }


    // ------------------------------------------------
    // 7. ATTENDANCE LIST
    // ------------------------------------------------
    public function getAttendance()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->orderBy('date', 'DESC')
            ->paginate(10);

        return response()->json([
            'status'  => true,
            'message' => 'Attendance List Fetched Successfully',
            'data'    => $attendance->items(),     // only records
            'pagination' => [
                'total'        => $attendance->total(),
                'per_page'     => $attendance->perPage(),
                'current_page' => $attendance->currentPage(),
                'last_page'    => $attendance->lastPage(),
                'next_page_url' => $attendance->nextPageUrl(),
                'prev_page_url' => $attendance->previousPageUrl(),
            ]
        ]);
    }

    // ================================================================
    // LEAVE MANAGEMENT — Production-Ready API
    // Policy: 18 PL + 7 SL/year | Internship/Probation = 1 max
    // ================================================================

    /**
     * Private Helper: Calculate working days (excl. Sundays & holidays)
     * Weekend extension rule:
     *   Fri+Sat leave → Sunday also counted
     *   Thu+Fri leave → Sat+Sun also counted
     */
    private function calculateLeaveDays(\Carbon\Carbon $start, \Carbon\Carbon $end): array
    {
        $holidays = \App\Models\HRMS\Leave\NationalHolidayM::whereBetween('holiday_date', [
            $start->format('Y-m-d'), $end->format('Y-m-d'),
        ])->pluck('holiday_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))->toArray();

        // Weekend extension
        $extendedEnd = $end->copy();
        if ($extendedEnd->dayOfWeek === \Carbon\Carbon::SATURDAY && $start->lte($extendedEnd->copy()->subDay())) {
            $extendedEnd->addDay(); // include Sunday
        } elseif ($extendedEnd->dayOfWeek === \Carbon\Carbon::FRIDAY && $start->lte($extendedEnd->copy()->subDay())) {
            $extendedEnd->addDays(2); // include Sat+Sun
        }

        $days = 0;
        $temp = $start->copy();
        while ($temp->lte($extendedEnd)) {
            if ($temp->dayOfWeek !== \Carbon\Carbon::SUNDAY && !in_array($temp->format('Y-m-d'), $holidays)) {
                $days++;
            }
            $temp->addDay();
        }
        return ['days' => $days, 'effective_end' => $extendedEnd->format('Y-m-d')];
    }

    /** Private Helper: Monthly leave used by an employee */
    private function getMonthlyLeaveUsed(int $employeeId, int $month, int $year): float
    {
        return \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employeeId)
            ->whereIn('status', ['approved', 'pending'])
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->whereIn('leave_type', ['PL', 'SL'])
            ->sum('total_days');
    }

    // ------------------------------------------------
    // LEAVE TYPES
    // ------------------------------------------------

    /** POST /api/leave/types */
    public function createLeaveType(Request $request)
    {
        try {
            $request->validate([
                'name'         => 'required|string|max:100|unique:leave_types,name',
                'yearly_quota' => 'required|numeric|min:0',
                'accrual_type' => 'nullable|in:monthly,yearly,prorated',
            ]);
            $type = \App\Models\HRMS\Leave\LeaveTypeM::create([
                'name'         => $request->name,
                'yearly_quota' => $request->yearly_quota,
                'accrual_type' => $request->accrual_type ?? 'yearly',
            ]);
            return response()->json(['status' => true, 'message' => 'Leave type created.', 'data' => $type], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['status' => false, 'message' => 'Validation Error', 'errors' => $ve->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** GET /api/leave/types */
    public function listLeaveTypes()
    {
        return response()->json(['status' => true, 'data' => \App\Models\HRMS\Leave\LeaveTypeM::orderBy('name')->get()], 200);
    }

    // ------------------------------------------------
    // LEAVE BALANCE
    // ------------------------------------------------

    /** GET /api/leave/my-balance */
    public function getLeaveBalance()
    {
        try {
            $user     = auth()->user();
            $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $user->id)->first();
            if (!$employee) {
                return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $now   = \Carbon\Carbon::now();
            $year  = $now->year;
            $month = $now->month;

            // ── 1. Allocation (quota) ──────────────────────────────────────
            $alloc   = \App\Models\HRMS\Leave\LeaveAllocationM::where('employee_id', $employee->id)->where('year', $year)->first();
            $totalPl = (float) ($alloc->paid_allocated ?? 0);
            $totalSl = (float) ($alloc->sick_allocated ?? 0);
            $totalCompOff = (float) ($alloc->comp_off_allocated ?? 0);

            // ── 2. Used — from APPROVED applications (source of truth) ────
            $usedPl = (float) \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
                ->where('leave_type', 'PL')->where('status', 'approved')
                ->whereYear('start_date', $year)->sum('total_days');

            $usedSl = (float) \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
                ->where('leave_type', 'SL')->where('status', 'approved')
                ->whereYear('start_date', $year)->sum('total_days');

            $lwpDirect = (float) \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
                ->where('leave_type', 'LWP')->where('status', 'approved')
                ->whereYear('start_date', $year)->sum('total_days');

            $lwpEmbedded = (float) \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
                ->whereIn('leave_type', ['PL', 'SL'])->where('status', 'approved')
                ->whereYear('start_date', $year)->sum('lwp_days');

            $totalLwp = $lwpDirect + $lwpEmbedded;

            // ── 3. Remaining ───────────────────────────────────────────────
            $remPl = max(0, (float) ($alloc->paid_remaining ?? ($totalPl - $usedPl)));
            $remSl = max(0, (float) ($alloc->sick_remaining ?? ($totalSl - $usedSl)));
            $remCompOff = max(0, (float) ($alloc->comp_off_remaining ?? $totalCompOff));

            // ── 4. Pending (applied but not yet approved) ──────────────────
            $pendingPl = (float) \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
                ->where('leave_type', 'PL')->where('status', 'pending')
                ->whereYear('start_date', $year)->sum('total_days');

            $pendingSl = (float) \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
                ->where('leave_type', 'SL')->where('status', 'pending')
                ->whereYear('start_date', $year)->sum('total_days');

            // ── 5. Monthly usage breakdown ─────────────────────────────────
            $monthlyPl = (float) \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
                ->where('leave_type', 'PL')->whereIn('status', ['approved', 'pending'])
                ->whereYear('start_date', $year)->whereMonth('start_date', $month)->sum('total_days');

            $monthlySl = (float) \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
                ->where('leave_type', 'SL')->whereIn('status', ['approved', 'pending'])
                ->whereYear('start_date', $year)->whereMonth('start_date', $month)->sum('total_days');

            $monthlyTotal  = $monthlyPl + $monthlySl;
            $monthlyLimit  = 2;
            $monthlyRemain = max(0, $monthlyLimit - $monthlyTotal);

            // ── 6. Policy flags ────────────────────────────────────────────
            $isRestricted    = in_array($employee->employment_type, ['Internship', 'Probation'])
                            || ($employee->probation_status && $employee->probation_status !== 'Permanent');
            $yearEndRestrict = ($month >= 11);
            $usablePl        = $yearEndRestrict ? floor($remPl * 0.5) : $remPl;
            $usableSl        = $yearEndRestrict ? floor($remSl * 0.5) : $remSl;
            if ($isRestricted) {
                $usablePl = max(0, 1 - ($usedPl + $usedSl));
                $usableSl = 0;
            }

            return response()->json([
                'status' => true,
                'data'   => [

                    // ── OVERVIEW SUMMARY ──────────────────────────────────
                    'overview' => [
                        'employee_name'   => $user->name,
                        'employee_type'   => $employee->employment_type ?? 'Full-Time',
                        'year'            => $year,
                        'total_leave'     => (float) ($alloc->total_allocated ?? ($totalPl + $totalSl + $totalCompOff)),
                        'used_leave'      => $usedPl + $usedSl,    // all approved
                        'remaining_leave' => (float) ($alloc->total_remaining ?? ($remPl + $remSl + $remCompOff)),
                        'lwp_days'        => $totalLwp,            // LWP taken
                        'pending_leave'   => $pendingPl + $pendingSl, // awaiting approval
                    ],

                    // ── PAID LEAVE ────────────────────────────────────────
                    'paid_leave' => [
                        'total'      => $totalPl,
                        'used'       => $usedPl,
                        'remaining'  => $remPl,
                        'pending'    => $pendingPl,
                        'usable_now' => $usablePl,
                    ],

                    // ── SICK LEAVE ────────────────────────────────────────
                    'sick_leave' => [
                        'total'      => $totalSl,
                        'used'       => $usedSl,
                        'remaining'  => $remSl,
                        'pending'    => $pendingSl,
                        'usable_now' => $usableSl,
                    ],

                    'comp_off' => [
                        'total' => $totalCompOff,
                        'used' => (float) ($alloc->comp_off_used ?? 0),
                        'remaining' => $remCompOff,
                    ],

                    // ── MONTHLY USAGE ─────────────────────────────────────
                    'monthly_usage' => [
                        'month'                => $now->format('F Y'),
                        'monthly_limit'        => $monthlyLimit,
                        'paid_leave_used'      => $monthlyPl,
                        'sick_leave_used'      => $monthlySl,
                        'total_used_this_month'=> $monthlyTotal,
                        'remaining_this_month' => $monthlyRemain,
                    ],

                    // ── POLICY ────────────────────────────────────────────
                    'policy' => [
                        'is_restricted_employee' => $isRestricted,
                        'year_end_restriction'   => $yearEndRestrict,
                        'note'                   => $yearEndRestrict
                            ? 'Nov/Dec restriction: only 50% of remaining balance is usable.'
                            : ($isRestricted ? 'Internship/Probation policy: max 1 leave per period.' : null),
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ------------------------------------------------
    // APPLY LEAVE — Full Production Logic
    // ------------------------------------------------

    /** POST /api/leave/apply */
    public function applyLeave(Request $request)
    {
        try {
            // Normalize leave_type
            $typeInput = strtolower((string) $request->leave_type);
            if (str_contains($typeInput, 'sick') || $typeInput === 'sl') {
                $request->merge(['leave_type' => 'SL']);
            } elseif (str_contains($typeInput, 'paid') || $typeInput === 'pl') {
                $request->merge(['leave_type' => 'PL']);
            }

            $request->validate([
                'leave_type' => 'required|in:PL,SL',
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
                'reason'     => 'required|string|max:1000',
                'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            ], ['leave_type.in' => 'Invalid type. Use "Paid Leave" (PL) or "Sick Leave" (SL).']);

            $user     = auth()->user();
            $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $user->id)->first();
            if (!$employee) return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);

            $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $end   = \Carbon\Carbon::parse($request->end_date)->startOfDay();
            $year  = $start->year;
            $month = $start->month;

            $calc      = $this->calculateLeaveDays($start, $end);
            $totalDays = $calc['days'];

            if ($totalDays <= 0) {
                return response()->json(['status' => false, 'message' => 'No working days in the selected range (Sundays/Holidays only).'], 422);
            }

            // Sick leave > 2 days: certificate mandatory
            if ($request->leave_type === 'SL' && $totalDays > 2 && !$request->hasFile('attachment')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Medical certificate (attachment) is mandatory for Sick Leave > 2 days.',
                    'errors'  => ['attachment' => ['Medical certificate required for sick leave > 2 days.']],
                ], 422);
            }

            $attachmentPath = $request->hasFile('attachment')
                ? $request->file('attachment')->storeAs(
                    $this->paths->employeeLeave($employee->id, $request->leave_type === 'SL' ? 'medical-certificates' : 'attachments'),
                    'leave_' . time() . '_' . uniqid() . '.' . $request->file('attachment')->getClientOriginalExtension(),
                    'private'
                )
                : null;

            $isRestricted = in_array($employee->employment_type, ['Internship', 'Probation'])
                         || ($employee->probation_status && $employee->probation_status !== 'Permanent');

            $allocation     = \App\Models\HRMS\Leave\LeaveAllocationM::where('employee_id', $employee->id)->where('year', $year)->first();
            $finalLeaveType = $request->leave_type;
            $lwpDays        = 0;
            $warnings       = [];

            if ($isRestricted) {
                $alreadyUsed = ($allocation->paid_used ?? 0) + ($allocation->sick_used ?? 0);
                if ($alreadyUsed >= 1) {
                    $finalLeaveType = 'LWP';
                    $lwpDays        = $totalDays;
                    $warnings[]     = 'Probation/Internship policy: Only 1 leave allowed. This leave is LWP.';
                } elseif ($totalDays > 1) {
                    $lwpDays    = $totalDays - 1;
                    $warnings[] = "Probation/Internship: 1 day as {$finalLeaveType}, {$lwpDays} day(s) as LWP.";
                }
            } elseif (!$allocation) {
                $finalLeaveType = 'LWP';
                $lwpDays        = $totalDays;
                $warnings[]     = "No allocation for {$year}. Applied entirely as LWP.";
            } else {
                // Monthly cap: max 2 per month
                $monthUsed  = $this->getMonthlyLeaveUsed($employee->id, $month, $year);
                $remaining  = max(0, 2 - $monthUsed);
                if ($remaining < $totalDays) {
                    $over       = $totalDays - $remaining;
                    $lwpDays   += $over;
                    $warnings[] = "Monthly cap exceeded. {$over} day(s) auto-LWP.";
                }

                // Year-end restriction Nov/Dec = 50% balance
                $nowMonth  = \Carbon\Carbon::now()->month;
                $available = ($finalLeaveType === 'PL')
                    ? max(0, (float) ($allocation->paid_remaining ?? 0))
                    : max(0, (float) ($allocation->sick_remaining ?? 0));

                if ($nowMonth >= 11) {
                    $available  = floor($available * 0.5);
                    $warnings[] = "Year-end restriction: Only 50% balance ({$available} days) usable.";
                }

                if ($available < $totalDays) {
                    $deficit    = $totalDays - $available;
                    $lwpDays    = max($lwpDays, $deficit);
                    $warnings[] = "{$deficit} day(s) as LWP due to insufficient balance.";
                }
            }

            $application = \App\Models\HRMS\Leave\LeaveApplicationM::create([
                'employee_id' => $employee->id,
                'leave_type'  => ($lwpDays >= $totalDays) ? 'LWP' : $finalLeaveType,
                'start_date'  => $start->format('Y-m-d'),
                'end_date'    => $end->format('Y-m-d'),
                'total_days'  => $totalDays,
                'lwp_days'    => $lwpDays,
                'reason'      => $request->reason,
                'attachment'  => $attachmentPath,
                'status'      => 'pending',
            ]);

            $msg = "Leave submitted for {$totalDays} working day(s).";
            if (!empty($warnings)) $msg .= ' | ' . implode(' | ', $warnings);

            return response()->json([
                'status'   => true,
                'message'  => $msg,
                'warnings' => $warnings,
                'data'     => [
                    'application_id' => $application->id,
                    'employee_name'  => $user->name,
                    'leave_type'     => $application->leave_type,
                    'start_date'     => $start->format('d M Y'),
                    'end_date'       => $end->format('d M Y'),
                    'effective_end'  => \Carbon\Carbon::parse($calc['effective_end'])->format('d M Y'),
                    'total_days'     => $totalDays,
                    'lwp_days'       => $lwpDays,
                    'status'         => 'pending',
                    'attachment_url' => $this->resolver->secureFileUrl($attachmentPath),
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['status' => false, 'message' => 'Validation Error: ' . implode(', ', $ve->validator->errors()->all()), 'errors' => $ve->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ------------------------------------------------
    // MY LEAVES (serves my-requests, my-requests-status, my-balance routes)
    // ------------------------------------------------

    /** GET /api/leave/my-requests  |  /api/leave/my-requests-status */
    public function myLeaves()
    {
        try {
            $user     = auth()->user();
            $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', $user->id)->first();
            if (!$employee) return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);

            $year  = \Carbon\Carbon::now()->year;
            $alloc = \App\Models\HRMS\Leave\LeaveAllocationM::where('employee_id', $employee->id)->where('year', $year)->first();

            $balance = [
                'year' => $year,
                'total_allocated' => $alloc->total_allocated ?? 0,
                'paid_allocated' => $alloc->paid_allocated ?? 0,
                'sick_allocated' => $alloc->sick_allocated ?? 0,
                'comp_off_allocated' => $alloc->comp_off_allocated ?? 0,
                'total_used' => $alloc->total_used ?? 0,
                'paid_used' => $alloc->paid_used ?? 0,
                'sick_used' => $alloc->sick_used ?? 0,
                'comp_off_used' => $alloc->comp_off_used ?? 0,
                'lwp_used' => $alloc->lwp_used ?? 0,
                'total_remaining' => $alloc->total_remaining ?? 0,
                'paid_remaining' => $alloc->paid_remaining ?? 0,
                'sick_remaining' => $alloc->sick_remaining ?? 0,
                'comp_off_remaining' => $alloc->comp_off_remaining ?? 0,
            ];

            $history = \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')->get()
                ->map(fn($app) => [
                    'id'             => $app->id,
                    'leave_type'     => $app->leave_type,
                    'type_label'     => ['PL' => 'Paid Leave', 'SL' => 'Sick Leave', 'LWP' => 'Leave Without Pay'][$app->leave_type] ?? $app->leave_type,
                    'start_date'     => \Carbon\Carbon::parse($app->start_date)->format('d M Y'),
                    'end_date'       => \Carbon\Carbon::parse($app->end_date)->format('d M Y'),
                    'total_days'     => $app->total_days,
                    'lwp_days'       => $app->lwp_days ?? 0,
                    'reason'         => $app->reason,
                    'status'         => $app->status,
                    'admin_remark'   => $app->admin_remark,
                    'attachment_url' => $this->resolver->secureFileUrl($app->attachment),
                    'applied_on'     => $app->created_at->format('d M Y'),
                ]);

            return response()->json(['status' => true, 'balance' => $balance, 'history' => $history], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ------------------------------------------------
    // CANCEL LEAVE REQUEST
    // ------------------------------------------------

    /** POST /api/leave/requests/{id}/cancel */
    public function cancelLeaveRequest($id)
    {
        try {
            $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', auth()->id())->first();
            if (!$employee) return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);

            $application = \App\Models\HRMS\Leave\LeaveApplicationM::where('id', $id)->where('employee_id', $employee->id)->first();
            if (!$application) return response()->json(['status' => false, 'message' => 'Leave application not found.'], 404);

            if ($application->status !== 'pending') {
                return response()->json(['status' => false, 'message' => "Cannot cancel a '{$application->status}' leave."], 400);
            }

            $application->update(['status' => 'cancelled']);
            return response()->json(['status' => true, 'message' => 'Leave request cancelled.', 'data' => ['id' => $application->id, 'status' => 'cancelled']], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ------------------------------------------------
    // APPROVE LEAVE (Admin/HR)
    // ------------------------------------------------

    /** POST /api/leave/requests/{id}/approve */
    public function approveLeave($id)
    {
        try {
            $application = \App\Models\HRMS\Leave\LeaveApplicationM::with('employee')->find($id);
            if (!$application) return response()->json(['status' => false, 'message' => 'Leave application not found.'], 404);
            if ($application->status !== 'pending') return response()->json(['status' => false, 'message' => "Leave is already '{$application->status}'."], 400);

            $application->update(['status' => 'approved', 'approved_by' => auth()->id()]);

            // Update allocation counters
            $allocation = \App\Models\HRMS\Leave\LeaveAllocationM::where('employee_id', $application->employee_id)
                ->where('year', \Carbon\Carbon::parse($application->start_date)->year)->first();

            if ($allocation) {
                $paidDays = max(0, $application->total_days - ($application->lwp_days ?? 0));
                $lwpDays  = $application->lwp_days ?? 0;
                if ($application->leave_type === 'PL') $allocation->paid_used = (float) $allocation->paid_used + $paidDays;
                elseif ($application->leave_type === 'SL') $allocation->sick_used = (float) $allocation->sick_used + $paidDays;
                if ($lwpDays > 0) $allocation->lwp_used = (float) $allocation->lwp_used + $lwpDays;

                $allocation->total_used = (float) $allocation->paid_used
                    + (float) $allocation->sick_used
                    + (float) $allocation->comp_off_used;
                $allocation->paid_remaining = max(0, (float) $allocation->paid_allocated - (float) $allocation->paid_used);
                $allocation->sick_remaining = max(0, (float) $allocation->sick_allocated - (float) $allocation->sick_used);
                $allocation->comp_off_remaining = max(0, (float) $allocation->comp_off_allocated - (float) $allocation->comp_off_used);
                $allocation->total_remaining = (float) $allocation->paid_remaining
                    + (float) $allocation->sick_remaining
                    + (float) $allocation->comp_off_remaining;
                $allocation->save();
            }

            return response()->json(['status' => true, 'message' => 'Leave approved.', 'data' => ['id' => $application->id, 'status' => 'approved']], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ------------------------------------------------
    // REJECT LEAVE (Admin/HR)
    // ------------------------------------------------

    /** POST /api/leave/requests/{id}/reject */
    public function rejectLeave(Request $request, $id)
    {
        try {
            $application = \App\Models\HRMS\Leave\LeaveApplicationM::find($id);
            if (!$application) return response()->json(['status' => false, 'message' => 'Leave application not found.'], 404);
            if ($application->status !== 'pending') return response()->json(['status' => false, 'message' => "Leave is already '{$application->status}'."], 400);

            $application->update(['status' => 'rejected', 'admin_remark' => $request->remark ?? 'Rejected by HR/Admin.', 'approved_by' => auth()->id()]);
            return response()->json(['status' => true, 'message' => 'Leave rejected.', 'data' => ['id' => $application->id, 'status' => 'rejected']], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ------------------------------------------------
    // LEAVE CALENDAR
    // ------------------------------------------------

    /** GET /api/leave/calendar/my?month=3&year=2026 */
    public function myLeaveCalendar(Request $request)
    {
        try {
            $employee = \App\Models\HRMS\Employee\EmployeeM::where('user_id', auth()->id())->first();
            if (!$employee) return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);

            $month = (int)($request->month ?? \Carbon\Carbon::now()->month);
            $year  = (int)($request->year  ?? \Carbon\Carbon::now()->year);

            $leaves = \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
                ->whereYear('start_date', $year)->whereMonth('start_date', $month)
                ->whereIn('status', ['pending', 'approved'])->get()
                ->map(fn($app) => [
                    'id'         => $app->id,
                    'leave_type' => $app->leave_type,
                    'type_label' => ['PL' => 'Paid Leave', 'SL' => 'Sick Leave', 'LWP' => 'Leave Without Pay'][$app->leave_type] ?? $app->leave_type,
                    'start_date' => $app->start_date,
                    'end_date'   => $app->end_date,
                    'total_days' => $app->total_days,
                    'status'     => $app->status,
                    'reason'     => $app->reason,
                ]);

            $holidays = \App\Models\HRMS\Leave\NationalHolidayM::whereYear('holiday_date', $year)
                ->whereMonth('holiday_date', $month)->get(['name', 'holiday_date']);

            return response()->json(['status' => true, 'period' => \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y'), 'leaves' => $leaves, 'holidays' => $holidays], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** GET /api/leave/calendar/employees/{id}?month=3&year=2026 */
    public function employeeLeaveCalendar(Request $request, $id)
    {
        try {
            $employee = \App\Models\HRMS\Employee\EmployeeM::find($id);
            if (!$employee) return response()->json(['status' => false, 'message' => 'Employee not found.'], 404);

            $month = (int)($request->month ?? \Carbon\Carbon::now()->month);
            $year  = (int)($request->year  ?? \Carbon\Carbon::now()->year);

            $leaves = \App\Models\HRMS\Leave\LeaveApplicationM::where('employee_id', $employee->id)
                ->whereYear('start_date', $year)->whereMonth('start_date', $month)
                ->whereIn('status', ['pending', 'approved'])->get()
                ->map(fn($app) => [
                    'id'         => $app->id,
                    'leave_type' => $app->leave_type,
                    'type_label' => ['PL' => 'Paid Leave', 'SL' => 'Sick Leave', 'LWP' => 'Leave Without Pay'][$app->leave_type] ?? $app->leave_type,
                    'start_date' => $app->start_date,
                    'end_date'   => $app->end_date,
                    'total_days' => $app->total_days,
                    'status'     => $app->status,
                    'reason'     => $app->reason,
                ]);

            $holidays = \App\Models\HRMS\Leave\NationalHolidayM::whereYear('holiday_date', $year)
                ->whereMonth('holiday_date', $month)->get(['name', 'holiday_date']);

            return response()->json([
                'status'        => true,
                'employee_name' => $employee->user->name ?? 'N/A',
                'period'        => \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y'),
                'leaves'        => $leaves,
                'holidays'      => $holidays,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ------------------------------------------------
    // 9. NOTIFICATIONS
    // ------------------------------------------------
    public function getNotifications()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json($notifications);
    }

    // ============================================================
    // EMPLOYEE MANAGEMENT MODULE
    // ============================================================

    // HR: Create employee
    public function createEmployee(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'phone'         => 'required|string|max:20',
            'start_of_contract'  => 'required|date',
            'department_id' => 'nullable|integer',
            'position_id' => 'nullable|integer',
            'reporting_to'  => 'nullable|integer',
        ]);

        // Create user
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt('Password@123'), // default, HR should force reset
        ]);

        // Auto-generate employee ID (simple example)
        $employeeId = 'EMP-' . str_pad($user->id, 5, '0', STR_PAD_LEFT);

        $employee = Employee::create([
            'user_id'        => $user->id,
            'phone'          => $request->phone,
            'start_of_contract'   => $request->start_of_contract,
            'department_id'  => $request->department_id,
            'position_id'    => $request->position_id,
            'reporting_to'   => $request->reporting_to,
            'status'         => 'active',
            'employment_type' => $request->employment_type ?? 'Full-Time',
        ]);

        // Centralized Leave Allocation
        $allocationController = new \App\Http\Controllers\Web\HRMS\Leave\LeaveAllocationC();
        $allocationController->calculateAllocationForEmployee($employee, date('Y'));

        return response()->json([
            'message'  => 'Employee created successfully',
            'user'     => $user,
            'employee' => $employee
        ]);
    }

    // ============================================================
    // PAYROLL MODULE (MOBILE APP)
    // ============================================================

    /**
     * 0. PAYROLL DASHBOARD (MENU)
     */
    public function getPayrollDashboard()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'menu' => [
                    ['title' => 'Salary Structure', 'key' => 'salary_structure', 'icon' => 'wallet'],
                    ['title' => 'Monthly Salary', 'key' => 'monthly_salary', 'icon' => 'money'],
                    ['title' => 'Claims & Reimbursement', 'key' => 'claims', 'icon' => 'bill'],
                    ['title' => 'Claims History', 'key' => 'claims_history', 'icon' => 'history'],
                    ['title' => 'Pay Slip', 'key' => 'payslip', 'icon' => 'file'],
                    ['title' => 'Pay Slip History', 'key' => 'payslip_history', 'icon' => 'folder'],
                ]
            ]
        ]);
    }

    /**
     * 1. GET SALARY STRUCTURE
     */
    public function getSalaryStructure()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $structure = SalaryStructure::find($employee->salary_structure_id);

        if (!$structure) {
            return response()->json(['message' => 'Salary structure not assigned'], 404);
        }

        // Components are stored as JSON in DB
        $components = is_string($structure->components) ? json_decode($structure->components, true) : $structure->components;

        // Breakdown for UI
        $basicSalary = $components['basic_salary'] ?? 0;
        $allowances = [
            'HRA' => $components['hra'] ?? 0,
            'Travel Allowance' => $components['travel_allowance'] ?? 0,
            'Medical Allowance' => $components['medical_allowance'] ?? 0,
        ];
        $deductions = [
            'Provident Fund (PF)' => $components['pf'] ?? 0,
            'Professional Tax' => $components['professional_tax'] ?? 0,
            'TDS' => $components['tds'] ?? 0,
        ];

        $totalAllowances = array_sum($allowances);
        $totalDeductions = array_sum($deductions);
        $netSalary = $basicSalary + $totalAllowances - $totalDeductions;

        return response()->json([
            'status' => true,
            'data' => [
                'structure_name' => $structure->name,
                'net_salary' => $netSalary,
                'basic_pay' => [
                    'basic_salary' => $basicSalary
                ],
                'allowances' => $allowances,
                'deductions' => $deductions,
                'summary' => [
                    'total_allowances' => $totalAllowances,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary
                ]
            ]
        ]);
    }

    /**
     * 2. MONTHLY SALARY SUMMARY
     */
    public function getMonthlySalary(Request $request)
    {
        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year'  => 'nullable|integer'
        ]);

        $user = auth()->user();
        $employee = $user->employee;

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $payroll = Payroll::where('employee_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$payroll) {
            return response()->json(['message' => 'Payroll data not found for this month'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'period' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
                'paid_days' => $payroll->paid_days,
                'total_days' => $payroll->working_days,
                'total_allowances' => $payroll->gross_salary - ($payroll->basic_salary ?? 0), // simplified
                'deductions' => $payroll->total_deductions,
                'net_salary' => $payroll->net_salary
            ]
        ]);
    }

    /**
     * 3. SUBMIT CLAIM
     */
    public function submitClaim(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'amount'   => 'required|numeric|min:1',
            'reason'   => 'required|string',
            'file'     => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048'
        ]);

        $user = auth()->user();
        $employee = $user->employee;

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->storeAs(
                $this->paths->employeePayroll($employee->id, 'reimbursements'),
                'claim_' . time() . '_' . uniqid() . '.' . $request->file('file')->getClientOriginalExtension(),
                'private'
            );
        }

        $claim = Claim::create([
            'employee_id' => $employee->id,
            'category'    => $request->category,
            'amount'      => $request->amount,
            'file'        => $filePath,
            'status'      => 'pending',
            'reason'      => $request->reason, // Ensure model has this field or add to fillable
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Claim submitted successfully',
            'data'    => $claim
        ], 201);
    }

    /**
     * 4. CLAIMS HISTORY
     */
    public function getClaimsHistory()
    {
        $user = auth()->user();
        $employee = $user->employee;

        $claims = Claim::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $claims
        ]);
    }

    /**
     * 5. GET PAYSLIP DETAILS
     */
    public function getPayslip(Request $request)
    {
        // 1. Validate the Request
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer'
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // 2. Fetch the logged-in employee record
        $employee = $user->employee;
        if (!$employee) {
            return response()->json(['status' => false, 'message' => 'Employee record not found.'], 404);
        }

        // 3. Fetch the payroll for that specific month & year
        $payroll = Payroll::where('employee_id', $employee->id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if (!$payroll) {
            return response()->json(['status' => false, 'message' => 'Payslip data not found for the selected month and year.'], 404);
        }

        // 4. Fetch the salary structure as a fallback breakdown
        $structure = null;
        if (!empty($employee->salary_structure_id)) {
            $structure = SalaryStructure::find($employee->salary_structure_id);
        }

        $components = [];
        if ($structure && !empty($structure->components)) {
            $components = is_string($structure->components) ? json_decode($structure->components, true) : $structure->components;
        }

        // 5. Build and return the proper API response
        return response()->json([
            'status' => true,
            'message' => 'Payslip fetched successfully.',
            'data' => [
                'employee_details' => [
                    'employee_name' => $user->name,
                    'employee_id'   => $employee->employeeDetail->identity_number ?? 'N/A',
                    'designation'   => $employee->position->name ?? 'N/A',
                    'department'    => $employee->department->name ?? 'N/A',
                    'joining_date'  => $employee->start_of_contract ?? 'N/A',
                ],
                'payslip_info' => [
                    'month_year'         => date('F Y', mktime(0, 0, 0, $request->month, 1, $request->year)),
                    'paid_days'          => $payroll->paid_days ?? 0,
                    'total_working_days' => $payroll->working_days ?? 0,
                    'status'             => $payroll->status ?? 'Processed',
                ],
                'earnings' => [
                    // Priority to processed payroll table data, else fallback to template structure
                    'Basic Salary'      => $payroll->basic ?? ($components['basic_salary'] ?? 0),
                    'HRA'               => $payroll->hra ?? ($components['hra'] ?? 0),
                    'Other Allowance'   => $payroll->allowance ?? 0,
                    'Travel Allowance'  => $components['travel_allowance'] ?? 0,
                    'Medical Allowance' => $components['medical_allowance'] ?? 0,
                ],
                'deductions' => [
                    'Professional Tax'  => $payroll->pt ?? ($components['professional_tax'] ?? 0),
                    'PF'                => $components['pf'] ?? 0,
                    'TDS'               => $components['tds'] ?? 0,
                ],
                'summary' => [
                    // Use payroll totals directly as they reflect actual calculations
                    'gross_salary'      => $payroll->gross_salary ?? 0,
                    'total_deductions'  => $payroll->total_deductions ?? 0,
                    'net_salary'        => $payroll->net_salary ?? 0,
                ]
            ]
        ]);
    }

    /**
     * 6. PAYSLIP HISTORY (LIST)
     */
    public function getPayslipHistory()
    {
        $user = auth()->user();
        $employee = $user->employee;

        $payslips = Payslip::where('employee_id', $employee->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $payslips->map(function($item) {
                return [
                    'id' => $item->id,
                    'period' => date('F Y', mktime(0, 0, 0, $item->month, 1, $item->year)),
                    'file_url' => $this->resolver->secureFileUrl($item->file_path),
                    'month' => $item->month,
                    'year' => $item->year
                ];
            })
        ]);
    }

    // HR: List employees
    public function listEmployees(Request $request)
    {
        $employees = Employee::with('user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('id', 'DESC')
            ->paginate(20);

        return response()->json($employees);
    }

    // HR: Show employee
    public function showEmployee($id)
    {
        $employee = Employee::with('user')->find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json($employee);
    }

    // HR: Update employee (except employee_code)
    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::with('user')->find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $request->validate([
            'name'          => 'nullable|string|max:255',
            'email'         => 'nullable|email|unique:users,email,' . $employee->user_id,
            'start_of_contract'  => 'nullable|date',
            'department_id' => 'nullable|integer',
            'position_id' => 'nullable|integer',
            'reporting_to'  => 'nullable|integer',
        ]);

        if ($request->filled('name')) {
            $employee->user->name = $request->name;
        }
        if ($request->filled('email')) {
            $employee->user->email = $request->email;
        }
        $employee->user->save();

        $employee->update($request->only([
            'start_of_contract',
            'department_id',
            'position_id',
            'reporting_to',
        ]));

        return response()->json([
            'message'  => 'Employee updated successfully',
            'employee' => $employee->fresh('user'),
        ]);
    }

    // HR: Archive / deactivate employee
    public function archiveEmployee($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employee->status = 'archived';
        $employee->save();

        return response()->json(['message' => 'Employee archived successfully']);
    }

    // Job / Role / Department / Designation mapping
    public function getJobDetails($id)
    {
        $employee = Employee::with(['department', 'designation', 'manager.user'])->find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json($employee);
    }
    public function updateJobDetails(Request $request, $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $request->validate([
            'department_id'  => 'required|integer',
            'position_id' => 'required|integer',
            'reporting_to'   => 'nullable|integer|different:id',
        ]);

        // Basic circular check (simplified)
        if ($request->reporting_to && $request->reporting_to == $employee->id) {
            return response()->json(['message' => 'Employee cannot report to self'], 422);
        }

        $employee->update([
            'department_id'  => $request->department_id,
            'position_id' => $request->position_id,
            'reporting_to'   => $request->reporting_to,
        ]);

        return response()->json([
            'message' => 'Job details updated successfully',
            'data'    => $employee->fresh(),
        ]);
    }

    // Employment Status
    public function getEmploymentStatus($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json([
            'status'            => $employee->employment_status ?? null,
            'probation_end'     => $employee->probation_end_date ?? null,
            'contract_start'    => $employee->contract_start_date ?? null,
            'contract_end'      => $employee->contract_end_date ?? null,
        ]);
    }
    public function updateEmploymentStatus(Request $request, $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $request->validate([
            'status'             => 'required|in:probation,confirmed,contract,intern',
            'probation_end_date' => 'nullable|date',
            'contract_start_date' => 'nullable|date',
            'contract_end_date'  => 'nullable|date|after_or_equal:contract_start_date',
        ]);

        $employee->employment_status   = $request->status;
        $employee->probation_end_date  = $request->probation_end_date;
        $employee->contract_start_date = $request->contract_start_date;
        $employee->contract_end_date   = $request->contract_end_date;
        $employee->save();

        return response()->json([
            'message' => 'Employment status updated successfully',
            'data'    => $employee,
        ]);
    }

    public function uploadEmployeeDocument(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'file'             => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'expiry_date'      => 'nullable|date'
        ]);

        $type = DocumentTypeModal::findOrFail($request->document_type_id);

        $employee = Employee::where('user_id', $user->id)->first();
        $basePath = $employee
            ? $this->paths->mapEmployeeDocumentType($employee->id, $this->paths->normalizeDocType((string) ($type->code ?: $type->name)))
            : 'hrms/company/templates/document-generation';
        $path = $request->file('file')->storeAs(
            $basePath,
            'doc_' . time() . '_' . uniqid() . '.' . $request->file('file')->getClientOriginalExtension(),
            'private'
        );

        $document = EmployeeDocumentModal::updateOrCreate(
            [
                // UNIQUE KEY MATCH
                'user_id'          => $user->id,
                'document_type_id' => $type->id,
            ],
            [
                'file_path'   => $path,
                'expiry_date' => $type->has_expiry ? $request->expiry_date : null,
                'status'      => 'verified',
                'uploaded_by' => auth()->id(),
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Document uploaded successfully',
            'data'    => $document->load('type')
        ], 200);
    }

    // ============================================================
    // HR SIDE – LIST EMPLOYEE DOCUMENTS
    // ============================================================
    public function listEmployeeDocuments($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $docs = EmployeeDocumentModal::with('type')
            ->where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($docs);
    }

    // ============================================================
    // HR SIDE – VERIFY / REJECT DOCUMENT
    // ============================================================
    public function verifyEmployeeDocument(Request $request, $id, $docId)
    {
        $request->validate([
            'status'           => 'required|in:verified,rejected',
            'rejection_reason' => 'nullable|string|max:500'
        ]);

        $doc = EmployeeDocumentModal::where('user_id', $id)->find($docId);
        if (!$doc) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $doc->status = $request->status;
        $doc->rejection_reason = $request->status === 'rejected'
            ? $request->rejection_reason
            : null;

        $doc->save();

        return response()->json([
            'message' => 'Document status updated',
            'data'    => $doc
        ]);
    }

    // ============================================================
    // HR SIDE – DELETE DOCUMENT (BLOCK VERIFIED)
    // ============================================================
    public function deleteEmployeeDocument($id, $docId)
    {
        $doc = EmployeeDocumentModal::where('user_id', $id)->find($docId);
        if (!$doc) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        if ($doc->status === 'verified') {
            return response()->json([
                'message' => 'Verified document cannot be deleted'
            ], 403);
        }

        Storage::disk('private')->delete($doc->file_path);
        $doc->delete();

        return response()->json(['message' => 'Document deleted']);
    }

    // ============================================================
    // EMPLOYEE SIDE – VIEW MY DOCUMENTS
    // ============================================================
    // public function myDocuments()
    // {
    //     $docs = EmployeeDocumentModal::with('type')
    //         ->where('user_id', auth()->id())
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return response()->json($docs);
    // }

    // ============================================================
    // COMPANY POLICY DOCUMENTS
    // ============================================================
    public function listPolicyDocuments()
    {
        // Assuming EmployeeDocument has type "policy" for company-wide docs
        $docs = EmployeeDocument::whereNull('employee_id')
            ->where('category', 'policy')
            ->get();

        return response()->json($docs);
    }

    public function uploadPolicyDocument(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('file')->storeAs(
            $this->paths->companyPolicy('general'),
            'policy_' . time() . '_' . uniqid() . '.' . $request->file('file')->getClientOriginalExtension(),
            'private'
        );

        $doc = EmployeeDocument::create([
            'employee_id' => null,
            'category'    => 'policy',
            'title'       => $request->title,
            'file_path'   => $path,
            'status'      => 'active',
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Policy document uploaded',
            'data'    => $doc,
        ], 201);
    }

    // ============================================================
    // ATTENDANCE & TIME TRACKING (additional)
    // ============================================================

    public function getMyAttendanceCalendar(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $userId = auth()->id();
        $attendance = Attendance::where('user_id', $userId)
            ->whereBetween('date', [
                $request->month . '-01',
                $request->month . '-31',
            ])->get();

        return response()->json($attendance);
    }
    public function requestRegularization(Request $request)
    {
        // Placeholder: you should create Regularization model & table
        $request->validate([
            'date'         => 'required|date',
            'type'         => 'required|in:missed_in,missed_out,incorrect',
            'requested_in' => 'nullable',
            'requested_out' => 'nullable',
            'reason'       => 'required|string',
        ]);

        return response()->json([
            'message' => 'Regularization request API stub – implement with Regularization model',
        ], 201);
    }

    public function myRegularizationRequests()
    {
        return response()->json([
            'message' => 'List regularization requests – implement after creating model',
        ]);
    }

    public function approveRegularization($id, Request $request)
    {
        return response()->json([
            'message' => 'Approve regularization stub – implement with business logic',
        ]);
    }

    public function manualAttendanceUpdate(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|integer',
            'date'      => 'required|date',
            'status'    => 'required|in:Present,Absent,Leave,Holiday,WeekOff,HalfDay',
            'reason'    => 'required|string',
        ]);

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $request->user_id, 'date' => $request->date],
            ['status'  => $request->status]
        );

        return response()->json([
            'message' => 'Attendance updated by HR',
            'data'    => $attendance,
        ]);
    }
    public function lateEarlyReport(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        return response()->json([
            'message' => 'Late/Early report stub – implement calculation based on shift rules',
        ]);
    }

    public function configureGeofence(Request $request)
    {
        $request->validate([
            'office_name' => 'required|string',
            'latitude'    => 'required',
            'longitude'   => 'required',
            'radius'      => 'required|numeric',
        ]);

        return response()->json([
            'message' => 'Geofence config stub – persist to geofence settings table',
        ]);
    }
    public function configureAllowedIps(Request $request)
    {
        $request->validate([
            'ips' => 'required|array',
        ]);

        return response()->json([
            'message' => 'Allowed IPs config stub – persist to config table',
        ]);
    }



    // ============================================================
    // TASK MANAGEMENT MODULE
    // ============================================================

    // ADMIN: Create a new task and assign it to an employee
    public function createTask(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'required|string',
            'due_date'    => 'required|date',
            'user_id'     => 'required|exists:users,id', // Employee's user ID
        ]);

        $task = TaskmanagementModel::create($request->all());

        return response()->json([
            'status'  => true,
            'message' => 'Task created and assigned successfully',
            'data'    => $task,
        ], 201);
    }

    // ADMIN: Get all tasks for all employees, with filters
     public function myTasks()
    {
        $tasks = TaskmanagementModel::where('user_id', auth()->id())
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Tasks fetched successfully',
            'data' => $tasks,
        ]);
    }

    // -------------------- TASK DETAILS --------------------
    public function taskDetail($id)
    {
        $task = TaskmanagementModel::where('user_id', auth()->id())
            ->find($id);

        if (!$task) {
            return response()->json([
                'status' => false,
                'message' => 'Task not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $task,
        ]);
    }
    // ADMIN: Update any task details (title, description, due date, assigned user, status)
    public function adminUpdateTask(Request $request, $id)
    {
        $request->validate([
            'title'       => 'nullable|string',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'user_id'     => 'nullable|exists:users,id',
            'status'      => 'nullable|in:pending,progress,completed,overdue',
        ]);

        $task = TaskmanagementModel::find($id);

        if (!$task) {
            return response()->json(['status' => false, 'message' => 'Task not found.'], 404);
        }

        $task->update($request->all());

        return response()->json([
            'status'  => true,
            'message' => 'Task record updated successfully by admin.',
            'data'    => $task->fresh(),
        ]);
    }

    // EMPLOYEE: Get all tasks assigned to the authenticated user
    // public function getMyTasks()
    // {
    //     $tasks = TaskmanagementModel::where('user_id', auth()->id())
    //         ->orderBy('due_date', 'desc')
    //         ->get();

    //     return response()->json(['status' => true, 'data' => $tasks]);
    // }

    // EMPLOYEE/ADMIN: Get details of a single task
    public function getTaskDetails($id)
    {
        $task = TaskmanagementModel::with('user:id,name')->find($id);

        if (!$task) {
            return response()->json(['status' => false, 'message' => 'Task not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $task]);
    }

    // EMPLOYEE: Update my assigned task (Status and Progress Updates)
    public function updateMyTask(Request $request, $id)
    {
        $request->validate([
            'status'      => 'required|in:pending,progress,completed',
            'description' => 'required|string', // Employee's progress description
        ]);

        // Find the task assigned to this user
        $task = TaskmanagementModel::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$task) {
            return response()->json([
                'status'  => false,
                'message' => 'Task not found or not assigned to you.'
            ], 404);
        }

        // Update status and store the employee's update in the description field
        $task->update([
            'status'      => $request->status,
            'description' => $request->description
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Task updated successfully with description.',
            'data'    => $task->fresh()
        ]);
    }

    // ============================================================
    // ESSENTIAL DOCUMENTS SHORTCUT
    // ============================================================

    public function myEssentialDocuments()
    {
        return $this->myDocuments();
    }

  

    /**
     * GET /api/my/documents
     * Fetch all uploaded documents for the authenticated user
     */
    public function myDocuments()
    {
        try {
            $user = auth()->user();
            $documentRecord = EmployeeDocumentModal::where('user_id', $user->id)->first();

            if (!$documentRecord) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No documents found for this user.',
                    'data'    => null
                ], 404);
            }

            // Map document fields to full URLs
            $fields = [
                'aadhar_card', 'pan_card', 'bank_proof', 'passport_photo', 
                'educational_documents', 'offer_letter', 'salary_slip_3_months', 
                'experience_letter', 'relieving_letter', 'nda_agreement_mou'
            ];

            $documents = [];
            foreach ($fields as $field) {
                $documents[$field] = $this->resolver->secureFileUrl($documentRecord->{$field});
            }

            return response()->json([
                'status'  => true,
                'message' => 'Your documents fetched successfully.',
                'data'    => [
                    'user_id'       => $user->id,
                    'document_type' => $documentRecord->document_type,
                    'status'        => $documentRecord->status,
                    'documents'     => $documents
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error fetching documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🟢 POST API: Upload Employee Documents
     * Endpoint: /api/my/documents
     * Handles professional document uploads with custom naming and public storage
     */
       public function uploadMyDocument(Request $request)
    {
        try {
            // 1. Strict Validation
            $rules = [
                'employee_id'           => 'required|exists:employees,id',
                'document_type_id'      => 'nullable|exists:document_types,id',
                
                // Mandatory Documents (Required for 100% functionality)
                'aadhar_card'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'pan_card'              => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'bank_proof'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'passport_photo'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'educational_documents' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                
                // Optional/Experienced Documents
                'offer_letter'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'salary_slip_3_months'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'experience_letter'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'relieving_letter'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'nda_agreement_mou'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ];

            $request->validate($rules);

            // 2. Fetch Employee & User context
            $employee = Employee::with('user')->findOrFail($request->employee_id);
            $user = $employee->user;

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Critical Error: No user account linked to this employee profile.'
                ], 404);
            }

            // 3. Optimized Upload Helper (Professional Storage)
            $uploadFile = function ($key) use ($request, $employee) {
                if ($request->hasFile($key)) {
                    $file = $request->file($key);
                    $fileName = 'doc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $map = [
                        'aadhar_card' => $this->paths->employeeIdentity($employee->id, 'aadhaar'),
                        'pan_card' => $this->paths->employeeIdentity($employee->id, 'pan'),
                        'bank_proof' => $this->paths->employeeBanking($employee->id, 'bank-proof'),
                        'passport_photo' => $this->paths->employeeIdentity($employee->id, 'passport'),
                        'educational_documents' => $this->paths->employeeEducation($employee->id, 'documents'),
                        'offer_letter' => $this->paths->employeeExperience($employee->id, 'offer-letter'),
                        'salary_slip_3_months' => $this->paths->employeeExperience($employee->id, 'salary-slips'),
                        'experience_letter' => $this->paths->employeeExperience($employee->id, 'experience-letter'),
                        'relieving_letter' => $this->paths->employeeExperience($employee->id, 'relieving-letter'),
                        'nda_agreement_mou' => $this->paths->employeeOnboarding($employee->id, 'nda'),
                    ];
                    $folder = $map[$key] ?? $this->paths->employeeEducation($employee->id, 'documents');
                    return $file->storeAs($folder, $fileName, 'private');
                }
                return null;
            };

            // 4. Atomic Database Transaction for Data Integrity
            return DB::transaction(function () use ($request, $user, $employee, $uploadFile) {
                
                // Identify document type name if ID provided
                $docTypeName = 'Onboarding Documents';
                if ($request->filled('document_type_id')) {
                    $docType = \App\Models\HRMS\Document\DocumentTypeM::find($request->document_type_id);
                    if ($docType) $docTypeName = $docType->name;
                }

                // Update existing record or create new one for this user
                $documentRecord = EmployeeDocumentModal::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'document_type_id'      => $request->document_type_id,
                        'document_type'         => $docTypeName,
                        
                        // Mapping File Paths
                        'aadhar_card'           => $uploadFile('aadhar_card'),
                        'pan_card'              => $uploadFile('pan_card'),
                        'bank_proof'            => $uploadFile('bank_proof'),
                        'passport_photo'        => $uploadFile('passport_photo'),
                        'educational_documents' => $uploadFile('educational_documents'),
                        
                        'offer_letter'          => $uploadFile('offer_letter'),
                        'salary_slip_3_months'  => $uploadFile('salary_slip_3_months'),
                        'experience_letter'     => $uploadFile('experience_letter'),
                        'relieving_letter'      => $uploadFile('relieving_letter'),
                        'nda_agreement_mou'     => $uploadFile('nda_agreement_mou'),

                        'status'                => 'pending', 
                        'uploaded_by'           => auth()->id() ?? $user->id,
                    ]
                );

                return response()->json([
                    'status'  => true,
                    'message' => 'Professional documents uploaded successfully. Pending HR verification.',
                    'data'    => [
                        'employee_id'   => $employee->id,
                        'employee_name' => $employee->name,
                        'documents'     => [
                            'aadhar_card'           => $this->resolver->secureFileUrl($documentRecord->aadhar_card),
                            'pan_card'              => $this->resolver->secureFileUrl($documentRecord->pan_card),
                            'bank_proof'            => $this->resolver->secureFileUrl($documentRecord->bank_proof),
                            'passport_photo'        => $this->resolver->secureFileUrl($documentRecord->passport_photo),
                            'educational_documents' => $this->resolver->secureFileUrl($documentRecord->educational_documents),
                            'offer_letter'          => $this->resolver->secureFileUrl($documentRecord->offer_letter),
                        ]
                    ]
                ], 200);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation Failed',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Server Error during file upload: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // NOTICE / ANNOUNCEMENT MODULE (simplified)
    // ============================================================

    public function createNotice(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'message'     => 'required|string',
            'category'    => 'required|in:urgent,general,policy,event',
            'visibility'  => 'required|string',
            'expiry_date' => 'nullable|date',
        ]);

        return response()->json([
            'message' => 'Create notice stub – implement with Notice model',
        ], 201);
    }

    public function updateNotice($id, Request $request)
    {
        return response()->json([
            'message' => 'Update notice stub – implement with Notice model',
        ]);
    }

    public function deleteNotice($id)
    {
        return response()->json([
            'message' => 'Delete notice stub – implement with Notice model',
        ]);
    }

    public function listActiveNotices()
    {
        return response()->json([
            'message' => 'List active notices stub – implement with Notice model and filters',
        ]);
    }

    public function listArchivedNotices()
    {
        return response()->json([
            'message' => 'List archived notices stub – implement with Notice model',
        ]);
    }

    public function viewNotice($id)
    {
        return response()->json([
            'message' => 'View notice stub – implement with Notice model',
        ]);
    }
}
