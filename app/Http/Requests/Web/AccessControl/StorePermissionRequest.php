<?php

namespace App\Http\Requests\Web\AccessControl;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $permissionId = $this->route('permission');

        return [
            'name' => ['required', 'string', 'max:100'],
            'module_key' => ['required', 'string', 'max:100'],
            'submodule' => ['nullable', 'string', 'max:100'],
            'permission_key' => [
                'required',
                'string',
                'max:150',
                'regex:/^[A-Za-z0-9_.-]+$/',
                Rule::unique('permissions', 'key')->ignore($permissionId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
