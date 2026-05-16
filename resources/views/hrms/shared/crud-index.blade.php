@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'hrms'])

@section('_head')
<style>
    :root{
        --orb-primary:#4B00E8;--orb-secondary:#8600EE;--orb-bg:#F6F7FB;--orb-card:#FFFFFF;--orb-border:#E7EAF3;--orb-text:#101828;--orb-muted:#667085;--orb-soft:#F4F2FF;--orb-shadow:0 14px 35px rgba(16,24,40,.07);
    }
    .orb-page{background:var(--orb-bg);color:var(--orb-text);padding:6px 0 28px}
    .orb-hero{background:linear-gradient(135deg,var(--orb-primary),var(--orb-secondary));border-radius:18px;padding:22px 24px;color:#fff;box-shadow:var(--orb-shadow);display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:18px}
    .orb-hero h1{font-size:26px;font-weight:900;margin:0}.orb-hero p{margin:4px 0 0;color:rgba(255,255,255,.82);font-size:13px}
    .orb-card{background:#fff;border:1px solid var(--orb-border);border-radius:14px;box-shadow:var(--orb-shadow);margin-bottom:16px}.orb-card-body{padding:16px}
    .orb-btn{border:0;border-radius:12px;padding:9px 14px;font-weight:800;display:inline-flex;align-items:center;gap:7px}.orb-btn-primary{background:#fff;color:var(--orb-primary)}.orb-btn-gradient{background:linear-gradient(135deg,var(--orb-primary),var(--orb-secondary));color:#fff}.orb-btn-light{background:#fff;border:1px solid var(--orb-border);color:var(--orb-text)}
    .orb-filter label,.orb-form-label{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--orb-muted);font-weight:900}.orb-filter .form-control,.orb-filter .custom-select{border-radius:12px;border:1px solid var(--orb-border);height:42px}
    .orb-table-wrap{overflow-x:auto}.orb-table{width:100%!important;min-width:980px}.orb-table th{font-size:11px;text-transform:uppercase;color:#475467;background:#F8FAFC;white-space:nowrap}.orb-table td{vertical-align:middle;white-space:nowrap}
    .orb-badge{display:inline-flex;border-radius:999px;padding:5px 10px;font-size:11px;font-weight:900;text-transform:uppercase}.orb-badge-success{background:#ECFDF3;color:#027A48}.orb-badge-warning{background:#FFFAEB;color:#B54708}.orb-badge-danger{background:#FEF3F2;color:#B42318}.orb-badge-muted{background:#F2F4F7;color:#475467}.orb-badge-primary{background:var(--orb-soft);color:var(--orb-primary)}
    .orb-modal .modal-content{border:0;border-radius:22px;overflow:hidden;box-shadow:0 24px 70px rgba(15,23,42,.28)}.orb-modal .modal-header{background:linear-gradient(135deg,var(--orb-primary),var(--orb-secondary));color:#fff;border:0}.orb-modal .modal-title{font-weight:900}.orb-modal .modal-body{background:#fff;padding:22px}.orb-modal .modal-footer{background:#F8FAFC;border-top:1px solid #EEF2F6}
    .orb-modal .form-control,.orb-modal .custom-select{border-radius:12px;border:1px solid var(--orb-border);min-height:42px}.dataTables_wrapper .dt-buttons .btn{border-radius:10px;margin-right:6px;background:var(--orb-soft);color:var(--orb-primary);border:1px solid #DDD6FE;font-weight:700}
    @media(max-width:768px){.orb-hero{align-items:flex-start;flex-direction:column}.orb-hero h1{font-size:22px}}
</style>
@endsection

@section('_content')
<div class="orb-page">
    <div class="orb-hero">
        <div>
            <h1>{{ $pageTitle }}</h1>
            <p>{{ $pageSubtitle ?? 'Manage HRMS records with filters, audit-friendly actions, and exports.' }}</p>
        </div>
        @if(!empty($canCreate))
            <button type="button" class="orb-btn orb-btn-primary" data-toggle="modal" data-target="#createModal" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus"></i> Add
            </button>
        @endif
    </div>

    @if(session('success') || session('status'))
        <div class="alert alert-success border-0">{{ session('success') ?: session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0">{{ $errors->first() }}</div>
    @endif

    @if(!empty($summaryCards))
        <div class="row">
            @foreach($summaryCards as $card)
                <div class="col-6 col-lg-3 mb-3">
                    <div class="orb-card mb-0"><div class="orb-card-body">
                        <div class="text-muted small font-weight-bold">{{ $card['label'] }}</div>
                        <div class="h4 mb-0">{{ $card['value'] }}</div>
                    </div></div>
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($filters))
        <div class="orb-card orb-filter">
            <div class="orb-card-body">
                <form method="GET" id="filterForm" class="row align-items-end">
                    @foreach($filters as $filter)
                        <div class="col-md-{{ $filter['col'] ?? 3 }} mb-2">
                            <label>{{ $filter['label'] }}</label>
                            @if(($filter['type'] ?? 'text') === 'select')
                                <select name="{{ $filter['name'] }}" class="form-control js-auto-filter">
                                    <option value="">{{ $filter['placeholder'] ?? 'All' }}</option>
                                    @foreach($filter['options'] as $value => $label)
                                        <option value="{{ $value }}" {{ (string) request($filter['name']) === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="{{ $filter['type'] ?? 'text' }}" name="{{ $filter['name'] }}" value="{{ request($filter['name']) }}" class="form-control js-auto-filter" placeholder="{{ $filter['placeholder'] ?? '' }}">
                            @endif
                        </div>
                    @endforeach
                    <div class="col-md-2 mb-2">
                        <a href="{{ url()->current() }}" class="btn orb-btn orb-btn-light w-100 justify-content-center"><i class="fas fa-undo"></i> Reset</a>
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
                                <td>{{ $loop->iteration }}</td>
                                @foreach($columns as $column)
                                    @php $value = data_get($row, $column['key']); @endphp
                                    <td>
                                        @if(($column['type'] ?? '') === 'badge')
                                            @php
                                                $badge = in_array($value, ['approved','active','earned','processed',1,true], true) ? 'orb-badge-success' : (in_array($value, ['pending','unprocessed'], true) ? 'orb-badge-warning' : (in_array($value, ['rejected','cancelled','expired','inactive',0,false], true) ? 'orb-badge-danger' : 'orb-badge-primary'));
                                            @endphp
                                            <span class="orb-badge {{ $badge }}">{{ is_bool($value) ? ($value ? 'Active' : 'Inactive') : ucfirst((string) $value) }}</span>
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
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm orb-btn-light dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown">Actions</button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @if(!empty($canEdit))
                                                    <button type="button" class="dropdown-item" data-toggle="modal" data-target="#editModal{{ data_get($row, 'id') }}" data-bs-toggle="modal" data-bs-target="#editModal{{ data_get($row, 'id') }}"><i class="fas fa-edit mr-2"></i>Edit</button>
                                                @endif
                                                @foreach($rowActions ?? [] as $action)
                                                    <form method="POST" action="{{ route($action['route'], data_get($row, 'id')) }}" onsubmit="return confirm('{{ $action['confirm'] ?? 'Continue?' }}')">
                                                        @csrf
                                                        <button class="dropdown-item" type="submit"><i class="{{ $action['icon'] ?? 'fas fa-check' }} mr-2"></i>{{ $action['label'] }}</button>
                                                    </form>
                                                @endforeach
                                                @if(!empty($canDelete))
                                                    <form method="POST" action="{{ route($deleteRoute, data_get($row, 'id')) }}" onsubmit="return confirm('Delete this record?')">
                                                        @csrf @method('DELETE')
                                                        <button class="dropdown-item text-danger" type="submit"><i class="fas fa-trash mr-2"></i>Delete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($columns) + 2 }}" class="text-center text-muted py-4">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($rows, 'links'))
                {{ $rows->appends(request()->query())->links() }}
            @endif
        </div>
    </div>

    @if(!empty($canCreate))
        @include('hrms.shared.crud-modal', ['modalId' => 'createModal', 'modalTitle' => 'Add '.$pageTitle, 'action' => route($storeRoute), 'method' => 'POST', 'fields' => $formFields, 'row' => null])
    @endif

    @if(!empty($canEdit))
        @foreach($rows as $row)
            @include('hrms.shared.crud-modal', ['modalId' => 'editModal'.data_get($row, 'id'), 'modalTitle' => 'Edit '.$pageTitle, 'action' => route($updateRoute, data_get($row, 'id')), 'method' => 'PUT', 'fields' => $formFields, 'row' => $row])
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
    });

    if (window.jQuery && $.fn.DataTable) {
        $('.js-orb-datatable').DataTable({
            paging: true,
            searching: true,
            info: true,
            lengthChange: true,
            pageLength: 25,
            order: [],
            dom: "<'row align-items-center mb-2'<'col-md-6'l><'col-md-6 text-md-right'Bf>>rt<'row align-items-center mt-2'<'col-md-6'i><'col-md-6'p>>",
            buttons: ['csv', 'excel', 'pdf', 'print']
        });
    }
</script>
@endsection
