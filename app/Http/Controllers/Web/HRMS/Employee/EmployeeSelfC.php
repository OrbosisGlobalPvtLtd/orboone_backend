<?php

namespace App\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\HRMS\Employee\EmployeeM;
use App\Models\HRMS\Employee\EmployeeProfileM;
use App\Services\HRMS\Employee\EmployeeProfileCompletionS;
use App\Services\HRMS\Employee\EmployeeFileS;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EmployeeSelfC extends Controller
{
    protected EmployeeProfileCompletionS $completionService;
    
    public function __construct(EmployeeProfileCompletionS $completionService)
    {
        $this->completionService = $completionService;
    }
    
    private function getCurrentEmployee()
    {
        return EmployeeM::with(['user', 'profile', 'documents.type'])
            ->where('user_id', Auth::id())
            ->first();
    }
    
    public function completeProfile()
    {
        $employee = $this->getCurrentEmployee();
        if (!$employee) {
            abort(404, 'Employee not found');
        }
        
        $profile = $employee->profile;
        if (!$profile) {
            $profile = EmployeeProfileM::create(['employee_id' => $employee->id]);
            $employee->load('profile');
        }
        
        $status = $this->completionService->buildCompletionStatus($employee, $profile);
        
        // If profile is submitted or approved, they shouldn't be here (unless rejected)
        if (!$status['must_complete_profile']) {
            return redirect()->route('hrms.employee.my_profile');
        }
        
        $rawExperience = strtolower(trim($employee->experience_type ?? $profile->experience_type ?? 'fresher'));
        $isExperienced = in_array($rawExperience, ['experienced', 'experience', 'exp', 'yes', '1']);
        $appliesTo = $isExperienced
            ? ['all', 'both', 'employee', 'employees', 'experienced', 'experience', 'exp']
            : ['all', 'both', 'employee', 'employees', 'fresher', 'freshers'];

        $documentTypes = \App\Models\HRMS\Document\DocumentTypeM::where('scope', 'employee')
            ->where('is_active', 1)
            ->where(function ($q) use ($appliesTo) {
                $q->whereNull('applies_to')
                    ->orWhere('applies_to', '')
                    ->orWhereIn(DB::raw('LOWER(TRIM(applies_to))'), $appliesTo);
            })
            ->orderBy('is_mandatory', 'desc')
            ->orderBy('name')
            ->get();

        $uploadedDocuments = \App\Models\HRMS\Document\EmployeeDocumentM::where('employee_id', $employee->id)
            ->get()
            ->keyBy('document_type_id');
        
        return view('hrms.employee-self.complete-profile', compact('employee', 'profile', 'status', 'documentTypes', 'uploadedDocuments'));
    }
    
    public function submitVerification(Request $request)
    {
        $employee = $this->getCurrentEmployee();
        if (!$employee) {
            abort(404, 'Employee not found');
        }

        // First, save profile details
        $validator = Validator::make($request->all(), [
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string',
            'emergency_contact_number' => [
                'required',
                'regex:/^(?:\+91)?[6-9]\d{9}$/',
                function ($attribute, $value, $fail) use ($employee) {
                    $normalize = static fn ($phone) => preg_replace('/\D+/', '', (string) $phone);
                    $normalizedEmergency = $normalize($value);
                    $userPhone = $normalize($employee->user->phone ?? null);

                    if ($userPhone !== '' && $normalizedEmergency !== '' && str_ends_with($normalizedEmergency, $userPhone)) {
                        $fail('Please enter a valid emergency contact number.');
                    }
                },
            ],
            'highest_qualification' => 'required|string',
            'cgpa_percentage' => 'nullable|string',
            'total_experience' => 'required|string',
            'experience_type' => 'required|in:fresher,experienced',
            'bank_account_no' => 'required|string',
            'bank_account_type' => 'required|string',
            'bank_holder_name' => 'required|string',
            'ifsc_code' => 'required|string',
            'bank_branch' => 'required|string',
            'profile_image' => 'nullable|image|max:2048',
            'resume_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Please complete all required profile fields.');
        }

        DB::beginTransaction();
        try {
            $profile = $employee->profile;
            if (!$profile) {
                $profile = EmployeeProfileM::create(['employee_id' => $employee->id]);
            }
            
            $profileData = $request->except(['_token', 'profile_image', 'resume_file']);
            
            if ($request->experience_type === 'fresher') {
                $profileData['total_experience'] = '0';
            }
            
            $fileService = app(EmployeeFileS::class);
            if ($request->hasFile('profile_image')) {
                $profileData['profile_image'] = $fileService->upload($request->file('profile_image'), $employee->id, $employee->employee_code, 'profile');
            }
            if ($request->hasFile('resume_file')) {
                $profileData['resume_file'] = $fileService->upload($request->file('resume_file'), $employee->id, $employee->employee_code, 'resume');
            }
            
            $profileData['updated_at'] = now();
            
            $profile->update($profileData);
            
            // Now check document completion
            $employee->refresh();
            
            $rawExperience = strtolower(trim($employee->experience_type ?? $profileData['experience_type'] ?? 'fresher'));
            $isExperienced = in_array($rawExperience, ['experienced', 'experience', 'exp', 'yes', '1']);
            $appliesTo = $isExperienced
                ? ['all', 'both', 'employee', 'employees', 'experienced', 'experience', 'exp']
                : ['all', 'both', 'employee', 'employees', 'fresher', 'freshers'];

            $requiredDocumentTypeIds = \App\Models\HRMS\Document\DocumentTypeM::where('scope', 'employee')
                ->where('is_active', 1)
                ->where('is_mandatory', 1)
                ->where(function ($q) use ($appliesTo) {
                    $q->whereNull('applies_to')
                        ->orWhere('applies_to', '')
                        ->orWhereIn(DB::raw('LOWER(TRIM(applies_to))'), $appliesTo);
                })
                ->pluck('id');

            $uploadedDocumentTypeIds = \App\Models\HRMS\Document\EmployeeDocumentM::where('employee_id', $employee->id)
                ->whereIn('document_type_id', $requiredDocumentTypeIds)
                ->pluck('document_type_id');

            $missingDocs = $requiredDocumentTypeIds->diff($uploadedDocumentTypeIds);

            if ($missingDocs->count() > 0) {
                DB::commit();
                return redirect()->back()->with('error', 'Profile saved, but please upload all mandatory documents before submitting.');
            }
            
            // All good, submit!
            $profile->update([
                'profile_status' => 'submitted',
                'is_profile_completed' => 0,
                'rejection_reason' => null
            ]);

            $employeeName = $employee->user->name ?? $employee->employee_code ?: 'An employee';
            app(\App\Services\HRMS\Notification\NotificationS::class)->notifyHrAndSuperAdmin(
                'Profile Verification Request',
                $employeeName . ' submitted profile for verification.',
                'profile_submitted',
                'hrms.employees.profile.view',
                ['employee' => $employee->id],
                [
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id,
                    'employee_code' => $employee->employee_code,
                    'redirect_type' => 'profile_view',
                    'notification_type' => 'profile_submitted',
                    'action_url' => route('hrms.employees.profile.view', ['employee' => $employee->id]),
                    'route_name' => 'hrms.employees.profile.view',
                    'route_params' => ['employee' => $employee->id],
                ]
            );
            
            DB::commit();
            return redirect()->route('hrms.employee.my_profile')->with('success', 'Your profile and documents have been submitted for HR approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
    
    public function myProfile()
    {
        $employee = $this->getCurrentEmployee();
        if (!$employee) {
            abort(404, 'Employee not found');
        }
        
        $profile = $employee->profile;
        $status = $this->completionService->buildCompletionStatus($employee, $profile);
        
        if ($status['must_complete_profile']) {
            return redirect()->route('hrms.employee.complete_profile');
        }
        
        $documentCompletionService = app(\App\Services\HRMS\Document\EmployeeDocumentCompletionS::class);
        $payload = $documentCompletionService->requiredPayload($employee);
        
        return view('hrms.employee-self.my-profile', compact('employee', 'profile', 'status', 'payload'));
    }
}
