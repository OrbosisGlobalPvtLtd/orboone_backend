@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Exit Employees')

@section('_content')
@include('hrms.employee.partials.styles')

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