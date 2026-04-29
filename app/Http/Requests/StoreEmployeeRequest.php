<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
        return [
            // USER
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role_id' => 'required|exists:roles,id',

            // EMPLOYEE
            'start_of_contract' => 'required|date',
            'end_of_contract'   => 'nullable|date|after_or_equal:start_of_contract', 
            'department_id'     => 'required|exists:departments,id',
            'position_id'       => 'required|exists:positions,id',
            'employment_type'   => 'required|in:Intern,Full-Time,Contract,Freelancer',
            'employee_status'   => 'required|in:WFH,WFO',
            'employment_status' => 'required|in:Active,Resigned,Terminated',
            'manager_id'        => 'nullable|exists:employees,id',
            
            // Allow salary and banking fields to be nullable
            'actual_salary'     => 'nullable|numeric|min:0',
            'bank_name'         => 'nullable|string',
            'account_number'    => 'nullable|string',
            'account_type'      => 'nullable|in:Savings,Current',
            'holder_name'       => 'nullable|string',
            'ifsc'              => 'nullable|string',
            'branch'            => 'nullable|string',

            // EMPLOYEE DETAIL
            'gender'            => 'required|in:M,F,O',
            'date_of_birth'     => 'nullable|date',
            'phone'             => 'required|string',
            'emergency_contact_number' => 'nullable|string',
            'address'           => 'nullable|string',

            'photo'             => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
            'cv'                => 'nullable|mimes:pdf,doc,docx|max:5120',

            'last_education'    => 'nullable|string',
            'gpa'               => 'nullable|string',
            'work_experience_in_years' => 'nullable|numeric',
        ];
    }

}
