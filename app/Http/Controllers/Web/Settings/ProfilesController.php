<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProfilesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['profileImage']);
    }

    public function index()
    {
        if (session('must_change_password')) {
            return view('auth.change-password');
        }

        $profile = auth()->user()->load([
            'role',
            'primaryRole',
            'employee.department',
            'employee.designation',
            'employee.reportingManager.user',
            'employee.profile',
        ]);

        $editableFields = [
            "profile_image",
            "date_of_birth",
            "gender",
            "address",
            "highest_qualification",
            "cgpa_percentage",
            "total_experience",
            "resume_file",
            "experience_type",
            "bank_account_no",
            "bank_account_type",
            "bank_holder_name",
            "ifsc_code",
            "bank_branch",
            "emergency_contact_number"
        ];

        $employee = $profile->employee;
        $documentTypes = collect();
        $employeeDocuments = collect();

        if ($employee) {
            $rawExperience = strtolower(trim(
                $employee->experience_type
                    ?? $employee->profile->experience_type
                    ?? 'fresher'
            ));

            $isExperienced = in_array($rawExperience, [
                'experienced',
                'experience',
                'exp',
                'yes',
                '1',
            ]);

            $appliesTo = $isExperienced
                ? ['all', 'both', 'employee', 'employees', 'experienced', 'experience', 'exp']
                : ['all', 'both', 'employee', 'employees', 'fresher', 'freshers'];

            if (Schema::hasTable('document_types')) {
                $documentTypes = DB::table('document_types')
                    ->where('scope', 'employee')
                    ->where('is_active', 1)
                    ->where(function ($q) use ($appliesTo) {
                        $q->whereNull('applies_to')
                            ->orWhere('applies_to', '')
                            ->orWhereIn(DB::raw('LOWER(TRIM(applies_to))'), $appliesTo);
                    })
                    ->orderBy('is_mandatory', 'desc')
                    ->orderBy('name')
                    ->get();
            }

            if (Schema::hasTable('employee_documents_new')) {
                $docQuery = DB::table('employee_documents_new')
                    ->where('employee_id', $employee->id);

                if (Schema::hasColumn('employee_documents_new', 'is_active')) {
                    $docQuery->where('is_active', 1);
                }

                $employeeDocuments = $docQuery->orderByDesc('id')
                    ->get()
                    ->keyBy('document_type_id');
            }
        }

        return view('settings.profile', compact('profile', 'editableFields', 'documentTypes', 'employeeDocuments'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            
            // Additional self-service fields
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'highest_qualification' => ['nullable', 'string', 'max:255'],
            'cgpa_percentage' => ['nullable', 'string', 'max:50'],
            'experience_type' => ['nullable', 'in:fresher,experienced'],
            'total_experience' => ['nullable', 'string', 'max:100'],
            
            'bank_holder_name' => ['nullable', 'string', 'max:150'],
            'bank_account_no' => ['nullable', 'string', 'max:100'],
            'bank_account_type' => ['nullable', 'string', 'max:100'],
            'ifsc_code' => ['nullable', 'string', 'max:50'],
            'bank_branch' => ['nullable', 'string', 'max:150'],
            
            'emergency_contact_number' => [
                'nullable',
                'regex:/^(?:\+91)?[6-9]\d{9}$/',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value === null || trim((string) $value) === '') {
                        return;
                    }

                    $normalize = static fn ($phone) => preg_replace('/\D+/', '', (string) $phone);
                    $normalizedEmergency = $normalize($value);
                    $userPhone = $normalize($user->phone ?? null);

                    if ($userPhone !== '' && $normalizedEmergency !== '' && str_ends_with($normalizedEmergency, $userPhone)) {
                        $fail('Please enter a valid emergency contact number.');
                    }
                },
            ],
            
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'resume_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        try {
            DB::transaction(function () use ($request, $user, $data) {
                // Backend Whitelist Enforcement: Force original name, email, and phone
                $userUpdate = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('users', 'phone')) {
                    $userUpdate['phone'] = $user->phone;
                }

                DB::table('users')->where('id', $user->id)->update($userUpdate);

                $employee = DB::table('employees_new')->where('user_id', $user->id)->first();

                if ($employee) {
                    $profile = DB::table('employee_profiles')->where('employee_id', $employee->id)->first();
                    
                    $isSubmittedOrApproved = $profile && $profile->profile_status === 'submitted';
                    
                    // Whitelisted fields allowed to be updated
                    $editableFields = [
                        "profile_image",
                        "date_of_birth",
                        "gender",
                        "address",
                        "highest_qualification",
                        "cgpa_percentage",
                        "total_experience",
                        "resume_file",
                        "experience_type",
                        "bank_account_no",
                        "bank_account_type",
                        "bank_holder_name",
                        "ifsc_code",
                        "bank_branch",
                        "emergency_contact_number"
                    ];

                    $profileData = [
                        'employee_id' => $employee->id,
                        'updated_at' => now(),
                    ];

                    // Backend Whitelist Enforcement: Ignore any fields not in the editableFields whitelist
                    if (!$isSubmittedOrApproved) {
                        foreach ($editableFields as $field) {
                            if ($field !== 'profile_image' && $field !== 'resume_file' && isset($data[$field])) {
                                $profileData[$field] = $data[$field];
                            }
                        }

                        if (isset($profileData['bank_account_type'])) {
                            $rawBank = strtolower(trim((string) $profileData['bank_account_type']));
                            if (in_array($rawBank, ['saving', 'savings'], true)) {
                                $profileData['bank_account_type'] = 'Savings';
                            } elseif (in_array($rawBank, ['current'], true)) {
                                $profileData['bank_account_type'] = 'Current';
                            } elseif (in_array($rawBank, ['salary'], true)) {
                                $profileData['bank_account_type'] = 'Salary';
                            }
                        }
                    }

                    // Process profile image & resume file uploads (allowed in all states or locked as per allowed rules)
                    if ($request->hasFile('profile_image')) {
                        $fileService = app(\App\Services\HRMS\Employee\EmployeeFileS::class);
                        $profileData['profile_image'] = $fileService->upload(
                            $request->file('profile_image'),
                            $employee->id,
                            $employee->employee_code,
                            'profile'
                        );
                    }

                    if ($request->hasFile('resume_file')) {
                        if (!$isSubmittedOrApproved) {
                            $fileService = app(\App\Services\HRMS\Employee\EmployeeFileS::class);
                            $profileData['resume_file'] = $fileService->upload(
                                $request->file('resume_file'),
                                $employee->id,
                                $employee->employee_code,
                                'resume'
                            );
                        }
                    }

                    if (isset($data['experience_type']) && !$isSubmittedOrApproved && Schema::hasColumn('employees_new', 'experience_type')) {
                        DB::table('employees_new')->where('id', $employee->id)->update([
                            'experience_type' => $data['experience_type'],
                            'updated_at' => now(),
                        ]);
                    }

                    $exists = DB::table('employee_profiles')->where('employee_id', $employee->id)->exists();

                    if (!$exists) {
                        $profileData['profile_status'] = 'approved';
                        $profileData['is_profile_completed'] = 1;
                        $profileData['created_at'] = now();
                        DB::table('employee_profiles')->insert($profileData);
                    } else {
                        DB::table('employee_profiles')->where('employee_id', $employee->id)->update($profileData);
                    }
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('profile.index')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }

        $updateData = [
            'password' => Hash::make($request->password),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'must_change_password')) {
            $updateData['must_change_password'] = 0;
        }

        DB::table('users')->where('id', $user->id)->update($updateData);
        $request->session()->forget('must_change_password');

        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return redirect()
            ->route('profile.index')
            ->with('success', 'Password updated successfully.');
    }

    public function profileImage($employeeId)
    {
        $user = auth()->user();
        
        $employeeIdNum = is_object($employeeId) && isset($employeeId->id) ? (int)$employeeId->id : (int)$employeeId;

        $employee = DB::table('employees_new')->where('id', $employeeIdNum)->first();
        if (!$employee) {
            $employee = DB::table('employees_new')->where('user_id', $employeeIdNum)->first();
        }
        if (!$employee) {
            return $this->serveDefaultAvatar();
        }

        if ($user) {
            $hasPermission = false;
            if ($employee->user_id == $user->id || $employee->id == $user->id) {
                $hasPermission = true;
            } else {
                $userRole = DB::table('roles')
                    ->join('user_roles', 'roles.id', '=', 'user_roles.role_id')
                    ->where('user_roles.user_id', $user->id)
                    ->whereIn('roles.slug', ['super_admin', 'admin', 'hr_admin', 'finance_admin', 'operations_admin'])
                    ->exists();
                if ($userRole) {
                    $hasPermission = true;
                }
            }

            if (!$hasPermission) {
                return $this->serveDefaultAvatar();
            }
        }

        $profile = DB::table('employee_profiles')->where('employee_id', $employee->id)->first();
        
        $path = $profile ? trim((string)$profile->profile_image) : null;
        $filePath = null;

        if ($path) {
            if (\Illuminate\Support\Facades\Storage::disk('private')->exists($path)) {
                $filePath = \Illuminate\Support\Facades\Storage::disk('private')->path($path);
            } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
            } elseif (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
                $filePath = \Illuminate\Support\Facades\Storage::disk('local')->path($path);
            } elseif (file_exists(public_path($path))) {
                $filePath = public_path($path);
            } elseif (file_exists(storage_path('app/' . $path))) {
                $filePath = storage_path('app/' . $path);
            } elseif (file_exists(storage_path('app/public/' . $path))) {
                $filePath = storage_path('app/public/' . $path);
            } elseif (file_exists(storage_path('app/private/' . $path))) {
                $filePath = storage_path('app/private/' . $path);
            }
        }

        if ($filePath && file_exists($filePath)) {
            $mime = mime_content_type($filePath) ?: 'image/jpeg';
            
            if (strpos($mime, 'image/') === 0) {
                return response()->file($filePath, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'no-cache, private',
                ]);
            }
        }

        return $this->serveDefaultAvatar();
    }

    private function serveDefaultAvatar()
    {
        $defaultAvatarPath = public_path('assets/images/default-avatar.png');
        if (!file_exists($defaultAvatarPath)) {
            $defaultAvatarPath = public_path('images/avatar.png');
        }
        if (!file_exists($defaultAvatarPath)) {
            return response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='), 200, [
                'Content-Type' => 'image/png'
            ]);
        }

        return response()->file($defaultAvatarPath);
    }

    public function submitForVerification()
    {
        $user = auth()->user();
        $employee = DB::table('employees_new')->where('user_id', $user->id)->first();

        if (!$employee) {
            return back()->with('error', 'Employee profile not found.');
        }

        $profile = DB::table('employee_profiles')->where('employee_id', $employee->id)->first();
        if (!$profile || !$profile->gender || !$profile->date_of_birth || !$profile->address || !$profile->emergency_contact_number || !$profile->bank_account_no || !$profile->ifsc_code) {
            return back()->with('error', 'Please complete all required profile fields (Gender, DOB, Permanent Address, Emergency Contact, and Bank Details) before submitting.');
        }

        // Check if mandatory documents are uploaded
        if (Schema::hasTable('document_types') && Schema::hasTable('employee_documents_new')) {
            $rawExperience = strtolower(trim(
                $employee->experience_type
                    ?? $profile->experience_type
                    ?? 'fresher'
            ));

            $isExperienced = in_array($rawExperience, [
                'experienced',
                'experience',
                'exp',
                'yes',
                '1',
            ]);

            $appliesTo = $isExperienced
                ? ['all', 'both', 'employee', 'employees', 'experienced', 'experience', 'exp']
                : ['all', 'both', 'employee', 'employees', 'fresher', 'freshers'];

            $mandatoryTypes = DB::table('document_types')
                ->where('scope', 'employee')
                ->where('is_active', 1)
                ->where('is_mandatory', 1)
                ->where(function ($q) use ($appliesTo) {
                    $q->whereNull('applies_to')
                        ->orWhere('applies_to', '')
                        ->orWhereIn(DB::raw('LOWER(TRIM(applies_to))'), $appliesTo);
                })
                ->pluck('id');

            $uploadedCount = DB::table('employee_documents_new')
                ->where('employee_id', $employee->id)
                ->whereIn('document_type_id', $mandatoryTypes)
                ->count();

            if ($uploadedCount < count($mandatoryTypes)) {
                return back()->with('error', 'Please upload all mandatory compliance documents before submitting.');
            }
        }

        DB::table('employee_profiles')->updateOrInsert(
            ['employee_id' => $employee->id],
            [
                'profile_status' => 'submitted',
                'is_profile_completed' => 1,
                'updated_at' => now(),
            ]
        );

        return redirect()
            ->route('profile.index')
            ->with('success', 'Profile submitted successfully for HR verification.');
    }
}
