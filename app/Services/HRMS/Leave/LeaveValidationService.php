<?php

namespace App\Services\HRMS\Leave;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeaveValidationService
{
    /**
     * Get validation rules for Leave Application (shared by Web & Mobile API).
     */
    public static function rules(array $data = []): array
    {
        $isHalfDay = filter_var($data['is_half_day'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return [
            'employee_id' => 'nullable|exists:employees_new,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'nullable|boolean',
            'half_day_type' => [
                Rule::requiredIf($isHalfDay),
                'nullable',
                'in:first_half,second_half',
            ],
            'reason' => 'required|string|max:2000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'emergency_leave' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom error messages for Leave Application validation.
     */
    public static function messages(): array
    {
        return [
            'half_day_type.required' => 'Please select First Half or Second Half.',
            'half_day_type.required_if' => 'Please select First Half or Second Half.',
            'half_day_type.in' => 'Please select First Half or Second Half.',
        ];
    }

    /**
     * Validate raw request payload and return sanitized validated data.
     * Throws ValidationException with standard error message if validation fails.
     */
    public static function validate(array $data): array
    {
        $validator = Validator::make($data, self::rules($data), self::messages());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return self::sanitizePayload($validator->validated());
    }

    /**
     * Sanitize validated data:
     * - If is_half_day is false, ensure half_day_type is set to NULL.
     * - If is_half_day is true, ensure half_day_type is strictly first_half or second_half.
     */
    public static function sanitizePayload(array $data): array
    {
        $isHalfDay = filter_var($data['is_half_day'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($isHalfDay) {
            $data['is_half_day'] = true;
            $data['half_day_type'] = strtolower((string) ($data['half_day_type'] ?? ''));
            if (! in_array($data['half_day_type'], ['first_half', 'second_half'], true)) {
                throw ValidationException::withMessages([
                    'half_day_type' => ['Please select First Half or Second Half.'],
                ]);
            }
        } else {
            $data['is_half_day'] = false;
            $data['half_day_type'] = null;
        }

        return $data;
    }
}
