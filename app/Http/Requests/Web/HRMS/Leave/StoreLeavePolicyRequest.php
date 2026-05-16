<?php

namespace App\Http\Requests\Web\HRMS\Leave;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeavePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'policy_name' => 'required|string|max:255',
            'annual_total_leaves' => 'required|numeric|min:0|max:366',
            'annual_paid_leaves' => 'required|numeric|min:0|max:366',
            'annual_sick_leaves' => 'required|numeric|min:0|max:366',
            'monthly_leave_limit' => 'required|numeric|min:0|max:31',
            'max_leave_at_once' => 'required|numeric|min:0|max:366',
            'probation_leave_limit' => 'required|numeric|min:0|max:31',
            'internship_leave_limit' => 'required|numeric|min:0|max:31',
            'medical_certificate_after_days' => 'required|integer|min:0|max:31',
            'nov_dec_threshold_balance' => 'required|numeric|min:0|max:366',
            'nov_dec_usage_percentage' => 'required|numeric|min:0|max:100',
            'rounding_method' => 'required|in:nearest,floor,ceil',
            'allow_monthly_balance_accumulation' => 'nullable|boolean',
            'carry_forward_enabled' => 'nullable|boolean',
            'sandwich_enabled' => 'nullable|boolean',
            'weekoff_included_in_sandwich' => 'nullable|boolean',
            'holiday_included_in_sandwich' => 'nullable|boolean',
            'nov_dec_half_usage_enabled' => 'nullable|boolean',
            'comp_off_expiry_same_month' => 'nullable|boolean',
            'allow_negative_balance' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }
}
