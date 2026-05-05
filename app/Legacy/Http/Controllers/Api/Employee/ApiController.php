<?php

namespace App\Legacy\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\Access;
use App\Models\Attendance;
use App\Models\Claim;
use App\Models\Department;
use App\Models\EmployeeDocument;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveRequest;
use App\Models\EmployeeModel;
use App\Models\EmployeeProfile;
use App\Models\FnF;
use App\Models\Holiday;
use App\Models\LeaveAllocation;
use App\Models\LeaveApplication;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Notification;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\Position;
use App\Models\SalaryStructure;
use App\Models\ProjectManagement\TaskmanagementModel;
use App\Models\User;
use App\Services\Shared\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    private function currentEmployee()
    {
        return EmployeeModel::with([
            'user',
            'department',
            'position',
            'systemRole',
            'reportingManager',
            'profile',
            'documents',
        ])->where('user_id', auth()->id())->first();
    }

    private function profileImageUrl(?EmployeeProfile $profile): ?string
    {
        if (!$profile || empty($profile->profile_image)) {
            return null;
        }

        return asset($profile->profile_image);
    }

    private function resumeUrl(?EmployeeProfile $profile): ?string
    {
        if (!$profile || empty($profile->resume_file)) {
            return null;
        }

        return asset($profile->resume_file);
    }

    private function documentUrl(?string $path): ?string
    {
        return $path ? asset($path) : null;
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

        $user = Auth::user()->load('role');
        $token = $user->createToken('api-token')->plainTextToken;

        $employee = EmployeeModel::with([
            'department',
            'position',
            'systemRole',
            'reportingManager',
            'profile',
        ])->where('user_id', $user->id)->first();

        return response()->json([
            'message' => 'Login Success',
            'token'   => $token,
            'user'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'is_active'  => $user->is_active,
                'role'       => $user->role ? $user->role->name : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'employee' => $employee,
            'profile'  => $employee ? $employee->profile : null,
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
            'clock_in_time' => 'nullable|string'
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
    public function getProfile()
    {
        $user = auth()->user();

        $employee = EmployeeModel::with([
            'department',
            'position',
            'systemRole',
            'reportingManager',
            'profile',
            'documents'
        ])->where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        $profile = $employee->profile;
        $today = Carbon::now();

        $holidays = Holiday::whereDate('date', $today->format('Y-m-d'))->pluck('name');
        $todayStatus = [
            'is_holiday' => $holidays->isNotEmpty(),
            'holidays'   => $holidays,
            'festivals'  => $holidays,
            'birthdays'  => [],
            'events'     => [],
        ];

        return response()->json([
            'status' => true,
            'data' => [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
                'employee' => [
                    'id'                            => $employee->id,
                    'user_id'                       => $employee->user_id,
                    'employee_code'                 => $employee->employee_code,
                    'system_role_id'                => $employee->system_role_id,
                    'department_id'                 => $employee->department_id,
                    'designation_id'                => $employee->designation_id,
                    'reporting_manager_employee_id' => $employee->reporting_manager_employee_id,
                    'employment_type'               => $employee->employment_type,
                    'work_mode'                     => $employee->work_mode,
                    'joining_date'                  => $employee->joining_date,
                    'relieving_date'                => $employee->relieving_date,
                    'employment_status'             => $employee->employment_status,
                    'probation_months'              => $employee->probation_months,
                    'probation_start_date'          => $employee->probation_start_date,
                    'probation_end_date'            => $employee->probation_end_date,
                    'probation_status'              => $employee->probation_status,
                    'internship_start_date'         => $employee->internship_start_date,
                    'internship_end_date'           => $employee->internship_end_date,
                    'internship_extended_to'        => $employee->internship_extended_to,
                    'is_paid_intern'                => $employee->is_paid_intern,
                    'actual_salary'                 => $employee->actual_salary,
                    'is_active'                     => $employee->is_active,
                    'is_permanent'                  => $employee->is_permanent,
                    'department'                    => $employee->department,
                    'position'                      => $employee->position,
                    'system_role'                   => $employee->systemRole,
                    'reporting_manager'             => $employee->reportingManager,
                ],
                'profile' => $profile ? [
                    'id'                    => $profile->id,
                    'employee_id'           => $profile->employee_id,
                    'profile_image'         => $this->profileImageUrl($profile),
                    'date_of_birth'         => $profile->date_of_birth,
                    'gender'                => $profile->gender,
                    'address'               => $profile->address,
                    'highest_qualification' => $profile->highest_qualification,
                    'cgpa_percentage'       => $profile->cgpa_percentage,
                    'total_experience'      => $profile->total_experience,
                    'resume_file'           => $this->resumeUrl($profile),
                    'bank_account_no'       => $profile->bank_account_no,
                    'bank_account_type'     => $profile->bank_account_type,
                    'bank_holder_name'      => $profile->bank_holder_name,
                    'ifsc_code'             => $profile->ifsc_code,
                    'bank_branch'           => $profile->bank_branch,
                    'is_profile_completed'  => $profile->is_profile_completed,
                    'profile_completed_at'  => $profile->profile_completed_at,
                ] : (object)[],
                'documents' => $employee->documents->map(function ($doc) {
                    return [
                        'id'                  => $doc->id,
                        'employee_id'         => $doc->employee_id,
                        'category_id'         => $doc->category_id,
                        'title'               => $doc->title,
                        'file_path'           => $this->documentUrl($doc->file_path),
                        'verification_status' => $doc->verification_status,
                        'verified_by_user_id' => $doc->verified_by_user_id,
                        'uploaded_at'         => $doc->uploaded_at,
                    ];
                })->values(),
                'today_status' => $todayStatus,
            ]
        ]);
    }

    // ------------------------------------------------
    // 6. UPDATE PROFILE
    // ------------------------------------------------
    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();
            $employee = EmployeeModel::where('user_id', $user->id)->first();

            if (!$employee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee profile not found.'
                ], 404);
            }

            $profile = EmployeeProfile::firstOrCreate([
                'employee_id' => $employee->id,
            ]);

            $validator = Validator::make($request->all(), [
                'name'                  => 'nullable|string|max:255',
                'email'                 => 'nullable|email|max:255|unique:users,email,' . $user->id,
                'profile_image'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'date_of_birth'         => 'nullable|date',
                'gender'                => 'nullable|in:male,female,other',
                'address'               => 'nullable|string|max:1000',
                'highest_qualification' => 'nullable|string|max:255',
                'cgpa_percentage'       => 'nullable|string|max:50',
                'total_experience'      => 'nullable|string|max:100',
                'resume_file'           => 'nullable|mimes:pdf,doc,docx|max:5120',
                'bank_account_no'       => 'nullable|string|max:100',
                'bank_account_type'     => 'nullable|string|max:100',
                'bank_holder_name'      => 'nullable|string|max:150',
                'ifsc_code'             => 'nullable|string|max:50',
                'bank_branch'           => 'nullable|string|max:150',
                'is_profile_completed'  => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $userChanges = [];
            if ($request->filled('name')) {
                $userChanges['name'] = $request->name;
            }
            if ($request->filled('email')) {
                $userChanges['email'] = $request->email;
            }
            if (!empty($userChanges)) {
                $user->update($userChanges);
            }

            $profileImagePath = $profile->profile_image;
            $resumeFilePath = $profile->resume_file;

            if ($request->hasFile('profile_image')) {
                if (!empty($profile->profile_image) && file_exists(public_path($profile->profile_image))) {
                    @unlink(public_path($profile->profile_image));
                }

                $image = $request->file('profile_image');
                $imageName = 'profile_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destination = public_path('uploads/employee_profiles');

                if (!file_exists($destination)) {
                    mkdir($destination, 0777, true);
                }

                $image->move($destination, $imageName);
                $profileImagePath = 'uploads/employee_profiles/' . $imageName;
            }

            if ($request->hasFile('resume_file')) {
                if (!empty($profile->resume_file) && file_exists(public_path($profile->resume_file))) {
                    @unlink(public_path($profile->resume_file));
                }

                $resume = $request->file('resume_file');
                $resumeName = 'resume_' . time() . '_' . uniqid() . '.' . $resume->getClientOriginalExtension();
                $destination = public_path('uploads/employee_resumes');

                if (!file_exists($destination)) {
                    mkdir($destination, 0777, true);
                }

                $resume->move($destination, $resumeName);
                $resumeFilePath = 'uploads/employee_resumes/' . $resumeName;
            }

            $isCompleted = $request->has('is_profile_completed')
                ? $request->is_profile_completed
                : $profile->is_profile_completed;

            $profile->update([
                'profile_image'         => $profileImagePath,
                'date_of_birth'         => $request->date_of_birth ?? $profile->date_of_birth,
                'gender'                => $request->gender ?? $profile->gender,
                'address'               => $request->address ?? $profile->address,
                'highest_qualification' => $request->highest_qualification ?? $profile->highest_qualification,
                'cgpa_percentage'       => $request->cgpa_percentage ?? $profile->cgpa_percentage,
                'total_experience'      => $request->total_experience ?? $profile->total_experience,
                'resume_file'           => $resumeFilePath,
                'bank_account_no'       => $request->bank_account_no ?? $profile->bank_account_no,
                'bank_account_type'     => $request->bank_account_type ?? $profile->bank_account_type,
                'bank_holder_name'      => $request->bank_holder_name ?? $profile->bank_holder_name,
                'ifsc_code'             => $request->ifsc_code ?? $profile->ifsc_code,
                'bank_branch'           => $request->bank_branch ?? $profile->bank_branch,
                'is_profile_completed'  => $isCompleted,
                'profile_completed_at'  => $isCompleted ? ($profile->profile_completed_at ?? now()) : null,
            ]);

            DB::commit();

            $employee->load([
                'department',
                'position',
                'systemRole',
                'reportingManager',
                'profile',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Profile Updated Successfully.',
                'user'    => [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'is_active' => $user->is_active,
                    'role'      => $user->role?->name,
                ],
                'employee' => $employee,
                'profile'  => $employee->profile,
            ], 200);
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
            'data'    => $attendance->items(),
            'pagination' => [
                'total'         => $attendance->total(),
                'per_page'      => $attendance->perPage(),
                'current_page'  => $attendance->currentPage(),
                'last_page'     => $attendance->lastPage(),
                'next_page_url' => $attendance->nextPageUrl(),
                'prev_page_url' => $attendance->previousPageUrl(),
            ]
        ]);
    }

    private function calculateLeaveDays(Carbon $start, Carbon $end): array
    {
        $holidays = Holiday::whereBetween('date', [
            $start->format('Y-m-d'), $end->format('Y-m-d'),
        ])->pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();

        $extendedEnd = $end->copy();
        if ($extendedEnd->dayOfWeek === Carbon::SATURDAY && $start->lte($extendedEnd->copy()->subDay())) {
            $extendedEnd->addDay();
        } elseif ($extendedEnd->dayOfWeek === Carbon::FRIDAY && $start->lte($extendedEnd->copy()->subDay())) {
            $extendedEnd->addDays(2);
        }

        $days = 0;
        $temp = $start->copy();
        while ($temp->lte($extendedEnd)) {
            if ($temp->dayOfWeek !== Carbon::SUNDAY && !in_array($temp->format('Y-m-d'), $holidays)) {
                $days++;
            }
            $temp->addDay();
        }

        return ['days' => $days, 'effective_end' => $extendedEnd->format('Y-m-d')];
    }

    private function getMonthlyLeaveUsed(int $employeeId, int $month, int $year): float
    {
        return LeaveApplication::where('employee_id', $employeeId)
            ->whereIn('status', ['approved', 'pending'])
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->whereIn('leave_type', ['PL', 'SL'])
            ->sum('total_days');
    }

    public function createLeaveType(Request $request)
    {
        try {
            $request->validate([
                'name'         => 'required|string|max:100|unique:leave_types,name',
                'yearly_quota' => 'required|numeric|min:0',
                'accrual_type' => 'nullable|in:monthly,yearly,prorated',
            ]);

            $type = LeaveType::create([
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

    public function listLeaveTypes()
    {
        return response()->json(['status' => true, 'data' => LeaveType::orderBy('name')->get()], 200);
    }

    public function getLeaveBalance()
    {
        try {
            $employee = $this->currentEmployee();
            if (!$employee) {
                return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $now   = Carbon::now();
            $year  = $now->year;
            $month = $now->month;

            $alloc   = LeaveAllocation::where('employee_id', $employee->id)->where('year', $year)->first();
            $totalPl = (float) ($alloc->total_pl ?? 18);
            $totalSl = (float) ($alloc->total_sl ?? 7);

            $usedPl = (float) LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type', 'PL')->where('status', 'approved')
                ->whereYear('start_date', $year)->sum('total_days');

            $usedSl = (float) LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type', 'SL')->where('status', 'approved')
                ->whereYear('start_date', $year)->sum('total_days');

            $lwpDirect = (float) LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type', 'LWP')->where('status', 'approved')
                ->whereYear('start_date', $year)->sum('total_days');

            $lwpEmbedded = (float) LeaveApplication::where('employee_id', $employee->id)
                ->whereIn('leave_type', ['PL', 'SL'])->where('status', 'approved')
                ->whereYear('start_date', $year)->sum('lwp_days');

            $totalLwp = $lwpDirect + $lwpEmbedded;

            $remPl = max(0, $totalPl - $usedPl);
            $remSl = max(0, $totalSl - $usedSl);

            $pendingPl = (float) LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type', 'PL')->where('status', 'pending')
                ->whereYear('start_date', $year)->sum('total_days');

            $pendingSl = (float) LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type', 'SL')->where('status', 'pending')
                ->whereYear('start_date', $year)->sum('total_days');

            $monthlyPl = (float) LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type', 'PL')->whereIn('status', ['approved', 'pending'])
                ->whereYear('start_date', $year)->whereMonth('start_date', $month)->sum('total_days');

            $monthlySl = (float) LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type', 'SL')->whereIn('status', ['approved', 'pending'])
                ->whereYear('start_date', $year)->whereMonth('start_date', $month)->sum('total_days');

            $monthlyTotal  = $monthlyPl + $monthlySl;
            $monthlyLimit  = 2;
            $monthlyRemain = max(0, $monthlyLimit - $monthlyTotal);

            $isRestricted    = in_array($employee->employment_type, ['intern']) || ($employee->probation_status && $employee->probation_status !== 'confirmed');
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
                    'overview' => [
                        'employee_name'   => $employee->user->name ?? null,
                        'employee_type'   => $employee->employment_type ?? 'full_time',
                        'year'            => $year,
                        'total_leave'     => $totalPl + $totalSl,
                        'used_leave'      => $usedPl + $usedSl,
                        'remaining_leave' => $remPl + $remSl,
                        'lwp_days'        => $totalLwp,
                        'pending_leave'   => $pendingPl + $pendingSl,
                    ],
                    'paid_leave' => [
                        'total'      => $totalPl,
                        'used'       => $usedPl,
                        'remaining'  => $remPl,
                        'pending'    => $pendingPl,
                        'usable_now' => $usablePl,
                    ],
                    'sick_leave' => [
                        'total'      => $totalSl,
                        'used'       => $usedSl,
                        'remaining'  => $remSl,
                        'pending'    => $pendingSl,
                        'usable_now' => $usableSl,
                    ],
                    'monthly_usage' => [
                        'month'                 => $now->format('F Y'),
                        'monthly_limit'         => $monthlyLimit,
                        'paid_leave_used'       => $monthlyPl,
                        'sick_leave_used'       => $monthlySl,
                        'total_used_this_month' => $monthlyTotal,
                        'remaining_this_month'  => $monthlyRemain,
                    ],
                    'policy' => [
                        'is_restricted_employee' => $isRestricted,
                        'year_end_restriction'   => $yearEndRestrict,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function applyLeave(Request $request)
    {
        try {
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
            ]);

            $employee = $this->currentEmployee();
            if (!$employee) {
                return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $start = Carbon::parse($request->start_date)->startOfDay();
            $end   = Carbon::parse($request->end_date)->startOfDay();
            $year  = $start->year;
            $month = $start->month;

            $calc      = $this->calculateLeaveDays($start, $end);
            $totalDays = $calc['days'];

            if ($totalDays <= 0) {
                return response()->json(['status' => false, 'message' => 'No working days in the selected range.'], 422);
            }

            if ($request->leave_type === 'SL' && $totalDays > 2 && !$request->hasFile('attachment')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Medical certificate is mandatory for Sick Leave > 2 days.',
                ], 422);
            }

            $attachmentPath = $request->hasFile('attachment')
                ? $request->file('attachment')->store('leave_attachments', 'public')
                : null;

            $allocation     = LeaveAllocation::where('employee_id', $employee->id)->where('year', $year)->first();
            $finalLeaveType = $request->leave_type;
            $lwpDays        = 0;
            $warnings       = [];

            if (!$allocation) {
                $finalLeaveType = 'LWP';
                $lwpDays        = $totalDays;
                $warnings[]     = "No allocation for {$year}. Applied entirely as LWP.";
            } else {
                $monthUsed = $this->getMonthlyLeaveUsed($employee->id, $month, $year);
                $remaining = max(0, 2 - $monthUsed);

                if ($remaining < $totalDays) {
                    $over = $totalDays - $remaining;
                    $lwpDays += $over;
                    $warnings[] = "Monthly cap exceeded. {$over} day(s) auto-LWP.";
                }
            }

            $application = LeaveApplication::create([
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

            return response()->json([
                'status'   => true,
                'message'  => 'Leave submitted successfully.',
                'warnings' => $warnings,
                'data'     => $application,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['status' => false, 'message' => 'Validation Error', 'errors' => $ve->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function myLeaves()
    {
        try {
            $employee = $this->currentEmployee();
            if (!$employee) {
                return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $year  = Carbon::now()->year;
            $alloc = LeaveAllocation::where('employee_id', $employee->id)->where('year', $year)->first();

            $balance = [
                'year'     => $year,
                'total_pl' => $alloc->total_pl ?? 0,
                'used_pl'  => $alloc->used_pl ?? 0,
                'rem_pl'   => max(0, ($alloc->total_pl ?? 0) - ($alloc->used_pl ?? 0)),
                'total_sl' => $alloc->total_sl ?? 0,
                'used_sl'  => $alloc->used_sl ?? 0,
                'rem_sl'   => max(0, ($alloc->total_sl ?? 0) - ($alloc->used_sl ?? 0)),
                'lwp_days' => $alloc->lwp_days ?? 0,
            ];

            $history = LeaveApplication::where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['status' => true, 'balance' => $balance, 'history' => $history], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cancelLeaveRequest($id)
    {
        try {
            $employee = $this->currentEmployee();
            if (!$employee) {
                return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $application = LeaveApplication::where('id', $id)->where('employee_id', $employee->id)->first();
            if (!$application) {
                return response()->json(['status' => false, 'message' => 'Leave application not found.'], 404);
            }

            if ($application->status !== 'pending') {
                return response()->json(['status' => false, 'message' => "Cannot cancel a '{$application->status}' leave."], 400);
            }

            $application->update(['status' => 'cancelled']);

            return response()->json(['status' => true, 'message' => 'Leave request cancelled.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approveLeave($id)
    {
        try {
            $application = LeaveApplication::find($id);
            if (!$application) {
                return response()->json(['status' => false, 'message' => 'Leave application not found.'], 404);
            }

            if ($application->status !== 'pending') {
                return response()->json(['status' => false, 'message' => "Leave is already '{$application->status}'."], 400);
            }

            $application->update([
                'status'      => 'approved',
                'approved_by' => auth()->id()
            ]);

            return response()->json(['status' => true, 'message' => 'Leave approved.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function rejectLeave(Request $request, $id)
    {
        try {
            $application = LeaveApplication::find($id);
            if (!$application) {
                return response()->json(['status' => false, 'message' => 'Leave application not found.'], 404);
            }

            if ($application->status !== 'pending') {
                return response()->json(['status' => false, 'message' => "Leave is already '{$application->status}'."], 400);
            }

            $application->update([
                'status'       => 'rejected',
                'admin_remark' => $request->remark ?? 'Rejected by HR/Admin.',
                'approved_by'  => auth()->id()
            ]);

            return response()->json(['status' => true, 'message' => 'Leave rejected.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function myLeaveCalendar(Request $request)
    {
        try {
            $employee = $this->currentEmployee();
            if (!$employee) {
                return response()->json(['status' => false, 'message' => 'Employee profile not found.'], 404);
            }

            $month = (int)($request->month ?? Carbon::now()->month);
            $year  = (int)($request->year ?? Carbon::now()->year);

            $leaves = LeaveApplication::where('employee_id', $employee->id)
                ->whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->whereIn('status', ['pending', 'approved'])
                ->get();

            $holidays = Holiday::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get(['name', 'date']);

            return response()->json([
                'status'   => true,
                'period'   => Carbon::createFromDate($year, $month, 1)->format('F Y'),
                'leaves'   => $leaves,
                'holidays' => $holidays
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function employeeLeaveCalendar(Request $request, $id)
    {
        try {
            $employee = EmployeeModel::find($id);
            if (!$employee) {
                return response()->json(['status' => false, 'message' => 'Employee not found.'], 404);
            }

            $month = (int)($request->month ?? Carbon::now()->month);
            $year  = (int)($request->year ?? Carbon::now()->year);

            $leaves = LeaveApplication::where('employee_id', $employee->id)
                ->whereYear('start_date', $year)
                ->whereMonth('start_date', $month)
                ->whereIn('status', ['pending', 'approved'])
                ->get();

            $holidays = Holiday::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get(['name', 'date']);

            return response()->json([
                'status'        => true,
                'employee_name' => $employee->user->name ?? 'N/A',
                'period'        => Carbon::createFromDate($year, $month, 1)->format('F Y'),
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
    public function createEmployee(Request $request)
    {
        $request->validate([
            'name'                          => 'required|string|max:255',
            'email'                         => 'required|email|unique:users,email',
            'employee_code'                 => 'required|string|max:50|unique:employees_new,employee_code',
            'department_id'                 => 'nullable|exists:departments,id',
            'designation_id'                => 'nullable|exists:positions,id',
            'reporting_manager_employee_id' => 'nullable|exists:employees_new,id',
            'employment_type'               => 'required|in:full_time,intern,freelancer,contract',
            'work_mode'                     => 'required|in:wfo,wfh',
            'joining_date'                  => 'nullable|date',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => bcrypt('Password@123'),
            ]);

            $employee = EmployeeModel::create([
                'user_id'                        => $user->id,
                'employee_code'                  => $request->employee_code,
                'department_id'                  => $request->department_id,
                'designation_id'                 => $request->designation_id,
                'reporting_manager_employee_id'  => $request->reporting_manager_employee_id,
                'employment_type'                => $request->employment_type,
                'work_mode'                      => $request->work_mode,
                'joining_date'                   => $request->joining_date,
                'employment_status'              => 'active',
                'probation_months'               => 3,
                'probation_status'               => 'pending',
                'is_active'                      => 1,
                'created_by'                     => auth()->id(),
                'updated_by'                     => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message'  => 'Employee created successfully',
                'user'     => $user,
                'employee' => $employee
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

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

    public function getSalaryStructure()
    {
        $employee = $this->currentEmployee();

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $structure = SalaryStructure::find($employee->salary_structure_id);

        if (!$structure) {
            return response()->json(['message' => 'Salary structure not assigned'], 404);
        }

        $components = is_string($structure->components) ? json_decode($structure->components, true) : $structure->components;

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
                'basic_pay' => ['basic_salary' => $basicSalary],
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

    public function getMonthlySalary(Request $request)
    {
        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year'  => 'nullable|integer'
        ]);

        $employee = $this->currentEmployee();
        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

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
                'total_allowances' => $payroll->gross_salary - ($payroll->basic_salary ?? 0),
                'deductions' => $payroll->total_deductions,
                'net_salary' => $payroll->net_salary
            ]
        ]);
    }

    public function submitClaim(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'amount'   => 'required|numeric|min:1',
            'reason'   => 'required|string',
            'file'     => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048'
        ]);

        $employee = $this->currentEmployee();
        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('claims', 'public');
        }

        $claim = Claim::create([
            'employee_id' => $employee->id,
            'category'    => $request->category,
            'amount'      => $request->amount,
            'file'        => $filePath,
            'status'      => 'pending',
            'reason'      => $request->reason,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Claim submitted successfully',
            'data'    => $claim
        ], 201);
    }

    public function getClaimsHistory()
    {
        $employee = $this->currentEmployee();
        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $claims = Claim::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $claims
        ]);
    }

    public function getPayslip(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer'
        ]);

        $employee = $this->currentEmployee();
        if (!$employee) {
            return response()->json(['status' => false, 'message' => 'Employee record not found.'], 404);
        }

        $payroll = Payroll::where('employee_id', $employee->id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if (!$payroll) {
            return response()->json(['status' => false, 'message' => 'Payslip data not found.'], 404);
        }

        $structure = null;
        if (!empty($employee->salary_structure_id)) {
            $structure = SalaryStructure::find($employee->salary_structure_id);
        }

        $components = [];
        if ($structure && !empty($structure->components)) {
            $components = is_string($structure->components) ? json_decode($structure->components, true) : $structure->components;
        }

        return response()->json([
            'status' => true,
            'message' => 'Payslip fetched successfully.',
            'data' => [
                'employee_details' => [
                    'employee_name' => $employee->user->name ?? 'N/A',
                    'employee_id'   => $employee->employee_code ?? 'N/A',
                    'designation'   => $employee->position->name ?? 'N/A',
                    'department'    => $employee->department->name ?? 'N/A',
                    'joining_date'  => $employee->joining_date ?? 'N/A',
                ],
                'payslip_info' => [
                    'month_year'         => date('F Y', mktime(0, 0, 0, $request->month, 1, $request->year)),
                    'paid_days'          => $payroll->paid_days ?? 0,
                    'total_working_days' => $payroll->working_days ?? 0,
                    'status'             => $payroll->status ?? 'Processed',
                ],
                'earnings' => [
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
                    'gross_salary'      => $payroll->gross_salary ?? 0,
                    'total_deductions'  => $payroll->total_deductions ?? 0,
                    'net_salary'        => $payroll->net_salary ?? 0,
                ]
            ]
        ]);
    }

    public function getPayslipHistory()
    {
        $employee = $this->currentEmployee();
        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $payslips = Payslip::where('employee_id', $employee->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $payslips->map(function ($item) {
                return [
                    'id'       => $item->id,
                    'period'   => date('F Y', mktime(0, 0, 0, $item->month, 1, $item->year)),
                    'file_url' => asset('storage/' . $item->file_path),
                    'month'    => $item->month,
                    'year'     => $item->year
                ];
            })
        ]);
    }

    public function listEmployees(Request $request)
    {
        $employees = EmployeeModel::with(['user', 'profile', 'department', 'position'])
            ->when($request->employment_status, fn($q) => $q->where('employment_status', $request->employment_status))
            ->orderBy('id', 'DESC')
            ->paginate(20);

        return response()->json($employees);
    }

    public function showEmployee($id)
    {
        $employee = EmployeeModel::with(['user', 'profile', 'department', 'position', 'documents'])->find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json($employee);
    }

    public function updateEmployee(Request $request, $id)
    {
        $employee = EmployeeModel::with('user')->find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $request->validate([
            'name'                          => 'nullable|string|max:255',
            'email'                         => 'nullable|email|unique:users,email,' . $employee->user_id,
            'department_id'                 => 'nullable|exists:departments,id',
            'designation_id'                => 'nullable|exists:positions,id',
            'reporting_manager_employee_id' => 'nullable|exists:employees_new,id',
            'joining_date'                  => 'nullable|date',
            'work_mode'                     => 'nullable|in:wfo,wfh',
            'employment_type'               => 'nullable|in:full_time,intern,freelancer,contract',
            'employment_status'             => 'nullable|in:active,resigned,terminated',
        ]);

        if ($request->filled('name')) {
            $employee->user->name = $request->name;
        }
        if ($request->filled('email')) {
            $employee->user->email = $request->email;
        }
        $employee->user->save();

        $employee->update($request->only([
            'department_id',
            'designation_id',
            'reporting_manager_employee_id',
            'joining_date',
            'work_mode',
            'employment_type',
            'employment_status',
        ]));

        return response()->json([
            'message'  => 'Employee updated successfully',
            'employee' => $employee->fresh(['user', 'profile', 'department', 'position']),
        ]);
    }

    public function archiveEmployee($id)
    {
        $employee = EmployeeModel::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $employee->employment_status = 'terminated';
        $employee->is_active = 0;
        $employee->save();

        return response()->json(['message' => 'Employee archived successfully']);
    }

    public function getJobDetails($id)
    {
        $employee = EmployeeModel::with(['department', 'position', 'reportingManager.user', 'systemRole'])->find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json($employee);
    }

    public function updateJobDetails(Request $request, $id)
    {
        $employee = EmployeeModel::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $request->validate([
            'department_id'                 => 'required|exists:departments,id',
            'designation_id'                => 'required|exists:positions,id',
            'reporting_manager_employee_id' => 'nullable|exists:employees_new,id|different:id',
        ]);

        if ($request->reporting_manager_employee_id && $request->reporting_manager_employee_id == $employee->id) {
            return response()->json(['message' => 'Employee cannot report to self'], 422);
        }

        $employee->update([
            'department_id'                 => $request->department_id,
            'designation_id'                => $request->designation_id,
            'reporting_manager_employee_id' => $request->reporting_manager_employee_id,
        ]);

        return response()->json([
            'message' => 'Job details updated successfully',
            'data'    => $employee->fresh(['department', 'position', 'reportingManager']),
        ]);
    }

    public function getEmploymentStatus($id)
    {
        $employee = EmployeeModel::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json([
            'employment_status'   => $employee->employment_status,
            'probation_end_date'  => $employee->probation_end_date,
            'joining_date'        => $employee->joining_date,
            'relieving_date'      => $employee->relieving_date,
            'employment_type'     => $employee->employment_type,
            'probation_status'    => $employee->probation_status,
        ]);
    }

    public function updateEmploymentStatus(Request $request, $id)
    {
        $employee = EmployeeModel::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $request->validate([
            'employment_status'   => 'required|in:active,resigned,terminated',
            'probation_end_date'  => 'nullable|date',
            'joining_date'        => 'nullable|date',
            'relieving_date'      => 'nullable|date|after_or_equal:joining_date',
            'employment_type'     => 'nullable|in:full_time,intern,freelancer,contract',
            'probation_status'    => 'nullable|in:pending,ongoing,completed,confirmed',
        ]);

        $employee->update([
            'employment_status'  => $request->employment_status,
            'probation_end_date' => $request->probation_end_date,
            'joining_date'       => $request->joining_date,
            'relieving_date'     => $request->relieving_date,
            'employment_type'    => $request->employment_type ?? $employee->employment_type,
            'probation_status'   => $request->probation_status ?? $employee->probation_status,
        ]);

        return response()->json([
            'message' => 'Employment status updated successfully',
            'data'    => $employee,
        ]);
    }

    public function uploadEmployeeDocument(Request $request, $id)
    {
        $employee = EmployeeModel::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:200',
            'category_id' => 'nullable|integer',
            'file'        => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        $file = $request->file('file');
        $fileName = 'doc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $destination = public_path('uploads/employee_docs');

        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        $file->move($destination, $fileName);
        $path = 'uploads/employee_docs/' . $fileName;

        $document = EmployeeDocument::create([
            'employee_id'         => $employee->id,
            'category_id'         => $request->category_id,
            'title'               => $request->title,
            'file_path'           => $path,
            'verification_status' => 'verified',
            'verified_by_user_id' => auth()->id(),
            'uploaded_at'         => now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Document uploaded successfully',
            'data'    => $document
        ], 200);
    }

    public function listEmployeeDocuments($id)
    {
        $employee = EmployeeModel::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $docs = EmployeeDocument::where('employee_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($docs);
    }

    public function verifyEmployeeDocument(Request $request, $id, $docId)
    {
        $request->validate([
            'status' => 'required|in:verified,rejected,pending',
        ]);

        $doc = EmployeeDocument::where('employee_id', $id)->find($docId);
        if (!$doc) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $doc->verification_status = $request->status;
        $doc->verified_by_user_id = auth()->id();
        $doc->save();

        return response()->json([
            'message' => 'Document status updated',
            'data'    => $doc
        ]);
    }

    public function deleteEmployeeDocument($id, $docId)
    {
        $doc = EmployeeDocument::where('employee_id', $id)->find($docId);
        if (!$doc) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        if ($doc->verification_status === 'verified') {
            return response()->json([
                'message' => 'Verified document cannot be deleted'
            ], 403);
        }

        if (!empty($doc->file_path) && file_exists(public_path($doc->file_path))) {
            @unlink(public_path($doc->file_path));
        }

        $doc->delete();

        return response()->json(['message' => 'Document deleted']);
    }

    public function listPolicyDocuments()
    {
        $docs = EmployeeDocument::whereNull('employee_id')
            ->get();

        return response()->json($docs);
    }

    public function uploadPolicyDocument(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('file')->store('policies', 'public');

        $doc = EmployeeDocument::create([
            'employee_id'         => null,
            'category_id'         => null,
            'title'               => $request->title,
            'file_path'           => 'storage/' . $path,
            'verification_status' => 'verified',
            'verified_by_user_id' => auth()->id(),
            'uploaded_at'         => now(),
        ]);

        return response()->json([
            'message' => 'Policy document uploaded',
            'data'    => $doc,
        ], 201);
    }

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
        $request->validate([
            'date'          => 'required|date',
            'type'          => 'required|in:missed_in,missed_out,incorrect',
            'requested_in'  => 'nullable',
            'requested_out' => 'nullable',
            'reason'        => 'required|string',
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
            'user_id' => 'required|integer',
            'date'    => 'required|date',
            'status'  => 'required|in:Present,Absent,Leave,Holiday,WeekOff,HalfDay',
            'reason'  => 'required|string',
        ]);

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $request->user_id, 'date' => $request->date],
            ['status' => $request->status]
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
    public function createTask(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'required|string',
            'due_date'    => 'required|date',
            'user_id'     => 'required|exists:users,id',
        ]);

        $task = TaskmanagementModel::create($request->all());

        return response()->json([
            'status'  => true,
            'message' => 'Task created and assigned successfully',
            'data'    => $task,
        ], 201);
    }

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

    public function taskDetail($id)
    {
        $task = TaskmanagementModel::where('user_id', auth()->id())->find($id);

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

    public function getTaskDetails($id)
    {
        $task = TaskmanagementModel::with('user:id,name')->find($id);

        if (!$task) {
            return response()->json(['status' => false, 'message' => 'Task not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $task]);
    }

    public function updateMyTask(Request $request, $id)
    {
        $request->validate([
            'status'      => 'required|in:pending,progress,completed',
            'description' => 'required|string',
        ]);

        $task = TaskmanagementModel::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$task) {
            return response()->json([
                'status'  => false,
                'message' => 'Task not found or not assigned to you.'
            ], 404);
        }

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

    public function myEssentialDocuments()
    {
        return $this->myDocuments();
    }

    public function myDocuments()
    {
        try {
            $employee = $this->currentEmployee();

            if (!$employee) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Employee not found.',
                    'data'    => null
                ], 404);
            }

            $docs = EmployeeDocument::where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Your documents fetched successfully.',
                'data'    => $docs->map(function ($doc) {
                    return [
                        'id'                  => $doc->id,
                        'employee_id'         => $doc->employee_id,
                        'category_id'         => $doc->category_id,
                        'title'               => $doc->title,
                        'file_path'           => $this->documentUrl($doc->file_path),
                        'verification_status' => $doc->verification_status,
                        'verified_by_user_id' => $doc->verified_by_user_id,
                        'uploaded_at'         => $doc->uploaded_at,
                    ];
                }),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error fetching documents: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadMyDocument(Request $request)
    {
        try {
            $employee = $this->currentEmployee();

            if (!$employee) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Employee not found.'
                ], 404);
            }

            $request->validate([
                'title'       => 'required|string|max:200',
                'category_id' => 'nullable|integer',
                'file'        => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            ]);

            $file = $request->file('file');
            $fileName = 'doc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/employee_docs');

            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }

            $file->move($destination, $fileName);
            $path = 'uploads/employee_docs/' . $fileName;

            $document = EmployeeDocument::create([
                'employee_id'         => $employee->id,
                'category_id'         => $request->category_id,
                'title'               => $request->title,
                'file_path'           => $path,
                'verification_status' => 'pending',
                'verified_by_user_id' => null,
                'uploaded_at'         => now(),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Document uploaded successfully. Pending HR verification.',
                'data'    => [
                    'id'                  => $document->id,
                    'employee_id'         => $document->employee_id,
                    'title'               => $document->title,
                    'file_path'           => $this->documentUrl($document->file_path),
                    'verification_status' => $document->verification_status,
                    'uploaded_at'         => $document->uploaded_at,
                ]
            ], 200);
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
