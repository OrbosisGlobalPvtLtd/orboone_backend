@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Exit Employees')

@section('_content')
<style>
    .eo-page{min-height:calc(100vh - 90px);padding:16px 10px 30px;background:#F6F7FB;}
    .eo-container{max-width:1320px;margin:0 auto;}
    .eo-header,.eo-card{background:#fff;border:1px solid #E7EAF3;border-radius:20px;box-shadow:0 10px 28px rgba(16,24,40,.06);}
    .eo-header{padding:16px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:14px;}
    .eo-title{margin:0;color:#101828;font-size:24px;font-weight:900;}
    .eo-subtitle{margin:4px 0 0;color:#667085;font-size:13px;font-weight:600;}
    .eo-card{overflow:hidden;}
    .eo-btn{min-height:40px;border-radius:12px;padding:9px 14px;font-size:13px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid #E7EAF3;text-decoration:none !important;background:#fff;color:#101828;}
    .eo-table th{background:#F8FAFC;color:#667085;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.45px;border-bottom:1px solid #E7EAF3;white-space:nowrap;}
    .eo-table td{vertical-align:middle;color:#101828;font-size:13px;font-weight:650;border-bottom:1px solid #F1F3F8;}
    .eo-code{display:inline-flex;padding:6px 9px;border-radius:10px;background:#F4F2FF;color:#4B00E8;font-size:12px;font-weight:900;white-space:nowrap;}
    .eo-pill{display:inline-flex;padding:6px 9px;border-radius:999px;font-size:11px;font-weight:900;text-transform:uppercase;white-space:nowrap;background:#F2F4F7;color:#667085;}
    .eo-pill-resigned{background:rgba(247,144,9,.12);color:#F79009;}
    .eo-pill-terminated{background:rgba(240,68,56,.10);color:#F04438;}
    .eo-actions{display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
    .eo-icon-btn,.eo-action-btn{border:0;border-radius:11px;background:#F8FAFC;color:#667085;display:inline-flex;align-items:center;justify-content:center;transition:.18s ease;text-decoration:none !important;}
    .eo-icon-btn{width:34px;height:34px;}
    .eo-icon-btn:hover,.eo-action-btn:hover{color:#fff;background:#4B00E8;}
    .eo-action-btn{min-height:34px;padding:0 10px;font-size:12px;font-weight:900;cursor:pointer;}
    .eo-mini-form{display:flex;align-items:center;gap:6px;margin:0;flex-wrap:wrap;}
    .eo-select,.eo-date{height:34px;border-radius:10px;border:1px solid #E7EAF3;padding:5px 8px;font-size:12px;font-weight:700;color:#101828;background:#fff;}
    @media(max-width:768px){.eo-header{flex-direction:column;align-items:flex-start;}.eo-actions{min-width:280px;}}
</style>

<div class="eo-page">
    <div class="eo-container">
        <div class="eo-header">
            <div>
                <h1 class="eo-title">Exit Employees</h1>
                <p class="eo-subtitle">Employees with resigned, terminated, inactive, or relieving records.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-3" style="border-radius:14px;font-weight:800;">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <div class="eo-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 eo-table">
                    <thead>
                        <tr>
                            <th>Employee Code</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Employment Status</th>
                            <th>Joining Date</th>
                            <th>Relieving Date</th>
                            <th width="220">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            @php
                                $status = strtolower($employee->employment_status ?? 'inactive');
                                $statusClass = $status === 'terminated' ? 'eo-pill-terminated' : 'eo-pill-resigned';
                            @endphp
                            <tr>
                                <td><span class="eo-code">{{ $employee->employee_code ?? 'EMP-'.$employee->id }}</span></td>
                                <td>{{ $employee->name ?? '-' }}</td>
                                <td>{{ $employee->department_name ?? '-' }}</td>
                                <td>{{ $employee->designation_name ?? '-' }}</td>
                                <td><span class="eo-pill {{ $statusClass }}">{{ ucfirst($status) }}</span></td>
                                <td>{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : '-' }}</td>
                                <td>{{ $employee->relieving_date ? \Carbon\Carbon::parse($employee->relieving_date)->format('d M Y') : '-' }}</td>
                                <td>
                                    <div class="eo-actions">
                                        @if(Route::has('hrms.employees.show'))
                                            <a href="{{ route('hrms.employees.show', $employee->id) }}" class="eo-icon-btn" title="View Employee">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif

                                        @if(Route::has('hrms.employees.edit'))
                                            <a href="{{ route('hrms.employees.edit', $employee->id) }}" class="eo-icon-btn" title="Edit Employee">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        @if(! in_array($status, ['resigned', 'terminated'], true) && Route::has('hrms.employees.exit.mark'))
                                            <form action="{{ route('hrms.employees.exit.mark', $employee->id) }}" method="POST" class="eo-mini-form">
                                                @csrf
                                                <select name="employment_status" class="eo-select" required>
                                                    <option value="resigned">Resigned</option>
                                                    <option value="terminated">Terminated</option>
                                                </select>
                                                <input type="date" name="relieving_date" class="eo-date" value="{{ $employee->relieving_date }}">
                                                <button type="submit" class="eo-action-btn">Save</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No exit employee records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
