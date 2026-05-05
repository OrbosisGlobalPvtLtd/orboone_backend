<?php

namespace App\Http\Requests\Web\HRMS\Leave;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeLeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->isMethod('POST')) {
            return [
                'employee_id' => 'required|exists:employees,id',
                'leave_type' => 'required|string',
                'from' => 'required|date',
                'to' => 'required|date|after_or_equal:from',
                'message' => 'required|string|max:1000',
            ];
        }

        return [
            'employee_id' => 'exists:employees,id|nullable',
            'from' => 'date|nullable',
            'to' => 'date|nullable|after_or_equal:from',
            'message' => 'nullable',
            'comment' => 'nullable',
            'checked_by' => 'nullable|exists:employees,id'
        ];
    }
}
