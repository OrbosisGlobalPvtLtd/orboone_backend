@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Exit Employees')

@section('_content')
    @include('hrms.employee.partials.styles')
    @include('hrms.employee.exit.partials.styles')

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
                <div class="eo-toolbar">
                    <div class="eo-toolbar-left">
                        <div class="eo-entries-wrapper">
                            <span class="eo-entries-label">Show</span>
                            <select id="customLengthMenu" class="eo-entries-select">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="eo-entries-label">entries</span>
                        </div>
                    </div>
                    <div class="eo-toolbar-right d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="eo-export-btn js-export-csv">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                        <button type="button" class="eo-export-btn js-export-excel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="eo-export-btn js-export-pdf">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                        <button type="button" class="eo-export-btn js-export-print">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>

                @include('hrms.employee.exit.partials.table')

                <!-- Footer / Pagination (will be dynamically appended by DataTable drawCallback) -->
                <div class="exit-dt-footer"></div>
            </div>
        </div>
    </div>

    <!-- Modals declaration -->
    @include('hrms.employee.exit.partials.modal.main')

    <!-- Scripts -->
    @include('hrms.employee.exit.partials.scripts')
@endsection