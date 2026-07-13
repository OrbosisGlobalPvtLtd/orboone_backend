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
        
        $isEmployee = false;
        if ($adminId && \Illuminate\Support\Facades\Schema::hasTable('employees_new')) {
            $isEmployee = \Illuminate\Support\Facades\DB::table('employees_new')->where('user_id', $adminId)->exists();
        }

        $roleIdsRule = ['array'];
        if (!$isEmployee) {
            $roleIdsRule[] = 'required_without:role_id';
            $roleIdsRule[] = 'min:1';
        }

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
            'role_ids' => $roleIdsRule,
            'role_ids.*' => ['integer', $adminRoleRule()],
            'is_active' => ['nullable', 'boolean'],
            'is_app_access' => ['nullable', 'boolean'],
        ];
    }
}
