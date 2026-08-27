@extends('layouts.panel', ['accesses' => $accesses ?? [], 'active' => $active ?? 'enterprise_payroll'])
@php
    $isSuperAdmin = auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') ? auth()->user()->isSuperAdmin() : false;
@endphp

@section('_head')
@include('hrms.enterprise-payroll.partials.styles')
@endsection

@section('_content')
<div class="ep-page">
    <div class="ep-hero">
        <div>
            <div class="ep-kicker"><i class="fas fa-layer-group"></i> Enterprise Payroll</div>
            <h1>Salary Structures</h1>
            <p>Maintain effective dated salary structures using employee records and approved CTC components.</p>
        </div>
    </div>

    @include('hrms.enterprise-payroll.partials.flash')

    <div class="ep-card">
        <!-- Table Card Header -->
        <div class="ep-table-header">
            <div class="ep-table-head-left">
                <div class="ep-icon-box"><i class="fas fa-layer-group"></i></div>
                <div>
                    <h5 class="ep-table-title">Salary Structures</h5>
                    <p class="ep-table-subtitle">Monitor and maintain effective dated salary structures assigned to employees.</p>
                </div>
            </div>
            <div class="ep-hero-actions">
                @if($canManage)
                    <button class="ep-btn ep-btn-gradient" data-toggle="modal" data-target="#salaryStructureModal"><i class="fas fa-plus"></i> Add Structure</button>
                @endif
            </div>
        </div>

        <!-- Attached Filters -->
        <div class="ep-card-filters">
            <form method="GET" action="{{ route('enterprise-payroll.salary-structures.index') }}" class="row align-items-end ep-form" id="filterForm">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label>Employee</label>
                    <select name="employee_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Stage</label>
                    <select name="stage" class="form-control" onchange="this.form.submit()">
                        <option value="">All Stages</option>
                        <option value="probation" {{ request('stage') == 'probation' ? 'selected' : '' }}>Probation</option>
                        <option value="permanent" {{ request('stage') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                        <option value="internship" {{ request('stage') == 'internship' ? 'selected' : '' }}>Internship</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Status</label>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 text-right">
                    <a href="{{ route('enterprise-payroll.salary-structures.index') }}" class="ep-btn ep-btn-light w-100"><i class="fas fa-sync-alt"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="ep-card-body p-0">
            <div class="ep-table-wrap">
                <table class="table ep-table js-orb-datatable">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Employee</th>
                            <th>Stage</th>
                            <th>Source</th>
                            <th>Sync Ref</th>
                            <th class="text-right">Monthly CTC</th>
                            <th class="text-right">Annual CTC</th>
                            <th>Effective From</th>
                            <th>Effective To</th>
                            <th>Status</th>
                            <th>Revision Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($rows) && count($rows) > 0)
                            @foreach($rows as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->employee_display_name }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $row->stage ?? '-')) }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $row->source ?? 'Manual')) }}</td>
                                    <td>{{ $row->sync_reference_type ?? '-' }}</td>
                                    <td class="text-right">₹{{ number_format((float) $row->monthly_ctc, 2) }}</td>
                                    <td class="text-right">₹{{ number_format((float) $row->annual_ctc, 2) }}</td>
                                    <td>{{ optional(\Carbon\Carbon::parse($row->effective_from))->format('d M Y') }}</td>
                                    <td>{{ $row->effective_to ? \Carbon\Carbon::parse($row->effective_to)->format('d M Y') : '-' }}</td>
                                    <td>@include('hrms.enterprise-payroll.partials.status-badge', ['status' => $row->status])</td>
                                    <td>{{ Str::limit($row->revision_reason ?? '-', 30) }}</td>
                                    <td>
                                        @if($isSuperAdmin || $canManage)
                                            <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#editModal{{ $row->id }}" title="Edit"><i class="fas fa-edit text-primary"></i></button>
                                            @if($isSuperAdmin)
                                            <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#deactivateModal{{ $row->id }}" title="Deactivate"><i class="fas fa-ban text-danger"></i></button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @if($canManage)
    <div class="modal fade" id="salaryStructureModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="{{ route('enterprise-payroll.salary-structures.store') }}" class="modal-content ep-form border-0 shadow-lg">
                @csrf
                <div class="ep-modal-header">
                    <h5 class="modal-title">Add Salary Structure</h5>
                    <p>Configure employee salary breakdown and payroll settings.</p>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="ep-modal-body">
                    
                    <div class="ep-section-card mb-3">
                        <div class="ep-section-title"><i class="fas fa-user-tie"></i> Employee & Salary Info</div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Employee</label><select name="employee_id" class="form-control" required><option value="">Select Employee</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->display_name }}</option>@endforeach</select></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Annual CTC</label><input type="number" step="0.01" name="annual_ctc" class="form-control" required></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Monthly CTC</label><input type="number" step="0.01" name="monthly_ctc" class="form-control"></div></div>
                            <div class="col-md-6 mb-3 mb-md-0"><div class="ep-form-group"><label>Effective From</label><input type="date" name="effective_from" class="form-control" required></div></div>
                            <div class="col-md-6"><div class="ep-form-group"><label>Effective To</label><input type="date" name="effective_to" class="form-control"></div></div>
                        </div>
                    </div>

                    <div class="ep-section-card mb-3">
                        <div class="ep-section-title"><i class="fas fa-coins"></i> Earnings Breakdown</div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Basic Annual</label><input type="number" step="0.01" name="basic_annual" class="form-control"></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>HRA Annual</label><input type="number" step="0.01" name="hra_annual" class="form-control"></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Special Annual</label><input type="number" step="0.01" name="special_allowance_annual" class="form-control"></div></div>
                            <div class="col-md-4 mb-3 mb-md-0"><div class="ep-form-group"><label>Basic Monthly</label><input type="number" step="0.01" name="basic_monthly" class="form-control"></div></div>
                            <div class="col-md-4 mb-3 mb-md-0"><div class="ep-form-group"><label>HRA Monthly</label><input type="number" step="0.01" name="hra_monthly" class="form-control"></div></div>
                            <div class="col-md-4"><div class="ep-form-group"><label>Special Monthly</label><input type="number" step="0.01" name="special_allowance_monthly" class="form-control"></div></div>
                        </div>
                    </div>

                    <div class="ep-section-card mb-3">
                        <div class="ep-section-title"><i class="fas fa-hand-holding-usd"></i> Deductions</div>
                        <div class="row">
                            <div class="col-md-3 mb-3 mb-md-0"><div class="ep-form-group"><label>PT Monthly</label><input type="number" step="0.01" name="professional_tax_monthly" class="form-control"></div></div>
                            <div class="col-md-3 mb-3 mb-md-0"><div class="ep-form-group"><label>TDS Annual</label><input type="number" step="0.01" name="tds_annual" class="form-control"></div></div>
                            <div class="col-md-3 mb-3 mb-md-0"><div class="ep-form-group"><label>TDS Monthly</label><input type="number" step="0.01" name="tds_monthly" class="form-control"></div></div>
                            <div class="col-md-3"><div class="ep-form-group"><label>Other Deduction</label><input type="number" step="0.01" name="other_deduction_monthly" class="form-control"></div></div>
                        </div>
                    </div>

                    <div class="ep-section-card mb-0">
                        <div class="ep-section-title"><i class="fas fa-cog"></i> Configuration</div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Stage</label><select name="stage" class="form-control"><option value="permanent">Permanent</option><option value="probation">Probation</option><option value="internship">Internship</option></select></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Source</label><select name="source" class="form-control"><option value="manual">Manual</option><option value="onboarding">Onboarding</option></select></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Status</label><select name="status" class="form-control"><option value="active">Active</option><option value="inactive">Inactive</option></select></div></div>
                            <div class="col-md-12"><div class="ep-form-group"><label>Remarks</label><input type="text" name="remarks" class="form-control"></div></div>
                        </div>
                    </div>

                </div>
                <div class="ep-modal-footer">
                    <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                    <button class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-save"></i> Save Structure</button>
                </div>
            </form>
        </div>
    </div>

    @foreach($rows as $row)
    <div class="modal fade" id="editModal{{ $row->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="{{ route('enterprise-payroll.salary-structures.update', $row->id) }}" class="modal-content ep-form border-0 shadow-lg">
                @csrf
                @method('PUT')
                <div class="ep-modal-header">
                    <h5 class="modal-title">Edit Salary Structure</h5>
                    <p>Update breakdown or status for {{ $row->employee_display_name }}.</p>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="ep-modal-body">
                    
                    <div class="ep-section-card mb-3">
                        <div class="ep-section-title"><i class="fas fa-user-tie"></i> Employee & Salary Info</div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Employee</label><select name="employee_id" class="form-control" required><option value="">Select Employee</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" {{ $row->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->display_name }}</option>@endforeach</select></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Annual CTC</label><input type="number" step="0.01" name="annual_ctc" class="form-control" value="{{ $row->annual_ctc }}" required></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Monthly CTC</label><input type="number" step="0.01" name="monthly_ctc" class="form-control" value="{{ $row->monthly_ctc }}"></div></div>
                            <div class="col-md-6 mb-3 mb-md-0"><div class="ep-form-group"><label>Effective From</label><input type="date" name="effective_from" class="form-control" value="{{ optional(\Carbon\Carbon::parse($row->effective_from))->format('Y-m-d') }}" required></div></div>
                            <div class="col-md-6"><div class="ep-form-group"><label>Effective To</label><input type="date" name="effective_to" class="form-control" value="{{ $row->effective_to ? optional(\Carbon\Carbon::parse($row->effective_to))->format('Y-m-d') : '' }}"></div></div>
                        </div>
                    </div>

                    <div class="ep-section-card mb-3">
                        <div class="ep-section-title"><i class="fas fa-coins"></i> Earnings Breakdown</div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Basic Annual</label><input type="number" step="0.01" name="basic_annual" class="form-control" value="{{ $row->basic_annual }}"></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>HRA Annual</label><input type="number" step="0.01" name="hra_annual" class="form-control" value="{{ $row->hra_annual }}"></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Special Annual</label><input type="number" step="0.01" name="special_allowance_annual" class="form-control" value="{{ $row->special_allowance_annual }}"></div></div>
                            <div class="col-md-4 mb-3 mb-md-0"><div class="ep-form-group"><label>Basic Monthly</label><input type="number" step="0.01" name="basic_monthly" class="form-control" value="{{ $row->basic_monthly }}"></div></div>
                            <div class="col-md-4 mb-3 mb-md-0"><div class="ep-form-group"><label>HRA Monthly</label><input type="number" step="0.01" name="hra_monthly" class="form-control" value="{{ $row->hra_monthly }}"></div></div>
                            <div class="col-md-4"><div class="ep-form-group"><label>Special Monthly</label><input type="number" step="0.01" name="special_allowance_monthly" class="form-control" value="{{ $row->special_allowance_monthly }}"></div></div>
                        </div>
                    </div>

                    <div class="ep-section-card mb-3">
                        <div class="ep-section-title"><i class="fas fa-hand-holding-usd"></i> Deductions</div>
                        <div class="row">
                            <div class="col-md-3 mb-3 mb-md-0"><div class="ep-form-group"><label>PT Monthly</label><input type="number" step="0.01" name="professional_tax_monthly" class="form-control" value="{{ $row->professional_tax_monthly }}"></div></div>
                            <div class="col-md-3 mb-3 mb-md-0"><div class="ep-form-group"><label>TDS Annual</label><input type="number" step="0.01" name="tds_annual" class="form-control" value="{{ $row->tds_annual }}"></div></div>
                            <div class="col-md-3 mb-3 mb-md-0"><div class="ep-form-group"><label>TDS Monthly</label><input type="number" step="0.01" name="tds_monthly" class="form-control" value="{{ $row->tds_monthly }}"></div></div>
                            <div class="col-md-3"><div class="ep-form-group"><label>Other Deduction</label><input type="number" step="0.01" name="other_deduction_monthly" class="form-control" value="{{ $row->other_deduction_monthly }}"></div></div>
                        </div>
                    </div>

                    <div class="ep-section-card mb-0">
                        <div class="ep-section-title"><i class="fas fa-cog"></i> Configuration</div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Stage</label><select name="stage" class="form-control"><option value="permanent" {{ $row->stage == 'permanent' ? 'selected' : '' }}>Permanent</option><option value="probation" {{ $row->stage == 'probation' ? 'selected' : '' }}>Probation</option><option value="internship" {{ $row->stage == 'internship' ? 'selected' : '' }}>Internship</option></select></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Source</label><select name="source" class="form-control"><option value="manual" {{ $row->source == 'manual' ? 'selected' : '' }}>Manual</option><option value="onboarding" {{ $row->source == 'onboarding' ? 'selected' : '' }}>Onboarding</option></select></div></div>
                            <div class="col-md-4 mb-3"><div class="ep-form-group"><label>Status</label><select name="status" class="form-control"><option value="active" {{ $row->status == 'active' ? 'selected' : '' }}>Active</option><option value="inactive" {{ $row->status == 'inactive' ? 'selected' : '' }}>Inactive</option></select></div></div>
                            <div class="col-md-12"><div class="ep-form-group"><label>Remarks</label><input type="text" name="remarks" class="form-control" placeholder="Update Reason" value="{{ $row->revision_reason }}"></div></div>
                        </div>
                    </div>

                </div>
                <div class="ep-modal-footer">
                    <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                    <button class="ep-modal-btn ep-modal-btn-primary"><i class="fas fa-save"></i> Update Structure</button>
                </div>
            </form>
        </div>
    </div>
    
    @if($isSuperAdmin)
    <div class="modal fade" id="deactivateModal{{ $row->id }}" tabindex="-1">
        <div class="modal-dialog modal-xs modal-dialog-centered">
            <form method="POST" action="{{ route('enterprise-payroll.salary-structures.update', $row->id) }}" class="modal-content ep-form border-0 shadow-lg">
                @csrf
                @method('PUT')
                <div class="ep-modal-header">
                    <h5 class="modal-title">Deactivate Structure</h5>
                    <p>Disable active salary components for this user.</p>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="ep-modal-body">
                    <input type="hidden" name="status" value="inactive">
                    <p class="mb-3 text-dark font-weight-bold">Are you sure you want to deactivate this salary structure for <strong>{{ $row->employee_display_name }}</strong>?</p>
                    <div class="ep-form-group">
                        <label>Deactivate Date (Effective To)</label>
                        <input type="date" name="effective_to" class="form-control" required>
                    </div>
                </div>
                <div class="ep-modal-footer">
                    <button type="button" class="ep-modal-btn ep-modal-btn-light" data-dismiss="modal">Cancel</button>
                    <button class="ep-modal-btn ep-modal-btn-danger"><i class="fas fa-ban"></i> Deactivate</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endforeach

    @endif
