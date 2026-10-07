@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'View Employee Profile')

@section('_head')
@include('hrms.employee.partials.styles')
@endsection

@section('_content')
@include('hrms.employee.profile.partials.view.styles')

@php
$isFullEditMode = request()->boolean('edit');
$isDocOnlyEditMode = request()->boolean('doc_edit') && ! $isFullEditMode;
$isDocEditMode = $isFullEditMode || $isDocOnlyEditMode;
$isProfileEditMode = $isFullEditMode;

$initial = strtoupper(substr($profile->name ?? 'E', 0, 1));
$status = $profile->profile_status ?? 'pending';

$statusClass = match($status) {
    'submitted' => 'status-submitted',
    'approved' => 'status-approved',
    'rejected' => 'status-rejected',
    default => 'status-pending',
};

$statusText = match($status) {
    'submitted' => 'Submitted For Review',
    'approved' => 'Completed / Approved',
    'rejected' => 'Rejected',
    default => 'Pending',
};

$documents = $documents ?? collect();
$verifiedDocs = $documents->where('verification_status', 'verified')->count();
$pendingDocs = $documents->where('verification_status', 'pending')->count();
$rejectedDocs = $documents->where('verification_status', 'rejected')->count();
@endphp

<div class="profile-page">
    <div class="profile-container">

        @if(session('success'))
        <div class="alert alert-success rounded-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger rounded-4">{{ session('error') }}</div>
        @endif

        {{-- Top Hero Banner --}}
        <div class="profile-hero">
            <div class="profile-main">
                <div class="profile-avatar">
                    @if (!empty($profile->profile_image) && Route::has('hrms.documents.file'))
                    <img src="{{ route('hrms.documents.file', $profile->profile_image) }}" alt="Profile">
                    @else
                    {{ $initial }}
                    @endif
                </div>

                <div>
                    <h2 class="profile-name">{{ $profile->name ?? '-' }}</h2>
                    <div class="profile-meta"><i class="fas fa-id-badge mr-1"></i>{{ $profile->employee_code ?? '-' }}</div>
                    <div class="profile-meta"><i class="fas fa-envelope mr-1"></i>{{ $profile->email ?? '-' }}</div>
                    <div class="profile-meta">
                        <i class="fas fa-building mr-1"></i>
                        {{ $profile->department_name ?? '-' }}
                        @if(!empty($profile->designation_name))
                        • {{ $profile->designation_name }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="status-panel">
                <div class="status-label">Profile Status</div>

                <div class="status-badge {{ $statusClass }}">
                    <i class="fas fa-circle"></i>
                    {{ $statusText }}
                </div>

                <div class="profile-actions">
                    <a href="{{ route('hrms.employees.pending_profiles') }}" class="btn-soft">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    @if($isFullEditMode)
                    <button type="submit" form="profileInlineForm" class="btn-orb">
                        <i class="fas fa-save"></i> Save
                    </button>

                    <a href="{{ route('hrms.employees.profile.view', $profile->employee_id) }}" class="btn-soft">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    @else
                    <a href="{{ request()->fullUrlWithQuery(['edit' => 1]) }}" class="btn-orb">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Inline Form (Personal, Education/Experience & Bank Details) --}}
        @include('hrms.employee.profile.partials.view.details-form')

        {{-- Documents, Review & Preview Modal --}}
        @include('hrms.employee.profile.partials.view.documents-section')

    </div>
</div>

{{-- Scripts --}}
@include('hrms.employee.profile.partials.view.scripts')
@endsection
