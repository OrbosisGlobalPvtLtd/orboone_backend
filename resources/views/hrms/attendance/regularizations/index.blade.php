@extends('layouts.panel', [
    'accesses' => $accesses ?? [],
    'active' => $active ?? 'hrms'
])

@section('page_title', $pageTitle ?? 'Attendance Regularizations')

@section('_head')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<style>
:root {
    --orb-primary: #4B00E8;
    --orb-secondary: #8600EE;
    --orb-bg: #F6F7FB;
    --orb-border: #E7EAF3;
    --orb-text: #101828;
    --orb-muted: #667085;
    --orb-soft: #F4F2FF;
    --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
}

body {
    overflow-x: hidden !important;
}

.att-page {
    min-height: calc(100vh - 90px);
    background: var(--orb-bg);
    padding: 16px 12px 36px;
}

.att-container {
    max-width: 1480px;
    margin: 0 auto;
}

.att-hero {
    background: linear-gradient(135deg, #4B00E8 0%, #7600EC 55%, #9A00F5 100%);
    border-radius: 26px !important;
    padding: 30px;
    margin-bottom: 18px;
    box-shadow: 0 18px 45px rgba(75, 0, 232, .20);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    color: #fff;
    position: relative;
    overflow: hidden;
}

.att-hero:before {
    content: "";
    position: absolute;
    right: -80px;
    top: -110px;
    width: 360px;
    height: 360px;
    border-radius: 50%;
    background: rgba(255, 255, 255, .12);
    pointer-events: none;
}

.att-kicker {
    font-size: 12px;
    font-weight: 950;
    letter-spacing: .14em;
    text-transform: uppercase;
    opacity: .9;
    margin-bottom: 10px;
    display: flex;
    gap: 9px;
    align-items: center;
}

.att-title {
    font-size: 34px;
    font-weight: 950;
    margin: 0;
    line-height: 1.1;
    color: #fff;
}

.att-subtitle {
    font-size: 15px;
    font-weight: 650;
    margin-top: 10px;
    opacity: .92;
    max-width: 850px;
}

.att-hero-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

.att-btn {
    border-radius: 14px;
    padding: 13px 18px;
    font-weight: 950;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    text-decoration: none !important;
    white-space: nowrap;
    border: 0;
    cursor: pointer;
    transition: all 0.2s ease;
}

.att-btn-light {
    background: #fff;
    color: #101828 !important;
    box-shadow: 0 10px 22px rgba(16, 24, 40, .08);
}

.att-btn-light:hover {
    background: var(--orb-soft);
    color: var(--orb-primary) !important;
}

.att-card {
    background: #fff;
    border: 1px solid var(--orb-border);
    border-radius: 22px !important;
    box-shadow: var(--orb-shadow);
    overflow: hidden !important;
}

.att-section-head {
    padding: 18px 22px;
    border-bottom: 1px solid var(--orb-border);
    background: linear-gradient(180deg, #fff, #FAFBFF);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.att-section-title {
    font-size: 19px;
    font-weight: 950;
    color: var(--orb-text);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.att-section-title i {
    color: var(--orb-primary);
}

.att-section-sub {
    font-size: 13px;
    color: var(--orb-muted);
    font-weight: 650;
    margin-top: 4px;
}

.att-head-badges {
    display: flex;
    gap: 9px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.att-total-pill {
    border: 1px solid var(--orb-border);
    background: #F8FAFC;
    color: var(--orb-text);
    border-radius: 12px;
    padding: 9px 12px;
    font-size: 12px;
    font-weight: 950;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.att-total-pill.orange {
    border-color: #FED7AA;
    background: #FFF7ED;
    color: #C2410C;
}

.att-total-pill.purple {
    border-color: #E0D7FF;
    background: #F5F2FF;
    color: #4B00E8;
}

.att-filter-panel {
    padding: 16px 22px;
    border-bottom: 1px solid var(--orb-border);
    background: #fff;
}

.att-filter-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 12px;
}

.att-filter-grid label {
    font-size: 10px;
    font-weight: 950;
    color: #667085;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 6px;
    display: block;
}

.att-filter-grid .form-control,
.att-filter-grid .custom-select {
    height: 40px;
    border-radius: 14px;
    border: 1px solid #E4E7EC;
    font-size: 13px;
    font-weight: 750;
    padding: 0 14px;
    box-shadow: none !important;
    background: #fff;
}

.att-filter-grid .form-control:focus,
.att-filter-grid .custom-select:focus {
    border-color: var(--orb-primary);
}

.att-table-wrap {
    padding: 0 16px 16px;
}

.att-table-responsive {
    width: 100% !important;
    overflow: hidden !important;
}

.att-table {
    width: 100% !important;
    border-collapse: collapse !important;
}

.att-table thead th {
    background: #F8FAFC !important;
    color: #344054 !important;
    font-size: 10px !important;
    font-weight: 950 !important;
    text-transform: uppercase;
    padding: 14px 12px !important;
    border-top: 1px solid #EAECF0 !important;
    border-bottom: 1px solid #EAECF0 !important;
    white-space: nowrap;
}

.att-table td {
    background: #fff;
    border-bottom: 1px solid #EEF2F6 !important;
    padding: 14px 12px !important;
    vertical-align: middle;
    font-size: 13px;
    color: var(--orb-text);
}

.att-table tbody tr {
    transition: .2s ease;
}

.att-table tbody tr:hover td {
    background: #FAF8FF;
}

.orb-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    border: 1px solid transparent;
}

.orb-badge-success {
    background: #ECFDF3;
    color: #027A48;
    border-color: #ABEFC6;
}

.orb-badge-warning {
    background: #FFFAEB;
    color: #B54708;
    border-color: #FEDF89;
}

.orb-badge-danger {
    background: #FEF3F2;
    color: #B42318;
    border-color: #FECDCA;
}

.orb-badge-primary {
    background: var(--orb-soft);
    color: var(--orb-primary);
    border-color: rgba(75, 0, 232, .12);
}

.orb-action-btn {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid var(--orb-border);
    background: #fff;
    color: #667085;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.orb-action-btn:hover {
    background: var(--orb-soft);
    color: var(--orb-primary);
    border-color: #D9CCFF;
}

.dropdown-menu {
    border: 1px solid var(--orb-border);
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(16, 24, 40, .08);
    padding: 6px;
}

.dropdown-item {
    border-radius: 10px;
    font-size: 13px;
    font-weight: 800;
    padding: 8px 12px;
    color: #344054;
    transition: all 0.15s ease;
}

.dropdown-item:hover {
    background: var(--orb-soft);
    color: var(--orb-primary);
}

.dataTables_wrapper {
    width: 100% !important;
    overflow: hidden !important;
}

.dt-toolbar {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 10px !important;
    flex-wrap: nowrap !important;
    width: 100% !important;
    padding: 10px 16px !important;
    border-bottom: 1px solid var(--orb-border) !important;
    background: #fff !important;
    margin: 0 !important;
}

@media(max-width: 768px) {
    .dt-toolbar {
        flex-wrap: wrap !important;
    }
}

.dt-left,
.dt-right {
    display: flex !important;
    align-items: center !important;
}

.dt-right {
    justify-content: flex-end !important;
    gap: 6px !important;
    flex: 0 0 auto !important;
}

.dt-left {
    flex: 0 0 auto !important;
}

.dataTables_wrapper > .row:last-child {
    border-top: 1px solid var(--orb-border);
    padding: 12px 16px 0;
    margin: 12px -16px 0 !important;
}

.dataTables_scroll {
    width: 100% !important;
    border-radius: 18px;
    overflow: hidden !important;
    margin-top: 16px;
}

.dataTables_scrollHead {
    width: 100% !important;
    background: #F8FAFC;
    overflow: hidden !important;
}

.dataTables_scrollHeadInner,
.dataTables_scrollBody table {
    width: 100% !important;
}

.dataTables_scrollBody {
    width: 100% !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    border-bottom: 0 !important;
}

.dataTables_scrollBody::-webkit-scrollbar {
    height: 10px;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 20px;
}

.dataTables_wrapper .dt-buttons {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 6px !important;
}

.dataTables_wrapper .dt-buttons .btn {
    width: auto !important;
    min-width: auto !important;
    height: 32px !important;
    padding: 0 10px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    border-radius: 9px !important;
    white-space: nowrap !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    border: 1px solid #E7EAF3 !important;
    background: #fff !important;
    color: #101828 !important;
    transition: all 0.2s ease !important;
    box-shadow: none !important;
    margin: 0 !important;
}

.dataTables_wrapper .dt-buttons .btn:hover {
    background: #F4F2FF !important;
    color: #4B00E8 !important;
    border-color: #4B00E8 !important;
}

.dataTables_length {
    margin-bottom: 0 !important;
}

.dataTables_length label {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    color: var(--orb-text) !important;
    margin-bottom: 0 !important;
}

.dataTables_length select {
    height: 32px !important;
    padding: 0 24px 0 8px !important;
    border-radius: 9px !important;
    font-size: 12px !important;
    border: 1px solid #E7EAF3 !important;
    background: #fff !important;
    font-weight: 600 !important;
}

.dataTables_info {
    color: var(--orb-muted);
    font-weight: 700;
}

.page-link {
    border-radius: 10px;
    margin: 0 2px;
    border-color: var(--orb-border);
    color: var(--orb-primary);
    font-weight: 800;
}

.page-item.active .page-link {
    background: var(--orb-primary) !important;
    border-color: var(--orb-primary) !important;
    color: #fff !important;
}

.orb-empty {
    padding: 44px 18px !important;
    text-align: center;
    color: var(--orb-muted);
}

.orb-empty i {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--orb-soft);
    color: var(--orb-primary);
    font-size: 20px;
    margin-bottom: 12px;
}

/* MODAL STANDARD OVERRIDE */
.orb-modal .modal-content {
    border: 0;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
}

.orb-modal .modal-header {
    background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
    border: 0;
    color: #fff;
    padding: 18px 24px;
}

.orb-modal .modal-title {
    font-weight: 950;
    color: #fff;
}

.orb-modal .modal-body {
    background: #fff;
    padding: 24px;
}

.orb-modal .modal-footer {
    background: #F8FAFC;
    border-top: 1px solid #EEF2F6;
    padding: 16px 24px;
}

.orb-modal .form-control,
.orb-modal .custom-select {
    border-radius: 14px;
    border: 1px solid var(--orb-border);
    min-height: 44px;
    font-size: 13px;
}

@media(max-width:1300px) {
    .att-filter-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media(max-width:768px) {
    .att-page {
        padding: 12px 8px 25px;
    }

    .att-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 22px;
        border-radius: 24px;
    }

    .att-title {
        font-size: 25px;
    }

    .att-hero-actions {
        width: 100%;
    }

    .att-btn {
        width: 100%;
    }

    .att-section-head {
        flex-direction: column;
    }

    .att-head-badges {
        justify-content: flex-start;
    }

    .att-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media(max-width:480px) {
    .att-filter-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('_content')
<div class="att-page">
    <div class="att-container">

        <div class="att-hero">
            <div>
                <div class="att-kicker"><i class="fas fa-calendar-check"></i> HRMS &bull; ATTENDANCE</div>
                <h3 class="att-title">{{ $pageTitle ?? 'Attendance Regularizations' }}</h3>
                <div class="att-subtitle">{{ $pageSubtitle ?? 'Manage missed punch, correction and regularization requests.' }}</div>
            </div>
            <div class="att-hero-actions">
                @if(!empty($canCreate))
                <button type="button" class="att-btn att-btn-light text-primary" data-toggle="modal" data-target="#createModal" style="color: var(--orb-primary) !important; font-weight: 950 !important;">
                    <i class="fas fa-plus"></i> Add New
                </button>
                @endif
            </div>
        </div>

        @if(session('success') || session('status'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') ?: session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
        @endif

        <div class="att-card">
            <div class="att-section-head">
                <div>
                    <h5 class="att-section-title"><i class="fas fa-history"></i> Regularization Logs</h5>
                    <div class="att-section-sub">Track correction requests, employee submissions, and approval status logs.</div>
                </div>
                <div class="att-head-badges align-items-center">
                    <span class="att-total-pill purple"><i class="fas fa-list"></i> Total Requests: {{ optional($rows)->total() ?? collect($rows)->count() }}</span>
                </div>
            </div>

            @if(!empty($filters))
            <div class="att-filter-panel">
                <form method="GET" id="filterForm">
                    <div class="att-filter-grid align-items-end">
                        @foreach($filters as $filter)
                            <div>
                                <label>{{ $filter['label'] }}</label>
                                @if(($filter['type'] ?? 'text') === 'select')
                                    <select name="{{ $filter['name'] }}" class="form-control js-auto-filter">
                                        <option value="">{{ $filter['placeholder'] ?? 'All' }}</option>
                                        @foreach($filter['options'] as $value => $label)
                                            <option value="{{ $value }}" {{ (string) request($filter['name']) === (string) $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $filter['type'] ?? 'text' }}" name="{{ $filter['name'] }}" value="{{ request($filter['name']) }}" class="form-control js-auto-filter" placeholder="{{ $filter['placeholder'] ?? '' }}">
                                @endif
                            </div>
                        @endforeach
                        <div>
                            <a href="{{ url()->current() }}" class="att-btn att-btn-light w-100 justify-content-center" style="height: 40px !important; border-radius: 14px !important; font-size: 13px !important; font-weight: 800 !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; border: 1px solid #E4E7EC !important;">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            <div class="att-table-wrap">
                <div class="att-table-responsive" style="overflow-x: auto;">
                    <table class="att-table table table-hover js-orb-datatable" id="regularizationDataTable">
                        <thead>
                            <tr>
                                <th style="width: 80px;">S.No.</th>
                                @foreach($columns as $column)
                                    <th>{{ $column['label'] }}</th>
                                @endforeach
                                @if(!empty($rowActions) || !empty($canEdit) || !empty($canDelete))
                                    <th class="text-right no-export" style="width: 100px;">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td><strong>{{ (($rows->currentPage() - 1) * $rows->perPage()) + $loop->iteration }}</strong></td>
                                    @foreach($columns as $column)
                                        @php
                                            $value = data_get($row, $column['key']);
                                        @endphp
                                        <td>
                                            @if(($column['type'] ?? '') === 'badge')
                                                @php
                                                    $badge = in_array($value, ['approved','active','earned','processed',1,true], true)
                                                        ? 'orb-badge-success'
                                                        : (in_array($value, ['pending','unprocessed'], true)
                                                            ? 'orb-badge-warning'
                                                            : (in_array($value, ['rejected','cancelled','expired','inactive',0,false], true)
                                                                ? 'orb-badge-danger'
                                                                : 'orb-badge-primary'));
                                                @endphp
                                                <span class="orb-badge {{ $badge }}">
                                                    {{ is_bool($value) ? ($value ? 'Active' : 'Inactive') : ucfirst((string) $value) }}
                                                </span>
                                            @elseif(($column['type'] ?? '') === 'date' && $value)
                                                {{ \Carbon\Carbon::parse($value)->format('d M Y') }}
                                            @elseif(($column['type'] ?? '') === 'datetime' && $value)
                                                {{ \Carbon\Carbon::parse($value)->format('d M Y h:i A') }}
                                            @elseif(($column['type'] ?? '') === 'json')
                                                <pre class="mb-0 small" style="max-width:360px;white-space:pre-wrap">{{ json_encode(is_string($value) ? json_decode($value, true) : $value, JSON_PRETTY_PRINT) }}</pre>
                                            @else
                                                {{ $value ?? '-' }}
                                            @endif
                                        </td>
                                    @endforeach

                                    @if(!empty($rowActions) || !empty($canEdit) || !empty($canDelete))
                                        <td class="text-right no-export">
                                            <div class="dropdown">
                                                <button class="orb-action-btn" type="button" data-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @if(!empty($canEdit))
                                                        <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ data_get($row, 'id') }}">
                                                            <i class="fas fa-edit mr-2 text-primary"></i> Edit
                                                        </button>
                                                    @endif

                                                    @foreach($rowActions ?? [] as $action)
                                                        <form method="POST" action="{{ route($action['route'], data_get($row, 'id')) }}" onsubmit="return confirm('{{ $action['confirm'] ?? 'Continue?' }}')">
                                                            @csrf
                                                            <button class="dropdown-item" type="submit">
                                                                <i class="{{ $action['icon'] ?? 'fas fa-check' }} mr-2"></i> {{ $action['label'] }}
                                                            </button>
                                                        </form>
                                                    @endforeach

                                                    @if(!empty($canDelete))
                                                        <form method="POST" action="{{ route($deleteRoute, data_get($row, 'id')) }}" onsubmit="return confirm('Delete this record?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="dropdown-item text-danger" type="submit">
                                                                <i class="fas fa-trash mr-2"></i> Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($rows, 'links'))
                <div class="mt-3 px-3 pb-3">
                    {{ $rows->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        @if(!empty($canCreate))
            @include('hrms.shared.crud-modal', [
                'modalId' => 'createModal',
                'modalTitle' => 'Add '.$pageTitle,
                'action' => route($storeRoute),
                'method' => 'POST',
                'fields' => $formFields,
                'row' => null
            ])
        @endif

        @if(!empty($canEdit))
            @foreach($rows as $row)
                @include('hrms.shared.crud-modal', [
                    'modalId' => 'editModal'.data_get($row, 'id'),
                    'modalTitle' => 'Edit '.$pageTitle,
                    'action' => route($updateRoute, data_get($row, 'id')),
                    'method' => 'PUT',
                    'fields' => $formFields,
                    'row' => $row
                ])
            @endforeach
        @endif

    </div>
</div>
@endsection

@section('_script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-auto-filter').forEach(function (input) {
        input.addEventListener('change', function () {
            document.getElementById('filterForm').submit();
        });

        if (input.tagName === 'INPUT') {
            let timeout = null;
            input.addEventListener('keyup', function () {
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    document.getElementById('filterForm').submit();
                }, 500);
            });
        }
    });

    if (window.jQuery && $.fn.DataTable) {
        if ($.fn.DataTable.isDataTable('#regularizationDataTable')) {
            $('#regularizationDataTable').DataTable().destroy();
        }

        $('#regularizationDataTable').DataTable({
            destroy: true,
            paging: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            searching: false,
            lengthChange: true,
            info: true,
            responsive: false,
            autoWidth: false,
            order: [],
            scrollX: true,
            scrollCollapse: true,
            dom: "<'dt-toolbar'lB>rt<'row align-items-center mt-3 px-3 pb-3'<'col-md-5'i><'col-md-7'p>>",
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-light border',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-light border',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-light border',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    title: 'Orbosis HRMS Attendance Regularizations',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-light border',
                    title: 'Orbosis HRMS Attendance Regularizations',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    }
                }
            ],
            language: {
                emptyTable: 'No regularization records found.'
            }
        });

        setTimeout(function() {
            $('#regularizationDataTable').DataTable().columns.adjust();
        }, 250);
    }
});
</script>
@endsection
