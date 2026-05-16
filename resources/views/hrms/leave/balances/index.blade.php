@extends('layouts.panel')

@section('_head')
@include('hrms.leave.shared.style')
@endsection

@section('_content')
<div class="leave-page">
    <div class="leave-container">

        <div class="leave-header">
            <div>
                <h3 class="leave-title">Leave Balances</h3>
                <p class="leave-subtitle">Employee-wise earned, used, remaining, and LWP balances.</p>
            </div>
            <button class="leave-btn leave-btn-primary" onclick="$('.js-datatable').DataTable().button('.buttons-excel').trigger();">
                <i class="fas fa-file-excel mr-1"></i> Export Balances
            </button>
        </div>

        @include('hrms.leave.shared.flash')

        <div class="leave-filters mb-4" style="border-radius: 16px;">
            <form class="d-flex w-100 flex-wrap gap-2 align-items-center">
                <div class="font-weight-bold text-dark mr-3"><i class="fas fa-filter text-primary mr-1"></i> Filters:</div>
                <input name="year" type="number" class="leave-filter-select" value="{{ request('year', $year ?? date('Y')) }}" style="width: 120px;" placeholder="Year">
                
                <select name="employee_id" class="leave-filter-select auto-filter">
                    <option value="">All Employees</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->user_name ?? $employee->display_name }}
                        </option>
                    @endforeach
                </select>
                
                <button type="submit" class="leave-btn leave-btn-light ml-auto">
                    Apply Filters
                </button>
            </form>
        </div>

        <div class="leave-card">
            <div class="leave-table-wrap">
                <div class="leave-table-responsive">
                    <table class="leave-table js-datatable">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Year</th>
                                <th>Total Remaining</th>
                                <th>Paid Rem.</th>
                                <th>Sick Rem.</th>
                                <th>Comp Off Rem.</th>
                                <th>LWP Used</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($balances as $balance)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center mr-2" style="width:32px;height:32px;font-weight:bold;background:#F4F2FF;">
                                                {{ substr(optional($balance->employee)->display_name ?? 'U', 0, 1) }}
                                            </div>
                                            <strong>{{ optional($balance->employee)->display_name }}</strong>
                                        </div>
                                    </td>
                                    <td><strong>{{ $balance->year }}</strong></td>
                                    <td>
                                        <span class="text-success font-weight-bold">{{ $balance->total_remaining }}</span>
                                        <span class="small text-muted">/ {{ $balance->total_allocated }}</span>
                                    </td>
                                    <td><span class="leave-badge badge-paid">{{ $balance->paid_remaining }}</span></td>
                                    <td><span class="leave-badge badge-pending">{{ $balance->sick_remaining }}</span></td>
                                    <td><span class="leave-badge badge-comp-off">{{ $balance->comp_off_remaining }}</span></td>
                                    <td><span class="leave-badge badge-lwp">{{ $balance->lwp_used }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-3 bg-light border-top">
                {{ method_exists($balances, 'links') ? $balances->links() : '' }}
            </div>
        </div>

    </div>
</div>
@endsection

@section('_script')
@include('hrms.leave.shared.datatable')
@endsection
