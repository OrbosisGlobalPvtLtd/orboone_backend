<?php

namespace App\Http\Requests\Web\HRMS\Leave;

use Illuminate\Foundation\Http\FormRequest;

class RejectLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|min:3|max:2000',
            'remark' => 'nullable|string|max:2000',
            'admin_remark' => 'nullable|string|max:2000',
        ];
    }
}
