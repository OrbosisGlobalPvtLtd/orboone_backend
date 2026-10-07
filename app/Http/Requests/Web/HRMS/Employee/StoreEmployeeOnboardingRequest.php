<?php

namespace App\Http\Requests\Web\HRMS\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreEmployeeOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'intern', 'freelancer', 'contract'])],
            'work_mode' => ['required', Rule::in(['wfo', 'wfh', 'hybrid'])],
            'work_schedule_type' => ['nullable', Rule::in(['full_day', 'part_day', 'hourly', 'shift_based', 'general', 'general_shift', 'wfh', 'wfh_shift', 'part_time', 'part_time_shift', 'part_time_morning', 'part_time_evening', 'half_day', 'half_day_shift', 'half_day_morning', 'half_day_evening', 'flexible_part_time', 'dynamic_hours'])],
            'department_id' => ['required', 'exists:departments,id'],
            'designation_id' => ['required', Rule::exists('designations', 'id')->where(function ($query) {
                if ($this->filled('department_id')) {
                    $query->where('department_id', $this->department_id);
                }
            })],
            'reporting_manager_employee_id' => ['nullable', 'exists:employees_new,id'],
            'system_role_id' => ['required', 'exists:roles,id'],
            'actual_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_effective_from' => ['nullable', 'date'],
            'salary_change_reason' => ['nullable', 'string', 'max:255'],
            'punch_allowed_from' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'shift_start_time' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'late_after_time' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'half_day_after_time' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'block_after_time' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'shift_end_time' => ['required_if:work_schedule_type,flexible_part_time', 'nullable'],
            'required_work_minutes' => ['required_if:work_schedule_type,flexible_part_time', 'nullable', 'integer'],
            'lunch_minutes' => ['required_if:work_schedule_type,flexible_part_time', 'nullable', 'integer'],
        ];
    }
}
