<?php

namespace App\Legacy\Http\Controllers\Web\HRMS\Employee;

use App\Http\Controllers\Controller;
use App\Models\Access;
use App\Models\EmployeeModel;
use App\Models\EmployeeProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $profiles = EmployeeProfile::with([
            'employee.user',
            'employee.department',
            'employee.position',
            'employee.systemRole',
        ])->latest()->paginate(15);

        $accesses = Access::where('role_id', $user->role_id)->get();

        return view('employee.profile.index', compact('profiles', 'user', 'accesses'));
    }

    public function create($employeeId = null)
    {
        $user = auth()->user();

        $employees = EmployeeModel::with(['user', 'department', 'position', 'systemRole'])
            ->orderBy('employee_code')
            ->get();

        $selectedEmployee = null;

        if ($employeeId) {
            $selectedEmployee = EmployeeModel::with(['user', 'department', 'position', 'systemRole'])
                ->findOrFail($employeeId);
        }

        $accesses = Access::where('role_id', $user->role_id)->get();

        return view('employee.profile.create', compact(
            'employees',
            'selectedEmployee',
            'user',
            'accesses'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'           => 'required|exists:employees_new,id|unique:employee_profiles,employee_id',
            'profile_image'         => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'date_of_birth'         => 'nullable|date',
            'gender'                => ['nullable', Rule::in(['male', 'female', 'other'])],
            'address'               => 'nullable|string',
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

        $profileImagePath = null;
        $resumeFilePath = null;

        if ($request->hasFile('profile_image')) {
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
            $resume = $request->file('resume_file');
            $resumeName = 'resume_' . time() . '_' . uniqid() . '.' . $resume->getClientOriginalExtension();
            $destination = public_path('uploads/employee_resumes');

            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }

            $resume->move($destination, $resumeName);
            $resumeFilePath = 'uploads/employee_resumes/' . $resumeName;
        }

        EmployeeProfile::create([
            'employee_id'           => $request->employee_id,
            'profile_image'         => $profileImagePath,
            'date_of_birth'         => $request->date_of_birth,
            'gender'                => $request->gender,
            'address'               => $request->address,
            'highest_qualification' => $request->highest_qualification,
            'cgpa_percentage'       => $request->cgpa_percentage,
            'total_experience'      => $request->total_experience,
            'resume_file'           => $resumeFilePath,
            'bank_account_no'       => $request->bank_account_no,
            'bank_account_type'     => $request->bank_account_type,
            'bank_holder_name'      => $request->bank_holder_name,
            'ifsc_code'             => $request->ifsc_code,
            'bank_branch'           => $request->bank_branch,
            'is_profile_completed'  => $request->has('is_profile_completed') ? $request->is_profile_completed : 0,
            'profile_completed_at'  => $request->has('is_profile_completed') && $request->is_profile_completed ? now() : null,
        ]);

        return redirect()->route('employee-profiles.index')->with('success', 'Employee profile created successfully.');
    }

    public function show($id)
    {
        $user = auth()->user();

        $profile = EmployeeProfile::with([
            'employee.user',
            'employee.department',
            'employee.position',
            'employee.systemRole',
        ])->findOrFail($id);

        $accesses = Access::where('role_id', $user->role_id)->get();

        return view('employee.profile.show', compact('profile', 'user', 'accesses'));
    }

    public function edit($id)
    {
        $user = auth()->user();

        $profile = EmployeeProfile::with([
            'employee.user',
            'employee.department',
            'employee.position',
            'employee.systemRole',
        ])->findOrFail($id);

        $employees = EmployeeModel::with(['user', 'department', 'position', 'systemRole'])
            ->orderBy('employee_code')
            ->get();

        $accesses = Access::where('role_id', $user->role_id)->get();

        return view('employee.profile.edit', compact('profile', 'employees', 'user', 'accesses'));
    }

    public function update(Request $request, $id)
    {
        $profile = EmployeeProfile::findOrFail($id);

        $request->validate([
            'employee_id'           => 'required|exists:employees_new,id|unique:employee_profiles,employee_id,' . $profile->id,
            'profile_image'         => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'date_of_birth'         => 'nullable|date',
            'gender'                => ['nullable', Rule::in(['male', 'female', 'other'])],
            'address'               => 'nullable|string',
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

        $profileImagePath = $profile->profile_image;
        $resumeFilePath = $profile->resume_file;

        if ($request->hasFile('profile_image')) {
            if (!empty($profile->profile_image)) {
                $oldImage = public_path($profile->profile_image);
                if (file_exists($oldImage) && is_file($oldImage)) {
                    unlink($oldImage);
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
                    unlink($oldResume);
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

        $isCompleted = $request->has('is_profile_completed') ? $request->is_profile_completed : 0;

        $profile->update([
            'employee_id'           => $request->employee_id,
            'profile_image'         => $profileImagePath,
            'date_of_birth'         => $request->date_of_birth,
            'gender'                => $request->gender,
            'address'               => $request->address,
            'highest_qualification' => $request->highest_qualification,
            'cgpa_percentage'       => $request->cgpa_percentage,
            'total_experience'      => $request->total_experience,
            'resume_file'           => $resumeFilePath,
            'bank_account_no'       => $request->bank_account_no,
            'bank_account_type'     => $request->bank_account_type,
            'bank_holder_name'      => $request->bank_holder_name,
            'ifsc_code'             => $request->ifsc_code,
            'bank_branch'           => $request->bank_branch,
            'is_profile_completed'  => $isCompleted,
            'profile_completed_at'  => $isCompleted ? ($profile->profile_completed_at ?? now()) : null,
        ]);

        return redirect()->route('employee-profiles.index')->with('success', 'Employee profile updated successfully.');
    }

    public function destroy($id)
    {
        $profile = EmployeeProfile::findOrFail($id);

        if (!empty($profile->profile_image)) {
            $oldImage = public_path($profile->profile_image);
            if (file_exists($oldImage) && is_file($oldImage)) {
                unlink($oldImage);
            }
        }

        if (!empty($profile->resume_file)) {
            $oldResume = public_path($profile->resume_file);
            if (file_exists($oldResume) && is_file($oldResume)) {
                unlink($oldResume);
            }
        }

        $profile->delete();

        return redirect()->route('employee-profiles.index')->with('success', 'Employee profile deleted successfully.');
    }
}
