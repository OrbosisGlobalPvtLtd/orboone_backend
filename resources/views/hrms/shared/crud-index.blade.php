@extends('layouts.panel', [
'accesses' => $accesses ?? [],
'active' => $active ?? 'hrms'
])

@section('_head')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body {
        background: var(--orb-bg);
    }

    .orb-page {
        padding: 8px 0 28px;
    }

    /* HERO */

    .orb-hero {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, .24), transparent 30%),
            linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        border-radius: 24px;
        padding: 22px 24px;
        color: #fff;
        box-shadow: 0 20px 45px rgba(75, 0, 232, .22);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .orb-hero::after {
        content: '';
        position: absolute;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        right: -90px;
        bottom: -110px;
        background: rgba(255, 255, 255, .10);
    }

    .orb-hero-content {
        position: relative;
        z-index: 2;
    }

    .orb-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .14);
        color: rgba(255, 255, 255, .92);
        font-size: 11px;
        font-weight: 900;
        margin-bottom: 10px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .orb-hero h1 {
        font-size: 28px;
        font-weight: 950;
        margin: 0;
        letter-spacing: -.03em;
        color: #fff;
    }

    .orb-hero p {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: 13px;
        line-height: 1.6;
        max-width: 760px;
    }

    /* BUTTONS */

    .orb-btn {
        border: 0;
        border-radius: 14px;
        height: 42px;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all .2s ease;
        cursor: pointer;
    }

    .orb-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .orb-btn-primary {
        background: #fff;
        color: var(--orb-primary);
        box-shadow: 0 12px 24px rgba(16, 24, 40, .12);
    }

    .orb-btn-gradient {
        background: linear-gradient(135deg, var(--orb-primary), var(--orb-secondary));
        color: #fff;
        box-shadow: 0 14px 30px rgba(75, 0, 232, .18);
    }

    .orb-btn-light {
        background: #fff;
        color: var(--orb-text);
        border: 1px solid var(--orb-border);
    }

    .orb-btn-light:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    /* CARDS */

    .orb-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 24px;
        box-shadow: var(--orb-shadow);
        margin-bottom: 18px;
        overflow: hidden;
    }

    .orb-card-body {
        padding: 18px;
    }

    /* SUMMARY */

    .orb-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 18px;
    }

    .orb-summary-card {
        position: relative;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        padding: 16px;
        box-shadow: var(--orb-shadow);
    }

    .orb-summary-card::after {
        content: '';
        position: absolute;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        right: -26px;
        bottom: -26px;
        background: rgba(75, 0, 232, .06);
    }

    .orb-summary-label {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--orb-muted);
        font-weight: 900;
        margin-bottom: 10px;
        letter-spacing: .04em;
    }

    .orb-summary-value {
        font-size: 28px;
        font-weight: 950;
        color: var(--orb-text);
        line-height: 1;
    }

    /* FILTER */

    .orb-filter .form-control,
    .orb-filter .custom-select {
        border-radius: 14px;
        border: 1px solid var(--orb-border);
        height: 44px;
        font-size: 13px;
        font-weight: 700;
        color: var(--orb-text);
        box-shadow: none !important;
    }

    .orb-filter label,
    .orb-form-label {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--orb-muted);
        font-weight: 900;
        margin-bottom: 7px;
        letter-spacing: .04em;
    }

    .orb-filter .form-control:focus {
        border-color: rgba(75, 0, 232, .28);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    /* TABLE */

    .orb-table-wrap {
        overflow-x: auto;
    }

    .orb-table {
        width: 100% !important;
        min-width: 1100px;
        margin: 0 !important;
    }

    .orb-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #F9FAFB;
        border-top: 0 !important;
        border-bottom: 1px solid var(--orb-border) !important;
        color: #475467;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
        padding: 14px 12px;
    }

    .orb-table tbody td {
        vertical-align: middle !important;
        white-space: nowrap;
        padding: 14px 12px !important;
        border-color: #F2F4F7 !important;
        font-size: 13px;
        font-weight: 600;
        color: var(--orb-text);
    }

    .orb-table tbody tr {
        transition: all .15s ease;
    }

    .orb-table tbody tr:hover {
        background: #FAFAFF;
    }

    /* BADGE */

    .orb-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .orb-badge-success {
        background: #ECFDF3;
        color: #027A48;
        border: 1px solid #ABEFC6;
    }

    .orb-badge-warning {
        background: #FFFAEB;
        color: #B54708;
        border: 1px solid #FEDF89;
    }

    .orb-badge-danger {
        background: #FEF3F2;
        color: #B42318;
        border: 1px solid #FECDCA;
    }

    .orb-badge-muted {
        background: #F2F4F7;
        color: #475467;
        border: 1px solid #EAECF0;
    }

    .orb-badge-primary {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border: 1px solid rgba(75, 0, 232, .12);
    }

    /* ACTION */

    .orb-action-btn {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #fff;
        color: #667085;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .orb-action-btn:hover {
        background: var(--orb-soft);
        color: var(--orb-primary);
    }

    .dropdown-menu {
        border: 1px solid var(--orb-border);
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(16, 24, 40, .12);
        padding: 8px;
    }

    .dropdown-item {
        border-radius: 10px;
        font-size: 13px;
        font-weight: 800;
        padding: 9px 12px;
    }

    /* DATATABLE */

    .dataTables_wrapper .dt-buttons {
        margin-bottom: 12px;
    }

    .dataTables_wrapper .dt-buttons .btn {
        border-radius: 12px !important;
        border: 1px solid var(--orb-border) !important;
        background: #fff !important;
        color: var(--orb-text) !important;
        font-size: 12px !important;
        font-weight: 850 !important;
        padding: 8px 12px !important;
        box-shadow: none !important;
    }

    .dataTables_wrapper .dt-buttons .btn:hover {
        background: var(--orb-soft) !important;
        color: var(--orb-primary) !important;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--orb-border);
        border-radius: 12px;
        height: 40px;
        padding: 0 12px;
        font-size: 12px;
    }

    /* EMPTY */

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

    /* MODAL */

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
    }

    .orb-modal .modal-title {
        font-weight: 950;
    }

    .orb-modal .modal-body {
        background: #fff;
        padding: 22px;
    }

    .orb-modal .modal-footer {
        background: #F8FAFC;
        border-top: 1px solid #EEF2F6;
    }

    .orb-modal .form-control,
    .orb-modal .custom-select {
        border-radius: 14px;
        border: 1px solid var(--orb-border);
        min-height: 44px;
        font-size: 13px;
    }

    .orb-modal .form-control:focus {
        border-color: rgba(75, 0, 232, .28);
        box-shadow: 0 0 0 4px rgba(75, 0, 232, .08) !important;
    }

    @media(max-width:991px) {
        .orb-summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:768px) {

        .orb-page {
            padding-bottom: 18px;
        }

        .orb-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 18px;
            border-radius: 20px;
        }

        .orb-hero h1 {
            font-size: 22px;
        }

        .orb-summary-grid {
            grid-template-columns: 1fr;
        }

        .orb-card-body {
            padding: 14px;
        }

        .dataTables_wrapper .row {
            gap: 10px;
        }

        .dataTables_wrapper .col-md-6 {
            width: 100%;
            max-width: 100%;
            flex: 0 0 100%;
            text-align: left !important;
        }
    }
