<?php

namespace App\Http\Requests\Web\HRMS\Leave;

use Illuminate\Foundation\Http\FormRequest;

class ApproveLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => 'nullable|string|max:2000',
            'remark' => 'nullable|string|max:2000',
            'admin_remark' => 'nullable|string|max:2000',
        ];
    }
}
