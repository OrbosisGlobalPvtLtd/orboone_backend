<?php

namespace App\Http\Requests\Web\HRMS\Leave;

use App\Services\HRMS\Leave\LeaveValidationService;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return LeaveValidationService::rules($this->all());
    }

    public function messages(): array
    {
        return LeaveValidationService::messages();
    }
}
