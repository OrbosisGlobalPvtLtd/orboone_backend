<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Current logged-in employee with required relations
     */
    private function currentEmployee()
    {
        return EmployeeM::with([
            'user',
            'department',
            'position',
            'systemRole',
            'reportingManager.user',
            'profile',
            'documents',
            'assetAllocations',
        ])->where('user_id', auth()->id())->first();
    }

    private function profileImageUrl(?EmployeeProfileM $profile): ?string
    {
        if (!$profile || empty($profile->profile_image)) {
            return null;
        }

        return asset($profile->profile_image);
    }

    private function resumeUrl(?EmployeeProfileM $profile): ?string
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

    /**
     * Employee-side required fields for profile completion
     */
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
        $fields = $this->requiredProfileFields($profile);
        $missing = [];

        foreach ($fields as $key => $value) {
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

        if ($total === 0) {
            return 0;
        }

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
        $canPunchAttendance = !$mustCompleteProfile && !$attendanceBlocked;
        $nextRoute = $mustCompleteProfile ? 'profile_completion' : 'dashboard';

        return [
            'is_profile_completed'   => $isCompleted,
            'must_complete_profile'  => $mustCompleteProfile,
            'completion_percentage'  => $this->completionPercentage($profile),
            'missing_profile_fields' => $this->missingProfileFields($profile),
            'can_punch_attendance'   => $canPunchAttendance,
            'attendance_blocked'     => $attendanceBlocked,
            'next_route'             => $nextRoute,
        ];
    }

    /**
     * GET PROFILE
     */
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

        // Keep DB field synced
        $profile->update([
            'is_profile_completed' => $completionStatus['is_profile_completed'],
            'profile_completed_at' => $completionStatus['is_profile_completed']
                ? ($profile->profile_completed_at ?? now())
                : null,
        ]);

        $assetSummary = [];
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

        return response()->json([
            'success' => true,
            'message' => 'Profile fetched successfully.',
            'errors'  => null,
            'data'    => [
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

                    'department' => [
                        'id'   => $employee->department?->id,
                        'name' => $employee->department?->name,
                    ],

                    'position' => [
                        'id'   => $employee->position?->id,
                        'name' => $employee->position?->name,
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
                    'work_mode',
                    'joining_date',
                    'department',
                    'position',
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
                        'file_path'           => $this->documentUrl($doc->file_path),
                        'verification_status' => $doc->verification_status,
                        'verified_by_user_id' => $doc->verified_by_user_id,
                        'uploaded_at'         => $doc->uploaded_at,
                    ];
                })->values(),

                'today_status' => $this->todayStatus(),
            ],
        ]);
    }

    /**
     * UPDATE PROFILE
     * Employee can update only allowed profile fields
     */
    public function updateProfile(Request $request)
    {
        try {
            $employee = $this->currentEmployee();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found.',
                    'errors'  => null,
                    'data'    => null,
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'profile_image'         => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
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
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                    'data'    => null,
                ], 422);
            }

            DB::beginTransaction();

            $profile = $employee->profile;

            if (!$profile) {
                $profile = EmployeeProfileM::create([
                    'employee_id' => $employee->id,
                ]);
            }

            $profileImagePath = $profile->profile_image;
            $resumeFilePath = $profile->resume_file;

            if ($request->hasFile('profile_image')) {
                if (!empty($profile->profile_image)) {
                    $oldImage = public_path($profile->profile_image);
                    if (file_exists($oldImage) && is_file($oldImage)) {
                        @unlink($oldImage);
                    }
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
                if (!empty($profile->resume_file)) {
                    $oldResume = public_path($profile->resume_file);
                    if (file_exists($oldResume) && is_file($oldResume)) {
                        @unlink($oldResume);
                    }
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
            ]);

            $profile->refresh();

            $completionStatus = $this->buildCompletionStatus($employee->fresh(['user', 'profile']), $profile);

            $profile->update([
                'is_profile_completed' => $completionStatus['is_profile_completed'],
                'profile_completed_at' => $completionStatus['is_profile_completed']
                    ? ($profile->profile_completed_at ?? now())
                    : null,
            ]);

            DB::commit();

            $employee->load([
                'user',
                'department',
                'position',
                'systemRole',
                'reportingManager.user',
                'profile',
                'documents',
                'assetAllocations',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'errors'  => null,
                'data'    => [
                    'user' => [
                        'id'    => $employee->user?->id,
                        'name'  => $employee->user?->name,
                        'email' => $employee->user?->email,
                    ],
                    'employee' => [
                        'id'                => $employee->id,
                        'employee_code'     => $employee->employee_code,
                        'employment_type'   => $employee->employment_type,
                        'work_mode'         => $employee->work_mode,
                        'employment_status' => $employee->employment_status,
                    ],
                    'profile' => [
                        'id'                    => $employee->profile?->id,
                        'employee_id'           => $employee->profile?->employee_id,
                        'profile_image'         => $this->profileImageUrl($employee->profile),
                        'date_of_birth'         => $employee->profile?->date_of_birth,
                        'gender'                => $employee->profile?->gender,
                        'address'               => $employee->profile?->address,
                        'highest_qualification' => $employee->profile?->highest_qualification,
                        'cgpa_percentage'       => $employee->profile?->cgpa_percentage,
                        'total_experience'      => $employee->profile?->total_experience,
                        'resume_file'           => $this->resumeUrl($employee->profile),
                        'bank_account_no'       => $employee->profile?->bank_account_no,
                        'bank_account_type'     => $employee->profile?->bank_account_type,
                        'bank_holder_name'      => $employee->profile?->bank_holder_name,
                        'ifsc_code'             => $employee->profile?->ifsc_code,
                        'bank_branch'           => $employee->profile?->bank_branch,
                        'is_profile_completed'  => (bool) $employee->profile?->is_profile_completed,
                        'profile_completed_at'  => $employee->profile?->profile_completed_at,
                    ],
                    'completion_status' => $this->buildCompletionStatus($employee, $employee->profile),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Update Error: ' . $e->getMessage(),
                'errors'  => null,
                'data'    => null,
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