</div>
@endsection

@section('_script')
<script>
    function setMonthlyFromAnnual(annualName, monthlyName) {
        var annual = document.querySelector('[name="' + annualName + '"]');
        var monthly = document.querySelector('[name="' + monthlyName + '"]');
        if (!annual || !monthly) {
            return;
        }
        annual.addEventListener('input', function() {
            if (annual.value !== '') {
                monthly.value = (parseFloat(annual.value || 0) / 12).toFixed(2);
            }
        });
    }
    setMonthlyFromAnnual('annual_ctc', 'monthly_ctc');
    setMonthlyFromAnnual('basic_annual', 'basic_monthly');
    setMonthlyFromAnnual('hra_annual', 'hra_monthly');
    setMonthlyFromAnnual('special_allowance_annual', 'special_allowance_monthly');
    setMonthlyFromAnnual('tds_annual', 'tds_monthly');

    if (window.jQuery && $.fn.DataTable) {
        $('.js-orb-datatable').each(function() {
            var $table = $(this);
            $table.DataTable({
                pageLength: 25,
                order: [],
                searching: false,
                lengthChange: true,
                autoWidth: false,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                language: {
                    emptyTable: 'No salary structures found.',
                    zeroRecords: 'No matching records found.'
                },
                dom: '<"crud-dt-toolbar"<"crud-dt-left"l><"crud-dt-right"B>>rt<"orb-table-footer"ip>',
                buttons: [
                    { extend: 'csvHtml5', text: '<i class="fas fa-file-csv text-muted"></i> CSV', className: 'crud-export-btn' },
                    { extend: 'excelHtml5', text: '<i class="fas fa-file-excel text-success"></i> Excel', className: 'crud-export-btn' },
                    { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf text-danger"></i> PDF', className: 'crud-export-btn' },
                    { extend: 'print', text: '<i class="fas fa-print text-primary"></i> Print', className: 'crud-export-btn' }
                ]
            });
        });
    }
</script>
@endsection
