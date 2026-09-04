<?php

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;

use App\Services\HRMS\Employee\EmployeeFileS;
use App\Services\HRMS\Document\EmployeeDocumentCompletionS;
use App\Services\HRMS\Employee\EmployeeProfileCompletionS;
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
            'documents.type',
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
            'emergency_contact_number' => $profile->emergency_contact_number,
            'date_of_birth'         => $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('Y-m-d') : null,
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

        $holidays = Holiday::whereDate('holiday_date', $today->format('Y-m-d'))
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('is_working_day_override', 0)
                  ->orWhereNull('is_working_day_override');
            })
            ->pluck('title')
            ->values();

        return [
            'is_holiday' => $holidays->isNotEmpty(),
            'holidays'   => $holidays,
            'festivals'  => $holidays,
            'birthdays'  => [],
            'events'     => [],
        ];
    }

    // private function buildCompletionStatus(EmployeeM $employee, EmployeeProfileM $profile): array
    // {
    //     $documentCompletion = app(EmployeeDocumentCompletionS::class);
    //     $isEmployee = method_exists($employee->user, 'isEmployee')
    //         ? (bool) $employee->user->isEmployee()
    //         : true;

    //     $missingProfileFields = $documentCompletion->missingProfileFields($profile, $employee);
    //     $isCompleted = count($missingProfileFields) === 0;
    //     $documentStatus = $documentCompletion->completion($employee);

    //     $requiredVerified = ($documentStatus['verified_count'] ?? 0) === ($documentStatus['required_count'] ?? 0)
    //         && ($documentStatus['required_count'] ?? 0) > 0;

    //     $canPunchAttendance = ! $isEmployee || ($profile->profile_status === 'approved' && $requiredVerified);

    //     $docVerificationStatus = 'missing';
    //     if (($documentStatus['rejected_count'] ?? 0) > 0) {
    //         $docVerificationStatus = 'rejected';
    //     } elseif (($documentStatus['pending_count'] ?? 0) > 0) {
    //         $docVerificationStatus = 'pending';
    //     } elseif ($requiredVerified) {
    //         $docVerificationStatus = 'verified';
    //     }

    //     $mustCompleteProfile = $isEmployee ? ! $canPunchAttendance : false;
    //     $attendanceBlocked = $isEmployee ? ! $canPunchAttendance : false;

    //     $nextRoute = 'dashboard';
    //     if ($mustCompleteProfile) {
    //         if ($profile->profile_status === 'submitted') {
    //             $nextRoute = 'verification_pending';
    //         } elseif ($profile->profile_status === 'rejected') {
    //             $nextRoute = 'profile_completion';
    //         } else {
    //             $nextRoute = $isCompleted ? 'document_completion' : 'profile_completion';
    //         }
    //     }

    //     return [
    //         'is_profile_completed'         => (bool) $profile->is_profile_completed,
    //         'profile_verification_status'  => $profile->profile_status ?? 'pending',
    //         'document_verification_status' => $docVerificationStatus,
    //         'required_documents_verified'  => $requiredVerified,
    //         'can_punch_attendance'         => $canPunchAttendance,
    //         'attendance_blocked'           => $attendanceBlocked,
    //         'next_route'                   => $nextRoute,

    //         'must_complete_profile'        => $mustCompleteProfile,
    //         'completion_percentage'        => $documentCompletion->profileCompletionPercentage($profile, $employee),
    //         'missing_profile_fields'       => $missingProfileFields,
    //         'document_completion_status'   => $documentStatus,
    //         'experience_type'              => $profile->experience_type ?? 'fresher',
    //     ];
    // }

    private function buildCompletionStatus(EmployeeM $employee, EmployeeProfileM $profile): array
    {
        $completionService = app(EmployeeProfileCompletionS::class);
        return $completionService->buildCompletionStatus($employee, $profile);
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

        $assetSummary = [];
        $salaryHistory = [];

        if (method_exists($employee, 'assetAllocations') && $employee->relationLoaded('assetAllocations')) {
            $assetSummary = $employee->assetAllocations->map(function ($asset) {
                $brandModel = $asset->brand_model;
                $parts = explode(' ', trim($brandModel ?? ''), 2);
                $brand = $parts[0] ?? '';
                $model = $parts[1] ?? $brand;

                return [
                    'id'            => $asset->id ?? null,
                    'asset_name'    => $asset->brand_model ?? 'Asset',
                    'asset_code'    => $asset->asset_id_sn ?? null,
                    'category'      => $asset->asset_type ?? null,
                    'serial_number' => $asset->asset_id_sn ?? null,
                    'brand'         => $brand,
                    'model'         => $model,
                    'assigned_date' => $asset->issue_date ?? ($asset->created_at ? $asset->created_at->toDateString() : null),
                    'status'        => strtolower($asset->status ?? 'active'),
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
                    'experience_type'               => $profile->experience_type ?? 'fresher',
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
                    'emergency_contact_number' => $profile->emergency_contact_number,
                    'profile_image'         => $this->fileUrl($profile->profile_image),
                    'date_of_birth'         => $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('Y-m-d') : null,
                    'gender'                => $profile->gender,
                    'address'               => $profile->address,
                    'highest_qualification' => $profile->highest_qualification,
                    'cgpa_percentage'       => $profile->cgpa_percentage,
                    'total_experience'      => $profile->total_experience,
                    'experience_type'       => $profile->experience_type ?? 'fresher',
                    'resume_file'           => $this->fileUrl($profile->resume_file),
                    'bank_account_no'       => $profile->bank_account_no,
                    'bank_account_type'     => $profile->bank_account_type,
                    'bank_holder_name'      => $profile->bank_holder_name,
                    'ifsc_code'             => $profile->ifsc_code,
                    'bank_branch'           => $profile->bank_branch,
                    'profile_status'        => $profile->profile_status ?? 'pending',
                    'rejection_reason'      => $profile->rejection_reason,
                    'is_profile_completed'  => (bool) $profile->is_profile_completed,
                    'profile_completed_at'  => $profile->profile_completed_at,
                    'updated_at'            => $profile->updated_at ? $profile->updated_at->timestamp : time(),
                ],

                'completion_status' => $completionStatus,
                'document_completion_status' => $completionStatus['document_completion_status'] ?? null,

                'editable_fields' => [
                    'profile_image',
                    'date_of_birth',
                    'gender',
                    'address',
                    'highest_qualification',
                    'cgpa_percentage',
                    'total_experience',
                    'resume_file',
                    'experience_type',
                    'emergency_contact_number',
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

                'documents' => $employee->documents->map(
                    fn($doc) => app(EmployeeDocumentCompletionS::class)->formatDocument($doc)
                )->values(),

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

            // Sanitize input payload by removing non-printable/invisible unicode characters
            $rawInput = $request->all();
            $input = [];
            foreach ($rawInput as $key => $val) {
                if (is_string($val)) {
                    // Remove invisible spaces (\u200B, \u00A0, \u200E, etc.) and trim
                    $cleaned = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}\x{00A0}]/u', '', $val);
                    $input[$key] = trim($cleaned);
                } else {
                    $input[$key] = $val;
                }
            }
            $request->merge($input);

            $validator = Validator::make($input, [
                'date_of_birth' => ['sometimes', 'nullable', 'date'],
                'gender' => ['sometimes', 'nullable', 'in:male,female,other'],
                'address' => ['sometimes', 'nullable'],
                'highest_qualification' => ['sometimes', 'nullable'],
                'cgpa_percentage' => ['sometimes', 'nullable', 'regex:/^\d+(\.\d+)?$/'],
                'total_experience' => ['sometimes', 'nullable', 'regex:/^\d+(\.\d+)?$/'],
                'experience_type' => ['sometimes', 'nullable', 'in:fresher,experienced'],

                'bank_account_no' => ['sometimes', 'nullable', 'regex:/^\d{8,20}$/'],
                'bank_account_type' => ['sometimes', 'nullable', 'in:saving,savings,current'],
                'bank_holder_name' => ['sometimes', 'nullable', 'regex:/^[a-zA-Z\s\.]+$/'],
                'ifsc_code' => ['sometimes', 'nullable', 'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i'],
                'bank_branch' => ['sometimes', 'nullable'],
                'emergency_contact_number' => [
                    'sometimes',
                    'nullable',
                    'regex:/^(?:\+91)?[6-9]\d{9}$/',
                    function ($attribute, $value, $fail) use ($employee) {
                        if ($value === null || trim((string) $value) === '') {
                            return;
                        }

                        $normalize = static fn ($phone) => preg_replace('/\D+/', '', (string) $phone);
                        $normalizedEmergency = $normalize($value);
                        $userPhone = $normalize($employee->user->phone ?? null);

                        if ($userPhone !== '' && $normalizedEmergency !== '' && str_ends_with($normalizedEmergency, $userPhone)) {
                            $fail('Emergency contact cannot be the same as your primary phone number.');
                        }
                    },
                ],

                'profile_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:10240'],
                'resume_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            ], [
                'bank_account_no.regex' => 'Bank account number must contain digits only (8 to 20 digits).',
                'ifsc_code.regex' => 'Invalid IFSC code format (e.g. SBIN00010168).',
                'bank_holder_name.regex' => 'Account holder name can only contain letters and spaces.',
                'emergency_contact_number.regex' => 'Please enter a valid 10-digit emergency contact number.',
                'cgpa_percentage.regex' => 'CGPA / Percentage must be a valid number.',
                'total_experience.regex' => 'Total experience must be a valid number.',
                'bank_account_type.in' => 'Please select a valid account type (Saving or Current).',
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

            if ($request->has('experience_type')) {
                $experienceType = $request->input('experience_type');

                $profileData['experience_type'] = $experienceType;

                if ($experienceType === 'fresher') {
                    $profileData['total_experience'] = '0';
                }
            }

            foreach (
                [
                    'date_of_birth',
                    'gender',
                    'address',
                    'highest_qualification',
                    'cgpa_percentage',
                    'total_experience',
                    'emergency_contact_number',
                    'bank_account_no',
                    'bank_account_type',
                    'bank_holder_name',
                    'bank_branch',
                ] as $field
            ) {
                if ($request->has($field)) {
                    if ($field === 'total_experience' && (($profileData['experience_type'] ?? null) === 'fresher')) {
                        continue;
                    }

                    $val = $request->input($field);
                    if ($val !== null && trim((string) $val) !== '') {
                        $profileData[$field] = $val;
                    }
                }
            }

            if ($request->has('ifsc_code') && $request->input('ifsc_code') !== null && trim((string) $request->input('ifsc_code')) !== '') {
                $profileData['ifsc_code'] = strtoupper((string) $request->input('ifsc_code'));
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
            $profileData['rejection_reason'] = null;
            $profileData['updated_at'] = now();

            if (! $profile->profile_status) {
                $profileData['profile_status'] = 'pending';
            }

            if (! $profile->is_profile_completed) {
                $profileData['is_profile_completed'] = false;
                $profileData['profile_completed_at'] = null;
            }

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

    public function getProfileSummary()
    {
        $user = auth()->user();
        $employee = EmployeeM::with([
            'user',
            'department',
            'designation',
            'systemRole',
            'reportingManager.user',
            'profile',
        ])->where('user_id', auth()->id())->first();

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

        return response()->json([
            'success' => true,
            'message' => 'Profile summary fetched successfully.',
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
                    'experience_type'               => $profile->experience_type ?? 'fresher',
                    'employee_stage'                => $employee->employee_stage,
                    'work_mode'                     => $employee->work_mode,
                    'work_schedule_type'            => $employee->work_schedule_type,
                    'joining_date'                  => $employee->joining_date,
                    'employment_status'             => $employee->employment_status,
                    'is_active'                     => $employee->is_active,

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
                    'emergency_contact_number' => $profile->emergency_contact_number,
                    'profile_image'         => $this->fileUrl($profile->profile_image),
                    'profile_status'        => $profile->profile_status ?? 'pending',
                    'rejection_reason'      => $profile->rejection_reason,
                    'is_profile_completed'  => (bool) $profile->is_profile_completed,
                ],

                'completion_status' => $completionStatus,
            ],
        ]);
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

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = auth()->user();

        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('users', 'device_token')) {
            $user->update(['device_token' => $request->fcm_token]);
        }

        return response()->json([
            'success' => true,
            'status' => true,
            'message' => 'FCM token updated successfully.',
            'data' => null,
            'errors' => null,
        ]);
    }
}
