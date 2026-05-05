<?php

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Services\HRMS\Employee\EmployeeFileS;
use App\Models\HRMS\Leave\HolidayM as Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    private function currentEmployee()
    {
        return EmployeeM::with([
            'user',
            'department',
            'designation',
            'systemRole',
            'reportingManager.user',
            'profile',
            'documents',
            'assetAllocations',
            'salaryHistories',
        ])->where('user_id', auth()->id())->first();
    }

    private function fileUrl(?string $path): ?string
    {
        if (!$path) return null;

        return url('/api/v1/file?path=' . urlencode($path));
    }

    private function requiredProfileFields(EmployeeProfileM $profile): array
    {
        return [
            'date_of_birth'         => $profile->date_of_birth,
            'gender'                => $profile->gender,
            'address'               => $profile->address,
            'highest_qualification' => $profile->highest_qualification,
            'cgpa_percentage'       => $profile->cgpa_percentage,
            'total_experience'      => $profile->total_experience,
            'resume_file'           => $profile->resume_file,
            'bank_account_no'       => $profile->bank_account_no,
            'bank_account_type'     => $profile->bank_account_type,
            'bank_holder_name'      => $profile->bank_holder_name,
            'ifsc_code'             => $profile->ifsc_code,
            'bank_branch'           => $profile->bank_branch,
        ];
    }

    private function missingProfileFields(EmployeeProfileM $profile): array
    {
        $missing = [];

        foreach ($this->requiredProfileFields($profile) as $key => $value) {
            if (is_null($value) || trim((string) $value) === '') {
                $missing[] = $key;
            }
        }

        return $missing;
    }

    private function isProfileCompleted(EmployeeProfileM $profile): bool
    {
        return count($this->missingProfileFields($profile)) === 0;
    }

    private function completionPercentage(EmployeeProfileM $profile): int
    {
        $fields = $this->requiredProfileFields($profile);
        $total = count($fields);

        if ($total === 0) return 0;

        $filled = 0;

        foreach ($fields as $value) {
            if (!is_null($value) && trim((string) $value) !== '') {
                $filled++;
            }
        }

        return (int) floor(($filled / $total) * 100);
    }

    private function todayStatus(): array
    {
        $today = Carbon::now();

        $holidays = Holiday::whereDate('date', $today->format('Y-m-d'))
            ->pluck('name')
            ->values();

        return [
            'is_holiday' => $holidays->isNotEmpty(),
            'holidays'   => $holidays,
            'festivals'  => $holidays,
            'birthdays'  => [],
            'events'     => [],
        ];
    }

    private function buildCompletionStatus(EmployeeM $employee, EmployeeProfileM $profile): array
    {
        $isEmployee = method_exists($employee->user, 'isEmployee')
            ? (bool) $employee->user->isEmployee()
            : true;

        $isCompleted = $this->isProfileCompleted($profile);
        $mustCompleteProfile = $isEmployee ? !$isCompleted : false;
        $attendanceBlocked = $isEmployee ? !$isCompleted : false;

        return [
            'is_profile_completed'   => $isCompleted,
            'must_complete_profile'  => $mustCompleteProfile,
            'completion_percentage'  => $this->completionPercentage($profile),
            'missing_profile_fields' => $this->missingProfileFields($profile),
            'can_punch_attendance'   => !$mustCompleteProfile && !$attendanceBlocked,
            'attendance_blocked'     => $attendanceBlocked,
            'next_route'             => $mustCompleteProfile ? 'profile_completion' : 'dashboard',
        ];
    }

    public function getProfile()
    {
        $user = auth()->user();
        $employee = $this->currentEmployee();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found.',
                'errors'  => null,
                'data'    => null,
            ], 404);
        }

        $profile = $employee->profile;

        if (!$profile) {
            $profile = EmployeeProfileM::create([
                'employee_id' => $employee->id,
            ]);

            $employee->load('profile');
            $profile = $employee->profile;
        }

        $completionStatus = $this->buildCompletionStatus($employee, $profile);

        $profile->update([
            'is_profile_completed' => $completionStatus['is_profile_completed'],
            'profile_completed_at' => $completionStatus['is_profile_completed']
                ? ($profile->profile_completed_at ?? now())
                : null,
        ]);

        $assetSummary = [];
        $salaryHistory = [];

        if (method_exists($employee, 'assetAllocations') && $employee->relationLoaded('assetAllocations')) {
            $assetSummary = $employee->assetAllocations->map(function ($asset) {
                return [
                    'id'         => $asset->id ?? null,
                    'asset_name' => $asset->asset_name ?? $asset->name ?? 'Asset',
                    'asset_code' => $asset->asset_code ?? null,
                    'status'     => $asset->status ?? null,
                ];
            })->values();
        }

        if (method_exists($employee, 'salaryHistories') && $employee->relationLoaded('salaryHistories')) {
            $salaryHistory = $employee->salaryHistories->map(function ($history) {
                return [
                    'id' => $history->id,
                    'stage' => $history->stage,
                    'salary_amount' => $history->salary_amount,
                    'effective_from' => optional($history->effective_from)->toDateString(),
                    'effective_to' => optional($history->effective_to)->toDateString(),
                    'reason' => $history->reason,
                ];
            })->values();
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile fetched successfully.',
            'errors'  => null,
            'data'    => [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,    
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
                    'employee_stage'                => $employee->employee_stage,
                    'work_mode'                     => $employee->work_mode,
                    'work_schedule_type'            => $employee->work_schedule_type,
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
                    'salary_history'                => $salaryHistory,
                    'is_active'                     => $employee->is_active,
                    'is_permanent'                  => $employee->is_permanent,

                    'department' => [
                        'id'   => $employee->department?->id,
                        'name' => $employee->department?->name,
                    ],

                    'designation' => [
                        'id'   => $employee->designation?->id,
                        'name' => $employee->designation?->name,
                    ],

                    'system_role' => [
                        'id'   => $employee->systemRole?->id,
                        'name' => $employee->systemRole?->name,
                    ],

                    'reporting_manager' => [
                        'id'   => $employee->reportingManager?->id,
                        'name' => $employee->reportingManager?->user?->name,
                    ],
                ],

                'profile' => [
                    'id'                    => $profile->id,
                    'employee_id'           => $profile->employee_id,
                    'profile_image'         => $this->fileUrl($profile->profile_image),
                    'date_of_birth'         => $profile->date_of_birth,
                    'gender'                => $profile->gender,
                    'address'               => $profile->address,
                    'highest_qualification' => $profile->highest_qualification,
                    'cgpa_percentage'       => $profile->cgpa_percentage,
                    'total_experience'      => $profile->total_experience,
                    'resume_file'           => $this->fileUrl($profile->resume_file),
                    'bank_account_no'       => $profile->bank_account_no,
                    'bank_account_type'     => $profile->bank_account_type,
                    'bank_holder_name'      => $profile->bank_holder_name,
                    'ifsc_code'             => $profile->ifsc_code,
                    'bank_branch'           => $profile->bank_branch,
                    'is_profile_completed'  => $completionStatus['is_profile_completed'],
                    'profile_completed_at'  => $profile->fresh()->profile_completed_at,
                ],

                'completion_status' => $completionStatus,

                'editable_fields' => [
                    'profile_image',
                    'date_of_birth',
                    'gender',
                    'address',
                    'highest_qualification',
                    'cgpa_percentage',
                    'total_experience',
                    'resume_file',
                    'bank_account_no',
                    'bank_account_type',
                    'bank_holder_name',
                    'ifsc_code',
                    'bank_branch',
                ],

                'readonly_fields' => [
                    'name',
                    'email',
                    'employee_code',
                    'employment_type',
                    'employee_stage',
                    'work_mode',
                    'work_schedule_type',
                    'joining_date',
                    'department',
                    'designation',
                    'reporting_manager',
                    'system_role',
                    'actual_salary',
                    'employment_status',
                ],

                'assets' => $assetSummary,

                'documents' => $employee->documents->map(function ($doc) {
                    return [
                        'id'                  => $doc->id,
                        'employee_id'         => $doc->employee_id,
                        'category_id'         => $doc->category_id,
                        'title'               => $doc->title,
                        'file_path'           => $this->fileUrl($doc->file_path),
                        'verification_status' => $doc->verification_status,
                        'verified_by_user_id' => $doc->verified_by_user_id,
                        'uploaded_at'         => $doc->uploaded_at,
                    ];
                })->values(),

                'today_status' => $this->todayStatus(),
            ],
        ]);
    }

    public function updateProfile(Request $request)
{
    try {
        $employee = $this->currentEmployee();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
                'data' => null
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
            'gender' => ['sometimes', 'nullable', 'in:male,female,other'],
            'address' => ['sometimes', 'nullable'],
            'highest_qualification' => ['sometimes', 'nullable'],
            'cgpa_percentage' => ['sometimes', 'nullable'],
            'total_experience' => ['sometimes', 'nullable'],

            'bank_account_no' => ['sometimes', 'nullable'],
            'bank_account_type' => ['sometimes', 'nullable'],
            'bank_holder_name' => ['sometimes', 'nullable'],
            'ifsc_code' => ['sometimes', 'nullable'],
            'bank_branch' => ['sometimes', 'nullable'],

            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'resume_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        $profile = $employee->profile;

        if (!$profile) {
            $profile = EmployeeProfileM::create([
                'employee_id' => $employee->id
            ]);
        }

        $profileData = [];

        foreach ([
            'date_of_birth',
            'gender',
            'address',
            'highest_qualification',
            'cgpa_percentage',
            'total_experience',
            'bank_account_no',
            'bank_account_type',
            'bank_holder_name',
            'bank_branch',
        ] as $field) {
            if ($request->has($field)) {
                $profileData[$field] = $request->input($field);
            }
        }

        if ($request->has('ifsc_code')) {
            $profileData['ifsc_code'] = strtoupper((string) $request->ifsc_code);
        }

        $fileService = app(EmployeeFileS::class);

        if ($request->hasFile('profile_image')) {
            $profileData['profile_image'] = $fileService->upload(
                $request->file('profile_image'),
                $employee->id,
                $employee->employee_code,
                'profile'
            );
        }

        if ($request->hasFile('resume_file')) {
            $profileData['resume_file'] = $fileService->upload(
                $request->file('resume_file'),
                $employee->id,
                $employee->employee_code,
                'resume'
            );
        }

        if (empty($profileData)) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No data provided for update.',
                'data' => null,
            ], 422);
        }

        $profileData['employee_id'] = $employee->id;
        $profileData['profile_status'] = 'submitted';
        $profileData['rejection_reason'] = null;
        $profileData['updated_at'] = now();

        $profile->fill($profileData);
        $isCompleted = $this->isProfileCompleted($profile);

        $profileData['is_profile_completed'] = $isCompleted;
        $profileData['profile_completed_at'] = $isCompleted
            ? ($profile->profile_completed_at ?? now())
            : null;

        EmployeeProfileM::updateOrCreate(
            ['employee_id' => $employee->id],
            $profileData
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => null
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function listHolidays()
    {
        $holidays = Holiday::orderBy('date', 'ASC')->get();

        return response()->json([
            'success' => true,
            'message' => 'Holidays list fetched successfully',
            'errors'  => null,
            'data'    => $holidays,
        ]);
    }
}
