@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Exit Employees')

@section('_content')
<style>
    :root {
        --orb-primary: #4B00E8;
        --orb-secondary: #8600EE;
        --orb-bg: #F6F7FB;
        --orb-border: #E7EAF3;
        --orb-text: #101828;
        --orb-muted: #667085;
        --orb-soft: #F4F2FF;
        --orb-shadow: 0 10px 28px rgba(16, 24, 40, .06);
    }

    .eo-page {
        min-height: calc(100vh - 90px);
        padding: 16px 10px 30px;
        background: var(--orb-bg);
    }

    .eo-container {
        max-width: 1380px;
        margin: 0 auto;
    }

    .eo-header,
    .eo-card {
        background: #fff;
        border: 1px solid var(--orb-border);
        border-radius: 20px;
        box-shadow: var(--orb-shadow);
    }

    .eo-header {
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }

    .eo-title {
        margin: 0;
        color: var(--orb-text);
        font-size: 24px;
        font-weight: 900;
    }

    .eo-subtitle {
        margin: 4px 0 0;
        color: var(--orb-muted);
        font-size: 13px;
        font-weight: 600;
    }

    .eo-card {
        overflow: hidden;
    }

    .eo-table-wrap {
        overflow-x: auto;
    }

    .eo-table {
        min-width: 1280px;
        margin: 0 !important;
    }

    .eo-table th {
        background: #F8FAFC;
        color: #667085;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .45px;
        border-bottom: 1px solid var(--orb-border);
        white-space: nowrap;
        padding: 12px 14px;
    }

    .eo-table td {
        vertical-align: middle;
        color: var(--orb-text);
        font-size: 13px;
        font-weight: 650;
        border-bottom: 1px solid #F1F3F8;
        padding: 12px 14px;
    }

    .eo-table tbody tr:hover {
        background: #FCFAFF;
    }

    .eo-code {
        display: inline-flex;
        padding: 6px 9px;
        border-radius: 10px;
        background: #F4F2FF;
        color: var(--orb-primary);
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .eo-name {
        font-weight: 900;
        color: var(--orb-text);
        white-space: nowrap;
    }

    .eo-meta {
        font-size: 11px;
        color: var(--orb-muted);
        font-weight: 700;
        margin-top: 2px;
    }

    .eo-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
        background: #F2F4F7;
        color: #667085;
    }

    .eo-pill-resigned {
        background: rgba(247, 144, 9, .12);
        color: #F79009;
    }

    .eo-pill-terminated {
        background: rgba(240, 68, 56, .10);
        color: #F04438;
    }

    .eo-pill-inactive {
        background: #F2F4F7;
        color: #475467;
    }

    .eo-pill-success {
        background: #DCFCE7;
        color: #166534;
    }

    .eo-pill-warning {
        background: #FFF4D6;
        color: #B54708;
    }

    .eo-pill-danger {
        background: #FEE2E2;
        color: #991B1B;
    }

    .eo-pill-info {
        background: #E0F2FE;
        color: #0369A1;
    }

    .eo-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
    }

    .eo-icon-btn,
    .eo-action-btn {
        border: 0;
        border-radius: 11px;
        background: #F8FAFC;
        color: #667085;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: .18s ease;
        text-decoration: none !important;
    }

    .eo-icon-btn {
        width: 34px;
        height: 34px;
    }

    .eo-icon-btn:hover,
    .eo-action-btn:hover {
        color: #fff;
        background: var(--orb-primary);
    }

    .eo-action-btn {
        min-height: 34px;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
    }

    .eo-mini-form {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        flex-wrap: wrap;
    }

    .eo-select,
    .eo-date,
    .eo-input {
        height: 34px;
        border-radius: 10px;
        border: 1px solid var(--orb-border);
        padding: 5px 8px;
        font-size: 12px;
        font-weight: 700;
        color: var(--orb-text);
        background: #fff;
    }

    .eo-input {
        min-width: 160px;
    }

    .eo-empty {
        text-align: center;
        color: var(--orb-muted);
        font-weight: 800;
        padding: 28px !important;
    }

    @media(max-width:768px) {
        .eo-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .eo-actions {
            min-width: 280px;
        }
    }
</style>

