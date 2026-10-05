@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => 'attendances'])

@section('page_title', 'Attendance Access Control')

@section('_head')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
@endsection

@section('_content')

@include('hrms.employee.partials.styles')

<style>
    :root {
        --orb-bg: #F6F7FB;
        --orb-card: #FFFFFF;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 14px 35px rgba(16, 24, 40, .07);
    }

    body {
        background: var(--orb-bg) !important;
        font-family: 'Outfit', sans-serif !important;
        overflow-x: hidden !important;
    }

    .att-page {
        min-height: calc(100vh - 90px);
        padding: 24px;
        background: var(--orb-bg);
        font-family: 'Outfit', sans-serif;
    }

    .att-container {
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Dynamic DB Theme Premium Hero Header */
    .att-header-premium {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        border-radius: 24px !important;
        padding: 28px 32px !important;
        color: #fff !important;
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 20px !important;
        flex-wrap: wrap !important;
        box-shadow: 0 12px 30px rgba(75, 0, 232, 0.15) !important;
        position: relative !important;
        overflow: hidden !important;
        margin-bottom: 24px !important;
        border: none !important;
        box-sizing: border-box !important;
        width: 100% !important;
    }

    .att-header-premium::before {
        content: '' !important;
        position: absolute !important;
        top: -50% !important;
        right: -20% !important;
        width: 300px !important;
        height: 300px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        border-radius: 50% !important;
        filter: blur(40px) !important;
        pointer-events: none !important;
    }

    .att-header-premium .title-area {
        position: relative !important;
        z-index: 2 !important;
        flex: 1 1 280px !important;
        min-width: 0 !important;
    }

    .att-header-premium .title-area h3 {
        font-size: 26px !important;
        font-weight: 900 !important;
        margin: 0 !important;
        color: #fff !important;
        letter-spacing: -0.02em !important;
        line-height: 1.25 !important;
        word-break: break-word !important;
    }

    .att-header-premium .title-area p {
        font-size: 13.5px !important;
        color: rgba(255, 255, 255, 0.88) !important;
        margin: 6px 0 0 0 !important;
        font-weight: 500 !important;
        line-height: 1.4 !important;
    }

    .att-header-premium .header-kicker {
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 5px 12px !important;
        border-radius: 999px !important;
        background: rgba(255, 255, 255, 0.16) !important;
        color: rgba(255, 255, 255, 0.95) !important;
        font-size: 11px !important;
        font-weight: 800 !important;
        letter-spacing: 0.06em !important;
        text-transform: uppercase !important;
        margin-bottom: 10px !important;
        width: fit-content !important;
        max-width: 100% !important;
        white-space: normal !important;
        line-height: 1.3 !important;
    }

    .att-header-premium .hero-actions-wrap {
        position: relative !important;
        z-index: 2 !important;
        flex-shrink: 0 !important;
        display: flex !important;
        align-items: center !important;
    }

    .orb-card-theme {
        background: #ffffff;
        border-radius: 22px;
        border: 1px solid var(--orb-border);
        box-shadow: var(--orb-shadow);
        padding: 24px;
        margin-bottom: 24px;
    }

    /* Modern Theme Toggle Switch */
    .switch-toggle {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        margin: 0;
        vertical-align: middle;
    }

    .switch-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1;
        transition: .3s ease;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s ease;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    input:checked + .slider {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%);
    }

    input:checked + .slider:before {
        transform: translateX(20px);
    }

    .toggle-label-status {
        font-size: 12px;
        font-weight: 800;
        min-width: 50px;
        text-align: left;
    }

    .table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--orb-muted);
        font-weight: 800;
        border-bottom-width: 1px !important;
        padding: 16px;
    }

    .table td {
        vertical-align: middle !important;
        font-size: 14px;
        padding: 16px;
    }

    .action-dot {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        border: 1px solid var(--orb-border);
        background: #ffffff;
        color: var(--orb-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .2s ease;
        font-size: 14px;
        box-shadow: 0 1px 3px rgba(16, 24, 40, 0.04);
        padding: 0;
        outline: none !important;
    }

    .action-dot:hover,
    .action-dot:focus,
    .show > .action-dot {
        background: var(--orb-soft);
        color: var(--orb-primary);
        border-color: rgba(75, 0, 232, 0.25);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(75, 0, 232, 0.08);
    }

    .att-action-menu {
        border: 1px solid var(--orb-border) !important;
        border-radius: 16px !important;
        box-shadow: 0 16px 36px rgba(16, 24, 40, .12) !important;
        padding: 6px !important;
        min-width: 220px !important;
        overflow: hidden !important;
    }

    .att-action-menu .dropdown-item {
        border-radius: 10px !important;
        padding: 9px 12px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        transition: all 0.15s ease !important;
        color: var(--orb-text) !important;
        background: transparent !important;
        border: none !important;
        width: 100% !important;
        text-align: left !important;
    }

    .att-action-menu .dropdown-item:hover {
        background: #f8fafc !important;
        color: var(--orb-primary) !important;
        transform: translateX(2px);
    }

    .att-action-menu .dropdown-item.text-success:hover {
        background: #f0fdf4 !important;
        color: #16a34a !important;
    }

    .att-action-menu .dropdown-item.text-primary:hover {
        background: #eff6ff !important;
        color: #2563eb !important;
    }

    .att-action-menu .dropdown-item.text-info:hover {
        background: #f0fdfa !important;
        color: #0d9488 !important;
    }

    .att-action-menu .dropdown-item.text-danger:hover {
        background: #fef2f2 !important;
        color: #dc2626 !important;
    }

    .btn-theme-primary {
        background: linear-gradient(135deg, var(--orb-primary) 0%, var(--orb-secondary) 100%) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 12px !important;
        font-weight: 800 !important;
    }

    .att-btn-glass,
    .att-header-premium .att-btn,
    .att-header-premium .btn-glass {
        background: rgba(255, 255, 255, 0.18) !important;
        border: 1px solid rgba(255, 255, 255, 0.45) !important;
        color: #ffffff !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        border-radius: 999px !important;
        padding: 10px 22px !important;
        font-size: 13.5px !important;
        font-weight: 750 !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        text-decoration: none !important;
        cursor: pointer !important;
        transition: all 0.25s ease !important;
        white-space: nowrap !important;
    }

    .att-btn-glass:hover,
    .att-header-premium .att-btn:hover,
    .att-header-premium .btn-glass:hover {
        background: rgba(255, 255, 255, 0.32) !important;
        border-color: rgba(255, 255, 255, 0.75) !important;
        color: #ffffff !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
    }

    .table-responsive {
        -webkit-overflow-scrolling: touch;
    }

    @media(max-width: 991px) {
        .att-header-premium {
            padding: 22px 24px !important;
            border-radius: 20px !important;
            gap: 16px !important;
            margin-bottom: 20px !important;
        }

        .att-header-premium .title-area h3 {
            font-size: 22px !important;
        }
    }

    @media(max-width: 768px) {
        .att-page {
            padding: 14px 12px 24px !important;
        }

        .att-header-premium {
            flex-direction: column !important;
            align-items: stretch !important;
            padding: 18px 16px !important;
            border-radius: 18px !important;
            gap: 14px !important;
            margin-bottom: 16px !important;
        }

        .att-header-premium .title-area {
            width: 100% !important;
            flex: 1 1 100% !important;
        }

        .att-header-premium .header-kicker {
            font-size: 10.5px !important;
            padding: 4px 10px !important;
            letter-spacing: 0.04em !important;
            margin-bottom: 8px !important;
        }

        .att-header-premium .title-area h3 {
            font-size: 19px !important;
            line-height: 1.3 !important;
        }

        .att-header-premium .title-area p {
            font-size: 12.5px !important;
            line-height: 1.4 !important;
            margin-top: 4px !important;
        }

        .att-header-premium .hero-actions-wrap {
            width: 100% !important;
        }

        .att-header-premium .hero-actions-wrap button,
        .att-header-premium .att-btn-glass,
        .att-header-premium .btn-glass {
            width: 100% !important;
            justify-content: center !important;
            padding: 10px 16px !important;
            font-size: 13px !important;
        }

        .orb-card-theme {
            padding: 16px !important;
            border-radius: 16px !important;
            margin-bottom: 16px !important;
        }

        .table th, .table td {
            padding: 10px 12px !important;
            font-size: 12.5px !important;
        }
    }

    @media(max-width: 480px) {
        .att-page {
            padding: 10px 8px 20px !important;
        }

        .att-header-premium {
            padding: 16px 14px !important;
            border-radius: 16px !important;
            gap: 12px !important;
        }

        .att-header-premium .title-area h3 {
            font-size: 17.5px !important;
        }

        .att-header-premium .title-area p {
            font-size: 12px !important;
        }
    }
</style>

<div class="att-page">
    <div class="att-container">
        
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 14px;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 14px;">
                <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        {{-- Dynamic Theme Hero Header --}}
        <div class="att-header-premium">
            <div class="title-area">
                <div class="header-kicker">
                    <i class="fas fa-shield-alt"></i> Attendance Access Control Engine
                </div>
                <h3>Attendance Access Control</h3>
                <p>Configure Mobile and Web Attendance punching permissions employee-wise or in bulk.</p>
            </div>
            @php
                $canManageAccess = auth()->user()->hasRole('super_admin') || auth()->user()->hasPermission('attendance.access_control.manage') || auth()->user()->hasPermission('attendance.blocked.unlock');
            @endphp
            @if($canManageAccess)
            <div class="hero-actions-wrap">
                <button type="button" class="att-btn att-btn-glass btn-glass" data-toggle="modal" data-target="#bulkUpdateModal">
                    <i class="fas fa-users-cog mr-2"></i> Bulk Access Update
                </button>
            </div>
            @endif
        </div>

        {{-- Automatic Filtering Card with Reset Button --}}
        <div class="orb-card-theme">
            <form method="GET" action="{{ route('attendances.access-control') }}" id="accessFilterForm" class="row align-items-end">
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="font-weight-bold text-muted small">Search Employee</label>
                    <input type="text" name="search" id="accessSearchInput" class="form-control" placeholder="Type Name, Email, or Code..." value="{{ request('search') }}" style="border-radius: 12px; height: 42px;">
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="font-weight-bold text-muted small">Department</label>
                    <select name="department_id" class="form-control select2-searchable" style="border-radius: 12px; height: 42px;">
                        <option value="">All Departments</option>
                        @foreach ($departments as $id => $name)
                            <option value="{{ $id }}" {{ request('department_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <label class="font-weight-bold text-muted small">Web Attendance</label>
                    <select name="web_attendance" class="form-control select2-searchable" style="border-radius: 12px; height: 42px;">
                        <option value="">All</option>
                        <option value="1" {{ request('web_attendance') === '1' ? 'selected' : '' }}>Allowed</option>
                        <option value="0" {{ request('web_attendance') === '0' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <label class="font-weight-bold text-muted small">Mobile Attendance</label>
                    <select name="mobile_attendance" class="form-control select2-searchable" style="border-radius: 12px; height: 42px;">
                        <option value="">All</option>
                        <option value="1" {{ request('mobile_attendance') === '1' ? 'selected' : '' }}>Allowed</option>
                        <option value="0" {{ request('mobile_attendance') === '0' ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn text-white font-weight-bold mr-2" style="border-radius: 12px; height: 42px; background: linear-gradient(135deg, var(--orb-primary, #4B00E8) 0%, var(--orb-secondary, #FF5252) 100%); border: none; flex: 1;" title="Search Filters">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('attendances.access-control') }}" class="btn btn-outline-secondary font-weight-bold d-flex align-items-center justify-content-center" style="border-radius: 12px; height: 42px; width: 42px; flex-shrink: 0;" title="Reset All Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Employee Access Table --}}
        <div class="orb-card-theme p-0" style="overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 40px; min-width: 40px;" class="pl-4">
                                <input type="checkbox" id="selectAllEmployees">
                            </th>
                            <th style="min-width: 220px;">Employee Details (Code & Name)</th>
                            <th style="min-width: 170px;">Department & Designation</th>
                            <th class="text-center" style="min-width: 210px;">Login Access (Authentication)</th>
                            <th class="text-center" style="min-width: 140px;">Mobile Attendance</th>
                            <th class="text-center" style="min-width: 140px;">Web Attendance</th>
                            <th class="text-right pr-4" style="min-width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $emp)
                            <tr>
                                <td class="pl-4">
                                    <input type="checkbox" name="selected_ids[]" value="{{ $emp->id }}" class="emp-checkbox">
                                </td>
                                {{-- Combined Employee Details: Code, Name, Email --}}
                                <td>
                                    <div>
                                        <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $emp->user_name ?? 'N/A' }}</div>
                                        <div class="my-1">
                                            <span class="badge badge-light text-dark border font-weight-bold px-2 py-1" style="border-radius: 8px;">{{ $emp->employee_code ?? '-' }}</span>
                                        </div>
                                        <div class="small text-muted">{{ $emp->user_email ?? '-' }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $emp->department_name ?? 'Unassigned' }}</div>
                                    <div class="small text-muted">{{ $emp->designation_name ?? '-' }}</div>
                                </td>

                                {{-- Login Access (Authentication) Toggle Switches --}}
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center justify-content-center">
                                        {{-- App Login Toggle Switch --}}
                                        <form method="POST" action="{{ route('attendances.access-control.update', $emp->id) }}" class="d-inline-flex align-items-center mr-3">
                                            @csrf
                                            <input type="hidden" name="is_app_access" value="{{ $emp->is_app_access ? '0' : '1' }}">
                                            <label class="switch-toggle mb-0" title="Click to toggle App Login Access">
                                                <input type="checkbox" {{ $emp->is_app_access ? 'checked' : '' }} onchange="this.form.submit()">
                                                <span class="slider"></span>
                                            </label>
                                            <span class="toggle-label-status ml-1 {{ $emp->is_app_access ? 'text-success' : 'text-muted' }}">
                                                App: {{ $emp->is_app_access ? 'On' : 'Off' }}
                                            </span>
                                        </form>

                                        {{-- Web Login Toggle Switch --}}
                                        <form method="POST" action="{{ route('attendances.access-control.update', $emp->id) }}" class="d-inline-flex align-items-center">
                                            @csrf
                                            <input type="hidden" name="is_web_access" value="{{ $emp->is_web_access ? '0' : '1' }}">
                                            <label class="switch-toggle mb-0" title="Click to toggle Web Login Access">
                                                <input type="checkbox" {{ $emp->is_web_access ? 'checked' : '' }} onchange="this.form.submit()">
                                                <span class="slider"></span>
                                            </label>
                                            <span class="toggle-label-status ml-1 {{ $emp->is_web_access ? 'text-primary' : 'text-muted' }}">
                                                Web: {{ $emp->is_web_access ? 'On' : 'Off' }}
                                            </span>
                                        </form>
                                    </div>
                                </td>
                                
                                {{-- Mobile Attendance Toggle Switch --}}
                                <td class="text-center">
                                    @if($canManageAccess)
                                    <form method="POST" action="{{ route('attendances.access-control.update', $emp->id) }}" class="d-inline-flex align-items-center justify-content-center">
                                        @csrf
                                        <input type="hidden" name="allow_mobile_attendance" value="{{ $emp->allow_mobile_attendance ? '0' : '1' }}">
                                        <label class="switch-toggle mb-0" title="Click to toggle Mobile Attendance">
                                            <input type="checkbox" {{ $emp->allow_mobile_attendance ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span class="slider"></span>
                                        </label>
                                        <span class="toggle-label-status ml-2 {{ $emp->allow_mobile_attendance ? 'text-success' : 'text-muted' }}">
                                            {{ $emp->allow_mobile_attendance ? 'Allowed' : 'Disabled' }}
                                        </span>
                                    </form>
                                    @else
                                    <span class="badge {{ $emp->allow_mobile_attendance ? 'badge-success' : 'badge-light border text-muted' }}" style="border-radius: 8px; font-size: 11px; padding: 5px 10px;">
                                        <i class="fas {{ $emp->allow_mobile_attendance ? 'fa-check-circle mr-1' : 'fa-ban mr-1' }}"></i> {{ $emp->allow_mobile_attendance ? 'Allowed' : 'Disabled' }}
                                    </span>
                                    @endif
                                </td>

                                {{-- Web Attendance Toggle Switch --}}
                                <td class="text-center">
                                    @if($canManageAccess)
                                    <form method="POST" action="{{ route('attendances.access-control.update', $emp->id) }}" class="d-inline-flex align-items-center justify-content-center">
                                        @csrf
                                        <input type="hidden" name="allow_web_attendance" value="{{ $emp->allow_web_attendance ? '0' : '1' }}">
                                        <label class="switch-toggle mb-0" title="Click to toggle Web Attendance">
                                            <input type="checkbox" {{ $emp->allow_web_attendance ? 'checked' : '' }} onchange="this.form.submit()">
                                            <span class="slider"></span>
                                        </label>
                                        <span class="toggle-label-status ml-2 {{ $emp->allow_web_attendance ? 'text-success' : 'text-muted' }}">
                                            {{ $emp->allow_web_attendance ? 'Allowed' : 'Disabled' }}
                                        </span>
                                    </form>
                                    @else
                                    <span class="badge {{ $emp->allow_web_attendance ? 'badge-success' : 'badge-light border text-muted' }}" style="border-radius: 8px; font-size: 11px; padding: 5px 10px;">
                                        <i class="fas {{ $emp->allow_web_attendance ? 'fa-check-circle mr-1' : 'fa-ban mr-1' }}"></i> {{ $emp->allow_web_attendance ? 'Allowed' : 'Disabled' }}
                                    </span>
                                    @endif
                                </td>

                                <td class="text-right pr-4">
                                    @if($canManageAccess)
                                    <div class="dropdown d-inline-block">
                                        <button class="action-dot" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right att-action-menu">
                                            <form method="POST" action="{{ route('attendances.access-control.update', $emp->id) }}">
                                                @csrf
                                                <input type="hidden" name="allow_web_attendance" value="1">
                                                <input type="hidden" name="allow_mobile_attendance" value="1">
                                                <button type="submit" class="dropdown-item text-success"><i class="fas fa-check-circle mr-2"></i> Allow Both (Mobile + Web)</button>
                                            </form>
                                            <form method="POST" action="{{ route('attendances.access-control.update', $emp->id) }}">
                                                @csrf
                                                <input type="hidden" name="allow_web_attendance" value="0">
                                                <input type="hidden" name="allow_mobile_attendance" value="1">
                                                <button type="submit" class="dropdown-item text-primary"><i class="fas fa-mobile-alt mr-2"></i> Mobile Only</button>
                                            </form>
                                            <form method="POST" action="{{ route('attendances.access-control.update', $emp->id) }}">
                                                @csrf
                                                <input type="hidden" name="allow_web_attendance" value="1">
                                                <input type="hidden" name="allow_mobile_attendance" value="0">
                                                <button type="submit" class="dropdown-item text-info"><i class="fas fa-laptop mr-2"></i> Web Only</button>
                                            </form>
                                            <div class="dropdown-divider my-1"></div>
                                            <form method="POST" action="{{ route('attendances.access-control.update', $emp->id) }}">
                                                @csrf
                                                <input type="hidden" name="allow_web_attendance" value="0">
                                                <input type="hidden" name="allow_mobile_attendance" value="0">
                                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-ban mr-2"></i> Disable Both</button>
                                            </form>
                                        </div>
                                    </div>
                                    @else
                                    <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px; border-radius: 8px;">
                                        <i class="fas fa-eye mr-1"></i> Read-only
                                    </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-users-slash fa-3x mb-3 text-muted"></i>
                                    <div>No employees found matching the specified filters.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($employees->hasPages())
                <div class="p-4 border-top d-flex justify-content-between align-items-center bg-light">
                    <div class="small text-muted font-weight-bold">
                        Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} employees
                    </div>
                    <div>
                        {{ $employees->links('vendor.pagination.orbo') }}
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

{{-- Bulk Update Modal --}}
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title font-weight-bold" style="color: var(--orb-text);">
                    <i class="fas fa-users-cog text-primary mr-2"></i> Bulk Attendance Access Update
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('attendances.access-control.bulk-update') }}" id="bulkAccessForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted small">Update Target Scope</label>
                        <select name="target_scope" id="targetScope" class="form-control select2-modal-searchable" required style="border-radius: 12px;">
                            <option value="department">By Department</option>
                            <option value="designation">By Designation</option>
                            <option value="selected">Selected Employees (<span id="selectedCount">0</span>)</option>
                            <option value="all">All Active Employees</option>
                        </select>
                    </div>

                    <div class="form-group mb-3" id="departmentSelectGroup">
                        <label class="font-weight-bold text-muted small">Select Department</label>
                        <select name="department_id" class="form-control select2-modal-searchable" style="border-radius: 12px;">
                            <option value="">Select Department</option>
                            @foreach ($departments as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3 d-none" id="designationSelectGroup">
                        <label class="font-weight-bold text-muted small">Select Designation</label>
                        <select name="designation_id" class="form-control select2-modal-searchable" style="border-radius: 12px;">
                            <option value="">Select Designation</option>
                            @foreach ($designations as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="selected_ids_json" id="selectedIdsJson">

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted small">Mobile Attendance Access</label>
                        <select name="allow_mobile_attendance" class="form-control select2-modal-searchable" required style="border-radius: 12px;">
                            <option value="1">Enable Mobile Attendance</option>
                            <option value="0">Disable Mobile Attendance</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-muted small">Web Attendance Access</label>
                        <select name="allow_web_attendance" class="form-control select2-modal-searchable" required style="border-radius: 12px;">
                            <option value="1">Enable Web Attendance</option>
                            <option value="0">Disable Web Attendance</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal" style="border-radius: 12px;">Cancel</button>
                    <button type="submit" class="btn btn-theme-primary font-weight-bold px-4">Apply Access Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllEmployees');
    const checkboxes = document.querySelectorAll('.emp-checkbox');
    const selectedCountSpan = document.getElementById('selectedCount');
    const selectedIdsInput = document.getElementById('selectedIdsJson');
    const targetScope = document.getElementById('targetScope');
    const deptGroup = document.getElementById('departmentSelectGroup');
    const desigGroup = document.getElementById('designationSelectGroup');

    function updateSelectedCount() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
        selectedCountSpan.textContent = selected.length;
        selectedIdsInput.value = JSON.stringify(selected);
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateSelectedCount();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    targetScope.addEventListener('change', function() {
        const val = this.value;
        deptGroup.classList.toggle('d-none', val !== 'department');
        desigGroup.classList.toggle('d-none', val !== 'designation');
    });

    const accessSearchInput = document.getElementById('accessSearchInput');
    if (accessSearchInput && accessSearchInput.value) {
        accessSearchInput.focus();
        const valLen = accessSearchInput.value.length;
        accessSearchInput.setSelectionRange(valLen, valLen);
    }
});
</script>
@endsection
