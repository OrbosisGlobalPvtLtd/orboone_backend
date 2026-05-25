@extends('layouts.panel', ['active' => 'employees'])

@section('page_title', 'Reporting Structure')

@section('_content')
<style>
    .eo-page{min-height:calc(100vh - 90px);padding:16px 10px 30px;background:#F6F7FB;}
    .eo-container{max-width:1320px;margin:0 auto;}
    .eo-header,.eo-card{background:#fff;border:1px solid #E7EAF3;border-radius:20px;box-shadow:0 10px 28px rgba(16,24,40,.06);}
    .eo-header{padding:16px;margin-bottom:14px;}
    .eo-title{margin:0;color:#101828;font-size:24px;font-weight:900;}
    .eo-subtitle{margin:4px 0 0;color:#667085;font-size:13px;font-weight:600;}
    .eo-card{overflow:hidden;}
    .eo-table th{background:#F8FAFC;color:#667085;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.45px;border-bottom:1px solid #E7EAF3;white-space:nowrap;}
    .eo-table td{vertical-align:middle;color:#101828;font-size:13px;font-weight:650;border-bottom:1px solid #F1F3F8;}
    .eo-code{display:inline-flex;padding:6px 9px;border-radius:10px;background:#F4F2FF;color:#4B00E8;font-size:12px;font-weight:900;white-space:nowrap;}
    .eo-person{display:flex;flex-direction:column;gap:2px;}
    .eo-name{color:#101828;font-size:13px;font-weight:900;}
    .eo-meta{color:#667085;font-size:11px;font-weight:700;}
    .eo-count{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:28px;border-radius:999px;background:rgba(75,0,232,.08);color:#4B00E8;font-size:12px;font-weight:900;}
</style>

<div class="eo-page">
    <div class="eo-container">
        <div class="eo-header">
            <h1 class="eo-title">Reporting Structure</h1>
            <p class="eo-subtitle">Employee hierarchy based on assigned reporting managers.</p>
        </div>

        <div class="eo-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 eo-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Reporting Manager</th>
                            <th class="text-center">Team Members</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>
                                    <div class="eo-person">
                                        <span class="eo-name">{{ $employee->employee_name ?? '-' }}</span>
                                        <span class="eo-code">{{ $employee->employee_code ?? 'EMP-'.$employee->id }}</span>
                                    </div>
                                </td>
                                <td>{{ $employee->department_name ?? '-' }}</td>
                                <td>{{ $employee->designation_name ?? '-' }}</td>
                                <td>
                                    @if($employee->manager_name)
                                        <div class="eo-person">
                                            <span class="eo-name">{{ $employee->manager_name }}</span>
                                            <span class="eo-meta">{{ $employee->manager_code ?? '-' }}</span>
                                        </div>
                                    @else
                                        <span class="eo-meta">No reporting manager</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="eo-count">{{ (int) $employee->team_members_count }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No reporting structure records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection