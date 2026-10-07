<?php

namespace App\Http\Requests\Web\HRMS\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InitiateExitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'exit_type' => ['required', Rule::in(['resignation', 'termination', 'discontinued', 'retirement', 'contract_end', 'mutual_separation', 'layoff_redundancy', 'absconding', 'deceased', 'other', 'internship_completed', 'internship_exit'])],
            'resignation_date' => ['nullable', 'date'],
            'termination_date' => ['nullable', 'date'],
            'last_working_day' => ['nullable', 'date'],
            'notice_period_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'notice_waived' => ['nullable', 'boolean'],
            'immediate_exit' => ['nullable', 'boolean'],
            'buyout_recovery' => ['nullable', 'boolean'],
            'immediate_disable_login' => ['nullable', 'boolean'],
        ];
    }
}
