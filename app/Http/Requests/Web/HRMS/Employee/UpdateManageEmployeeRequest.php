<?php

namespace App\Http\Requests\Web\HRMS\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateManageEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $employeeParam = $this->route('employee');
        $userId = null;

        if (is_object($employeeParam)) {
            $userId = $employeeParam->user_id ?? (isset($employeeParam->id) ? DB::table('employees_new')->where('id', $employeeParam->id)->value('user_id') : null);
        } elseif (!empty($employeeParam)) {
            $userId = DB::table('employees_new')->where('id', $employeeParam)->value('user_id');
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . ($userId ?? 0)],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation_id' => ['required', 'exists:designations,id'],
            'reporting_manager_employee_id' => ['nullable', 'exists:employees_new,id'],
            'system_role_id' => ['required', 'exists:roles,id'],

            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'intern', 'freelancer', 'contract'])],
            'employee_stage' => ['nullable', Rule::in(['probation', 'internship', 'permanent'])],
            'work_mode' => ['required', Rule::in(['wfo', 'wfh', 'hybrid'])],
            'work_schedule_type' => ['nullable', Rule::in(['full_day', 'part_day', 'hourly', 'shift_based', 'general', 'general_shift', 'wfh', 'wfh_shift', 'part_time', 'part_time_shift', 'part_time_morning', 'part_time_evening', 'half_day', 'half_day_shift', 'half_day_morning', 'half_day_evening', 'flexible_part_time', 'dynamic_hours'])],
            'employment_status' => ['required', Rule::in(['active', 'resigned', 'terminated', 'inactive'])],

            'joining_date' => ['nullable', 'date'],
            'relieving_date' => ['nullable', 'date'],

            'internship_start_date' => ['nullable', 'date'],
            'internship_end_date' => ['nullable', 'date', 'after_or_equal:internship_start_date'],
            'is_paid_intern' => ['nullable', Rule::in(['0', '1', 0, 1])],

            'probation_months' => ['nullable', 'integer', 'min:0'],
            'probation_status' => ['nullable', 'string'],
            'probation_duration_option' => ['nullable', Rule::in(['3_months', '6_months', 'custom'])],
            'probation_duration_type' => ['nullable', Rule::in(['months', 'days'])],
            'probation_duration_value' => ['nullable', 'integer', 'min:0'],
            'custom_duration_value' => ['nullable', 'integer', 'min:0'],
            'custom_duration_unit' => ['nullable', Rule::in(['months', 'days'])],
            'probation_start_date' => ['nullable', 'date'],
            'probation_end_date' => ['nullable', 'date'],
            'confirmation_date' => ['nullable', 'date'],
            'permanent_at' => ['nullable', 'date'],

            'punch_allowed_from' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'shift_start_time' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'late_after_time' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'half_day_after_time' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'block_after_time' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'shift_end_time' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'required_work_minutes' => ['required_if:work_schedule_type,flexible_part_time', 'nullable', 'integer'],
            'lunch_minutes' => ['required_if:work_schedule_type,flexible_part_time', 'nullable', 'integer'],

            'actual_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_effective_from' => ['nullable', 'date'],
            'salary_change_reason' => ['nullable', 'string', 'max:255'],

            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'address' => ['nullable', 'string'],
            'highest_qualification' => ['nullable', 'string'],
            'cgpa_percentage' => ['nullable', 'string'],
            'total_experience' => ['nullable', 'string'],
            'emergency_contact_number' => ['nullable', 'string', 'max:255'],

            'bank_account_no' => ['nullable', 'string'],
            'bank_account_type' => ['nullable', 'string'],
            'bank_holder_name' => ['nullable', 'string'],
            'ifsc_code' => ['nullable', 'string'],
            'bank_branch' => ['nullable', 'string'],

            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'resume_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }
}
