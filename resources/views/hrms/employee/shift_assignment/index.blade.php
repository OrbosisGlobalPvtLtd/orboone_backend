@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Shift Assignment')

@section('_content')
@include('hrms.employee.partials.styles')
@include('hrms.employee.shift_assignment.partials.styles')

<div class="shift-assignment-page">
    <div class="report-container">

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show rounded-12 shadow-sm mb-4" role="alert" style="border-left: 5px solid #10B981;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('status') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-12 shadow-sm mb-4" role="alert" style="border-left: 5px solid #EF4444;">
                <i class="fas fa-exclamation-triangle mr-2"></i> <strong>Validation Error:</strong>
                <ul class="mb-0 mt-1 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Sleek Premium Hero Header -->
        <div class="report-header-premium">
            <div class="title-area">
                <div class="header-kicker">
                    <i class="fas fa-users-cog"></i> EMPLOYEE MANAGEMENT
                </div>
                <h3>Employee Shift Assignments</h3>
                <p>Manage employee shift mappings, custom shift windows, and active effective date ranges.</p>
            </div>
            <div>
                <button type="button" class="report-btn-pill" data-toggle="modal" data-target="#assignShiftModal">
                    <i class="fas fa-plus"></i> Assign Shift
                </button>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="card orb-table-card">

            <div class="orb-table-card-header d-flex align-items-center justify-content-between" style="padding: 20px 24px 16px; border-bottom: 1px solid #EEF2F7; background: #fff; flex-wrap: wrap; gap: 16px;">
                <div class="orb-title-wrap d-flex align-items-center" style="gap: 14px;">
                    <span class="orb-card-icon" style="width: 42px; height: 42px; border-radius: 12px; background: #F4F2FF; color: var(--orb-primary, #6366F1); display: inline-flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fas fa-business-time"></i>
                    </span>
                    <div>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #101828;">Shift Assignments List</h3>
                        <p style="margin: 3px 0 0 0; font-size: 12.5px; color: #667085;">View and manage assigned shift timings, custom flexible windows, and effective ranges.</p>
                    </div>
                </div>

                <!-- Reset Filters Button in Card Header -->
                <a href="{{ route('employee.shift-assignment.index') }}" class="btn btn-undo btn-outline-secondary btn-sm d-flex align-items-center" style="height: 38px !important; border-radius: 10px !important; padding: 0 16px !important; font-size: 12.5px !important; font-weight: 700 !important; border: 1px solid #e2e8f0 !important; color: #475467 !important; background: #fff !important; transition: all 0.2s ease !important; text-decoration: none;">
                    <i class="fas fa-undo mr-2" style="font-size: 11px;"></i> Reset Filters
                </a>
            </div>

            <!-- Auto-Submitting Filter Grid Bar -->
            @include('hrms.employee.shift_assignment.partials.filters')

            <!-- Table Container -->
            @include('hrms.employee.shift_assignment.partials.table')

        </div>
    </div>
</div>

<!-- Assign Shift Modal -->
@include('hrms.employee.shift_assignment.partials.assign-modal')

<!-- Edit Shift Modals -->
@include('hrms.employee.shift_assignment.partials.edit-modal')

@endsection

@include('hrms.employee.shift_assignment.partials.scripts')
