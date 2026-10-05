@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Exit Employees')

@section('_head')
    @include('hrms.employee.partials.styles')
    @include('hrms.employee.exit.partials.styles')
@endsection

@section('_content')
    <div class="eo-page">
        <div class="eo-container">
            @include('hrms.employee.exit.partials.header')

            @php
            $departments = \DB::table('departments')->orderBy('name')->pluck('name');
            if ($departments->isEmpty()) {
                $departments = $employees->pluck('department_name')->filter()->unique()->sort();
            }
            $statuses = $employees->pluck('employment_status')->filter()->unique()->sort();
            $exitTypes = $employees->pluck('exit_type')->filter()->unique()->sort();
            $assetStatuses = $employees->pluck('asset_handover_status')->filter()->unique()->sort();
            $fnfStatuses = $employees->pluck('fnf_status')->filter()->unique()->sort();
            @endphp

            <div class="eo-card exit-table-card">
                <!-- Premium Card Header -->
                <div class="eo-card-header-premium">
                    <div class="eo-card-header-left">
                        <div class="eo-header-icon-circle">
                            <i class="fas fa-door-open"></i>
                        </div>
                        <div>
                            <h5 class="eo-card-title-premium">Exit Employee Records</h5>
                            <p class="eo-card-subtitle-premium">Manage exit lifecycle, clearance status, FNF, assets and documents.</p>
                        </div>
                    </div>
                </div>

                @include('hrms.employee.exit.partials.filters')

                <!-- Entries Toolbar -->
                <div class="orb-table-tools-bar eo-toolbar">
                    <div id="exitLengthBox" class="orb-table-length-box eo-toolbar-left"></div>
                    <div id="exitExportButtons" class="orb-table-export-buttons eo-toolbar-right">
                        <x-ui.export-buttons table="exitEmployeesTable" />
                    </div>
                </div>

                @include('hrms.employee.exit.partials.table')

                <!-- Footer / Pagination -->
                <div class="eo-table-footer orb-pagination-wrapper">
                    <div id="exitInfoBox" class="orb-pagination-info"></div>
                    <div id="exitPaginationBox"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals declaration -->
    @include('hrms.employee.exit.partials.modal.main')
@endsection

@section('_script')
    @include('hrms.employee.exit.partials.scripts')
@endsection