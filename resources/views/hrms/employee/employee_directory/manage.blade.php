@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Manage Employee')

@section('_head')
@include('hrms.employee.employee_directory.partials.manage.styles')
@endsection

@section('_content')
@php
$name = $employeeData->name ?? 'Employee';
$initial = strtoupper(substr($name, 0, 1));
$isCompleted = (int) ($employeeData->is_profile_completed ?? 0) === 1;

$employmentStatus = strtolower($employeeData->employment_status ?? 'active');
$profileStatus = strtolower($employeeData->profile_status ?? 'pending');
$stage = strtolower($employeeData->employee_stage ?? 'probation');
$isPermanent = (int) ($employeeData->is_permanent ?? 0) === 1 || $stage === 'permanent';

$employmentBadgeClass = match ($employmentStatus) {
    'active' => 'em-badge-success',
    'resigned' => 'em-badge-warning',
    'terminated', 'inactive' => 'em-badge-danger',
    default => '',
};

$profileBadgeClass = match ($profileStatus) {
    'approved' => 'em-badge-success',
    'submitted' => 'em-badge-info',
    'rejected' => 'em-badge-danger',
    default => 'em-badge-warning',
};

$stageBadgeClass = match ($stage) {
    'internship' => 'em-badge-info',
    'probation' => 'em-badge-warning',
    'permanent' => 'em-badge-success',
    'contract', 'freelance' => 'em-badge-info',
    default => '',
};

$internshipStatus = strtolower($employeeData->internship_status ?? '');
$approvedAt = !empty($employeeData->approved_at) ? \Carbon\Carbon::parse($employeeData->approved_at)->format('d M Y') : null;

$fileUrl = function ($path) {
    return !empty($path) && Route::has('hrms.documents.file')
        ? route('hrms.documents.file', $path)
        : '#';
};

$employeeDocuments = $employeeDocuments ?? collect();

$user = auth()->user();
$canSeeSalary = false;
if ($user) {
    $canSeeSalary = $user->hasRole('super_admin')
        || $user->hasRole('Super Admin')
        || $user->hasRole('hr_admin')
        || $user->hasRole('hr')
        || $user->hasRole('admin')
        || $user->hasRole('finance_admin')
        || $user->can('hrms.employees.salary')
        || $user->can('employees.salary')
        || $user->can('salary.view')
        || $user->can('payroll.view')
        || $user->can('employees.edit')
        || $user->can('hrms.employees.edit');
}
@endphp

<div class="em-page">
    <div class="em-container">

        @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        @if (session('warning'))
        <div class="alert alert-warning border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;background-color:#fff3cd;color:#856404;border-color:#ffeeba;">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('hrms.employees.manage.update', $employeeData->id) }}"
            enctype="multipart/form-data" id="employeeManageForm">
            @csrf
            @method('PUT')

            @include('hrms.employee.employee_directory.partials.manage.header')

            <div class="em-layout">
                {{-- Card A: Employee Details --}}
                <div class="em-card" id="cardA">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-user-tie mr-2"></i>Employee Details</h5>
                            <div class="em-card-sub">Basic, job, lifecycle and salary setup</div>
                        </div>
                        <div class="card-header-actions">
                            <button type="button" class="btn edit-sec-btn" data-section="cardA"><i class="fas fa-edit mr-1"></i>Edit</button>
                            <button type="button" class="btn manage-action-btn manage-btn-cancel cancel-sec-btn" data-section="cardA" style="display: none;"><i class="fas fa-times mr-1"></i>Cancel</button>
                            <button type="button" class="btn manage-action-btn manage-btn-save save-sec-btn" data-section="cardA" style="display: none;"><i class="fas fa-save mr-1"></i>Save Changes</button>
                        </div>
                    </div>

                    <div class="em-card-body">
                        @include('hrms.employee.employee_directory.partials.manage.employee-details.basic-information')
                        @include('hrms.employee.employee_directory.partials.manage.employee-details.job-details')
                        @include('hrms.employee.employee_directory.partials.manage.employee-details.employment-lifecycle')
                        @include('hrms.employee.employee_directory.partials.manage.employee-details.flexible-timing')
                        @include('hrms.employee.employee_directory.partials.manage.employee-details.probation-permanent')
                        @include('hrms.employee.employee_directory.partials.manage.employee-details.internship-details')
                        @include('hrms.employee.employee_directory.partials.manage.employee-details.contract-freelance')
                        @include('hrms.employee.employee_directory.partials.manage.employee-details.salary-update')
                    </div>
                </div>

                {{-- Card B: Profile Details --}}
                <div class="em-card" id="cardB">
                    <div class="em-card-head">
                        <div>
                            <h5 class="em-card-title"><i class="fas fa-id-card mr-2"></i>Profile Details</h5>
                            <div class="em-card-sub">Personal, education, experience and bank details</div>
                        </div>
                        <div class="card-header-actions">
                            <button type="button" class="btn edit-sec-btn" data-section="cardB"><i class="fas fa-edit mr-1"></i>Edit</button>
                            <button type="button" class="btn manage-action-btn manage-btn-cancel cancel-sec-btn" data-section="cardB" style="display: none;"><i class="fas fa-times mr-1"></i>Cancel</button>
                            <button type="button" class="btn manage-action-btn manage-btn-save save-sec-btn" data-section="cardB" style="display: none;"><i class="fas fa-save mr-1"></i>Save Changes</button>
                        </div>
                    </div>

                    <div class="em-card-body">
                        @include('hrms.employee.employee_directory.partials.manage.profile-details.personal-information')
                        @include('hrms.employee.employee_directory.partials.manage.profile-details.education-experience')
                        @include('hrms.employee.employee_directory.partials.manage.profile-details.bank-details')
                        @include('hrms.employee.employee_directory.partials.manage.profile-details.profile-approval')
                    </div>
                </div>

                {{-- History Cards --}}
                @include('hrms.employee.employee_directory.partials.manage.history.shift-history')
                @include('hrms.employee.employee_directory.partials.manage.history.salary-history')

                {{-- Documents Card --}}
                @include('hrms.employee.employee_directory.partials.manage.documents.documents-list')
            </div>
        </form>

        @include('hrms.employee.employee_directory.partials.manage.documents.document-upload-forms')
    </div>
</div>

@include('hrms.employee.employee_directory.partials.manage.scripts')
@endsection