<?php

namespace App\Http\Requests\Web\HRMS\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $leaveTypeId = $this->route('leaveType') ?: $this->route('id');

        return [
            'name' => 'required|string|max:120',
            'code' => ['required', 'string', 'max:80', Rule::unique('leave_types', 'code')->ignore($leaveTypeId)],
            'is_paid' => 'nullable|boolean',
            'is_sick' => 'nullable|boolean',
            'is_lwp' => 'nullable|boolean',
            'is_comp_off' => 'nullable|boolean',
            'requires_attachment' => 'nullable|boolean',
            'medical_certificate_after_days' => 'nullable|integer|min:0|max:31',
            'max_days_per_month' => 'nullable|numeric|min:0|max:31',
            'max_days_per_request' => 'nullable|numeric|min:0|max:366',
            'allow_half_day' => 'nullable|boolean',
            'applicable_after_confirmation' => 'nullable|boolean',
            'color' => 'nullable|string|max:30',
            'is_active' => 'nullable|boolean',
        ];
    }
}
