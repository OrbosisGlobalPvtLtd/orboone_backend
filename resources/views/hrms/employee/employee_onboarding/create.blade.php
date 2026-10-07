@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Employee Onboarding')

@section('_head')
@include('hrms.employee.partials.styles')
@include('hrms.employee.employee_onboarding.partials.styles')
@endsection

@section('_content')
<div class="eo-page">
    <div class="eo-container">

        <div class="orb-page-header">
            <div class="orb-page-header-content">
                <div class="orb-page-kicker">
                    <i class="fas fa-user-plus"></i> HRMS &bull; Onboarding
                </div>
                <h1 class="orb-page-title">Employee Onboarding</h1>
                <p class="orb-page-subtitle">Create employee account, lifecycle, department, role and salary setup.</p>
            </div>
            <div class="orb-page-actions">
                <div class="orb-btn-light" style="pointer-events: none; opacity: 0.95;"><i class="fas fa-fingerprint mr-1"></i> Code: {{ $nextEmployeeCode }}</div>
            </div>
        </div>

        @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix these errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('hrms.employees.store') }}" method="POST" id="employeeOnboardingForm">
            @csrf

            @include('hrms.employee.employee_onboarding.partials.account-details')

            @include('hrms.employee.employee_onboarding.partials.employment-details')

            @include('hrms.employee.employee_onboarding.partials.internship-setup')

            @include('hrms.employee.employee_onboarding.partials.contract-setup')

            @include('hrms.employee.employee_onboarding.partials.access-salary')

            <div class="eo-actions-bar">
                <div class="eo-actions-note">
                    Employee profile will start as pending. HR can approve it later.
                </div>

                <div class="eo-actions">
                    <a href="{{ route('hrms.employees.index') }}" class="btn btn-soft">Cancel</a>

                    <button type="submit" name="action" value="save" class="btn btn-orb">
                        <i class="fas fa-save mr-1"></i> Create Employee
                    </button>

                    <!-- <button type="submit" name="action" value="save_profile" class="btn btn-profile">
                        <i class="fas fa-user-check mr-1"></i> Create & Complete Profile
                    </button> -->
                </div>
            </div>
        </form>
    </div>
</div>

@include('hrms.employee.employee_onboarding.partials.onboarding-script')
@endsection