<div class="eo-page">
    <div class="eo-container">
        <div class="eo-header">
            <div>
                <h1 class="eo-title">Exit Employees</h1>
                <p class="eo-subtitle">Track resigned, terminated, inactive employees, clearance, assets, FNF and
                    documents.</p>
            </div>
        </div>

        @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
        </div>
        @endif

        <div class="eo-card">
            <div class="eo-table-wrap">
                <table class="table table-hover eo-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th>Exit Type</th>
                            <th>Joining</th>
                            <th>Last Working</th>
                            <th>Asset</th>
                            <th>FNF</th>
                            <th>Experience Letter</th>
                            <th>Relieving Letter</th>
                            <th>Final Status</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        @php
                        $status = strtolower($employee->employment_status ?? 'inactive');

                        $statusClass = match ($status) {
                        'terminated' => 'eo-pill-terminated',
                        'inactive' => 'eo-pill-inactive',
                        default => 'eo-pill-resigned',
                        };

                        $exitType = $employee->exit_type ?? '-';
                        $assetStatus = $employee->asset_handover_status ?? 'pending';
                        $fnfStatus = $employee->fnf_status ?? 'pending';
                        $experienceStatus = $employee->experience_letter_status ?? 'pending';
                        $relievingStatus = $employee->relieving_letter_status ?? 'pending';
                        $finalStatus = $employee->final_status ?? 'pending';

                        $statusPill = function ($value) {
                        return match (strtolower($value ?? 'pending')) {
                        'completed', 'issued', 'not_required' => 'eo-pill-success',
                        'processing', 'clearance_pending' => 'eo-pill-info',
                        'lost', 'damaged' => 'eo-pill-danger',
                        default => 'eo-pill-warning',
                        };
                        };
                        @endphp

                        <tr>
                            <td>
                                <div>
                                    <span
                                        class="eo-code">{{ $employee->employee_code ?? 'EMP-' . $employee->id }}</span>
                                    <div class="eo-name mt-1">{{ $employee->name ?? '-' }}</div>
                                    <div class="eo-meta">{{ $employee->email ?? '-' }}</div>
                                </div>
                            </td>

                            <td>{{ $employee->department_name ?? '-' }}</td>
                            <td>{{ $employee->designation_name ?? '-' }}</td>

                            <td>
                                <span class="eo-pill {{ $statusClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td>
                                <span class="eo-pill eo-pill-info">
                                    {{ ucfirst(str_replace('_', ' ', $exitType)) }}
                                </span>
                            </td>

                            <td>
                                {{ !empty($employee->joining_date) ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : '-' }}
                            </td>

                            <td>
                                {{ !empty($employee->relieving_date) ? \Carbon\Carbon::parse($employee->relieving_date)->format('d M Y') : '-' }}
                            </td>

                            <td>
                                <span class="eo-pill {{ $statusPill($assetStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $assetStatus)) }}
                                </span>
                            </td>

                            <td>
                                <span class="eo-pill {{ $statusPill($fnfStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $fnfStatus)) }}
                                </span>
                            </td>

                            <td>
                                <span class="eo-pill {{ $statusPill($experienceStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $experienceStatus)) }}
                                </span>
                            </td>

                            <td>
                                <span class="eo-pill {{ $statusPill($relievingStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $relievingStatus)) }}
                                </span>
                            </td>

                            <td>
                                <span class="eo-pill {{ $statusPill($finalStatus) }}">
                                    {{ ucfirst(str_replace('_', ' ', $finalStatus)) }}
                                </span>
                            </td>

                            <td>
                                <div class="eo-actions">
                                    @if (Route::has('hrms.employees.show'))
                                    <a href="{{ route('hrms.employees.show', $employee->id) }}"
                                        class="eo-icon-btn" title="View Employee">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif

                                    @if (Route::has('hrms.employees.manage'))
                                    <a href="{{ route('hrms.employees.manage', $employee->id) }}"
                                        class="eo-icon-btn" title="Manage Employee">
                                        <i class="fas fa-user-cog"></i>
                                    </a>
                                    @elseif(Route::has('hrms.employees.edit'))
                                    <a href="{{ route('hrms.employees.edit', $employee->id) }}"
                                        class="eo-icon-btn" title="Edit Employee">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif

                                    @if (!in_array($status, ['resigned', 'terminated'], true) && Route::has('hrms.employees.exit.mark'))
                                    <form action="{{ route('hrms.employees.exit.mark', $employee->id) }}"
                                        method="POST" class="eo-mini-form">
                                        @csrf
                                        <select name="employment_status" class="eo-select" required>
                                            <option value="resigned">Resigned</option>
                                            <option value="terminated">Terminated</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        <input type="date" name="relieving_date" class="eo-date"
                                            value="{{ $employee->relieving_date }}">
                                        <input type="text" name="reason" class="eo-input"
                                            placeholder="Reason">
                                        <button type="submit" class="eo-action-btn"
                                            onclick="return confirm('Update employee exit status?')">
                                            Save
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="eo-empty">No exit employee records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection