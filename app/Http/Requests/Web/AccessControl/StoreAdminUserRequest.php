<?php

namespace App\Http\Requests\Web\AccessControl;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminUserRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $adminId = $this->route('admin');
        $passwordRule = $this->isMethod('post') ? ['required', 'string', 'min:8'] : ['nullable', 'string', 'min:8'];
        $adminRoleRule = function () {
            return Rule::exists('roles', 'id')->where(function ($query) {
                $query->where('slug', '!=', 'employee');
            });
        };

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($adminId),
            ],
            'password' => $passwordRule,
            'role_id' => ['nullable', 'integer', $adminRoleRule()],
            'role_ids' => ['required_without:role_id', 'array', 'min:1'],
            'role_ids.*' => ['integer', $adminRoleRule()],
            'is_active' => ['nullable', 'boolean'],
            'is_app_access' => ['nullable', 'boolean'],
        ];
    }
}