</style>
@endsection

@section('_content')
<div class="orb-page">

    <div class="orb-hero">
        <div class="orb-hero-content">
            <div class="orb-hero-kicker">
                <i class="fas fa-layer-group"></i>
                HRMS Management
            </div>

            <h1>{{ $pageTitle }}</h1>

            <p>
                {{ $pageSubtitle ?? 'Manage HRMS records with filters, audit-friendly actions, exports and premium management workflow.' }}
            </p>
        </div>

        @if(!empty($canCreate))
        <button type="button"
            class="orb-btn orb-btn-primary"
            data-toggle="modal"
            data-target="#createModal">
            <i class="fas fa-plus"></i>
            Add New
        </button>
        @endif
    </div>

    @if(session('success') || session('status'))
    <div class="alert alert-success border-0 shadow-sm">
        {{ session('success') ?: session('status') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        {{ $errors->first() }}
    </div>
    @endif

    @if(!empty($summaryCards))
    <div class="orb-summary-grid">
        @foreach($summaryCards as $card)
        <div class="orb-summary-card">
            <div class="orb-summary-label">
                {{ $card['label'] }}
            </div>

            <div class="orb-summary-value">
                {{ $card['value'] }}
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if(!empty($filters))
    <div class="orb-card orb-filter">
        <div class="orb-card-body">
            <form method="GET" id="filterForm" class="row">
                @foreach($filters as $filter)

                <div class="col-md-{{ $filter['col'] ?? 3 }} mb-3">

                    <label>
                        {{ $filter['label'] }}
                    </label>

                    @if(($filter['type'] ?? 'text') === 'select')

                    <select name="{{ $filter['name'] }}"
                        class="form-control js-auto-filter">

                        <option value="">
                            {{ $filter['placeholder'] ?? 'All' }}
                        </option>

                        @foreach($filter['options'] as $value => $label)
                        <option value="{{ $value }}"
                            {{ (string) request($filter['name']) === (string) $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>

                    @else

                    <input type="{{ $filter['type'] ?? 'text' }}"
                        name="{{ $filter['name'] }}"
                        value="{{ request($filter['name']) }}"
                        class="form-control js-auto-filter"
                        placeholder="{{ $filter['placeholder'] ?? '' }}">

                    @endif

                </div>

                @endforeach

                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <a href="{{ url()->current() }}"
                        class="orb-btn orb-btn-light w-100 justify-content-center">
                        <i class="fas fa-undo"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="orb-card">
        <div class="orb-card-body">

            <div class="orb-table-wrap">

                <table class="table table-hover orb-table js-orb-datatable">

                    <thead>
                        <tr>

                            <th>S.No.</th>

                            @foreach($columns as $column)
                            <th>{{ $column['label'] }}</th>
                            @endforeach

                            @if(!empty($rowActions) || !empty($canEdit) || !empty($canDelete))
                            <th>Action</th>
                            @endif

                        </tr>
                    </thead>

                    <tbody>

                        @forelse($rows as $row)

                        <tr>

                            <td>
                                <strong>{{ $loop->iteration }}</strong>
                            </td>

                            @foreach($columns as $column)

                            @php
                            $value = data_get($row, $column['key']);
                            @endphp

                            <td>

                                @if(($column['type'] ?? '') === 'badge')

                                @php
                                $badge =
                                in_array($value, ['approved','active','earned','processed',1,true], true)
                                ? 'orb-badge-success'
                                : (
                                in_array($value, ['pending','unprocessed'], true)
                                ? 'orb-badge-warning'
                                : (
                                in_array($value, ['rejected','cancelled','expired','inactive',0,false], true)
                                ? 'orb-badge-danger'
                                : 'orb-badge-primary'
                                )
                                );
                                @endphp

                                <span class="orb-badge {{ $badge }}">
                                    {{ is_bool($value) ? ($value ? 'Active' : 'Inactive') : ucfirst((string) $value) }}
                                </span>

                                @elseif(($column['type'] ?? '') === 'date' && $value)

                                {{ \Carbon\Carbon::parse($value)->format('d M Y') }}

                                @elseif(($column['type'] ?? '') === 'datetime' && $value)

                                {{ \Carbon\Carbon::parse($value)->format('d M Y h:i A') }}

                                @elseif(($column['type'] ?? '') === 'json')

                                <pre class="mb-0 small"
                                    style="max-width:360px;white-space:pre-wrap">{{ json_encode(is_string($value) ? json_decode($value, true) : $value, JSON_PRETTY_PRINT) }}</pre>

                                @else

                                {{ $value ?? '-' }}

                                @endif

                            </td>

                            @endforeach

                            @if(!empty($rowActions) || !empty($canEdit) || !empty($canDelete))

                            <td>

                                <div class="dropdown">

                                    <button class="orb-action-btn"
                                        type="button"
                                        data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right">

                                        @if(!empty($canEdit))

                                        <button type="button"
                                            class="dropdown-item"
                                            data-toggle="modal"
                                            data-target="#editModal{{ data_get($row, 'id') }}">
                                            <i class="fas fa-edit mr-2 text-primary"></i>
                                            Edit
                                        </button>

                                        @endif

                                        @foreach($rowActions ?? [] as $action)

                                        <form method="POST"
                                            action="{{ route($action['route'], data_get($row, 'id')) }}"
                                            onsubmit="return confirm('{{ $action['confirm'] ?? 'Continue?' }}')">

                                            @csrf

                                            <button class="dropdown-item"
                                                type="submit">
                                                <i class="{{ $action['icon'] ?? 'fas fa-check' }} mr-2"></i>
                                                {{ $action['label'] }}
                                            </button>

                                        </form>

                                        @endforeach

                                        @if(!empty($canDelete))

                                        <form method="POST"
                                            action="{{ route($deleteRoute, data_get($row, 'id')) }}"
                                            onsubmit="return confirm('Delete this record?')">

                                            @csrf
                                            @method('DELETE')

                                            <button class="dropdown-item text-danger"
                                                type="submit">
                                                <i class="fas fa-trash mr-2"></i>
                                                Delete
                                            </button>

                                        </form>

                                        @endif

                                    </div>

                                </div>

                            </td>

                            @endif

                        </tr>

                        @empty

                        <tr>
                            <td colspan="{{ count($columns) + 2 }}"
                                class="orb-empty">

                                <i class="fas fa-folder-open"></i>

                                <div class="font-weight-bold text-dark mb-1">
                                    No records found
                                </div>

                                <div class="small">
                                    Records will appear here once data is available.
                                </div>

                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if(method_exists($rows, 'links'))
            <div class="mt-3">
                {{ $rows->appends(request()->query())->links() }}
            </div>
            @endif

        </div>
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
@endsection

@section('_script')

<script>
    document.querySelectorAll('.js-auto-filter').forEach(function(input) {

        input.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        if (input.tagName === 'INPUT') {

            let timeout = null;

            input.addEventListener('keyup', function() {

                clearTimeout(timeout);

                timeout = setTimeout(function() {
                    document.getElementById('filterForm').submit();
                }, 500);

            });

        }

    });

    if (window.jQuery && $.fn.DataTable) {

        $('.js-orb-datatable').DataTable({

            paging: true,
            searching: true,
            info: true,
            lengthChange: true,
            responsive: false,
            autoWidth: false,
            pageLength: 25,
            order: [],

            dom: "<'row align-items-center mb-3'<'col-md-6'B><'col-md-6 text-md-right'f>>" +
                "rt" +
                "<'row align-items-center mt-3'<'col-md-6'l i><'col-md-6 text-md-right'p>>",

            buttons: [{
                    extend: 'csv',
                    className: 'btn'
                },
                {
                    extend: 'excel',
                    className: 'btn'
                },
                {
                    extend: 'pdf',
                    className: 'btn'
                },
                {
                    extend: 'print',
                    className: 'btn'
                }
            ]

        });

    }
</script>
@endsection