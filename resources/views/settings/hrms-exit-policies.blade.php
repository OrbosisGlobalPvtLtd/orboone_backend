@extends('layouts.panel', ['active' => 'settings'])

@section('page_title', 'HRMS Exit Policies')

@section('_content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">HRMS Exit Policies</h5>
                <small class="text-muted">Manage notice period and exit controls from database policies.</small>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('settings.hrms_exit_policies.store') }}" method="POST" class="mb-4">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <input type="text" name="name" class="form-control" placeholder="Policy Name" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="applies_to" class="form-control" required>
                            <option value="all">All</option>
                            <option value="internship">Internship</option>
                            <option value="probation">Probation</option>
                            <option value="permanent">Permanent</option>
                            <option value="contract">Contract</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select name="exit_type" class="form-control">
                            <option value="">All Exit Types</option>
                            <option value="resignation">Resignation</option>
                            <option value="termination">Termination</option>
                            <option value="internship_exit">Internship Exit</option>
                            <option value="internship_completed">Internship Completed</option>
                            <option value="contract_end">Contract End</option>
                            <option value="absconding">Absconding</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-2">
                        <input type="number" name="notice_period_days" class="form-control" placeholder="Notice" min="0" value="15" required>
                    </div>
                    <div class="col-md-1 mb-2">
                        <input type="number" name="fnf_processing_days" class="form-control" placeholder="FnF" min="0" value="15" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <input type="date" name="effective_from" class="form-control" value="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-md-1 mb-2 text-nowrap">
                        <button type="submit" class="btn btn-primary btn-block">Add</button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="mr-3"><input type="checkbox" name="allow_waiver" value="1" checked> Allow Waiver</label>
                        <label class="mr-3"><input type="checkbox" name="allow_buyout" value="1" checked> Allow Buyout</label>
                        <label class="mr-3"><input type="checkbox" name="allow_immediate_exit" value="1" checked> Allow Immediate Exit</label>
                        <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Applies To</th>
                            <th>Exit Type</th>
                            <th>Notice</th>
                            <th>FnF Days</th>
                            <th>Flags</th>
                            <th>Effective</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($policies as $policy)
                            <tr>
                                <form action="{{ route('settings.hrms_exit_policies.update', $policy->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td><input type="text" name="name" value="{{ $policy->name }}" class="form-control form-control-sm" required></td>
                                    <td>
                                        <select name="applies_to" class="form-control form-control-sm" required>
                                            @foreach(['all' => 'All', 'internship' => 'Internship', 'probation' => 'Probation', 'permanent' => 'Permanent', 'contract' => 'Contract'] as $key => $label)
                                                <option value="{{ $key }}" @selected($policy->applies_to === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="exit_type" class="form-control form-control-sm">
                                            <option value="" @selected(empty($policy->exit_type))>All Exit Types</option>
                                            @foreach(['resignation', 'termination', 'internship_exit', 'internship_completed', 'contract_end', 'absconding'] as $type)
                                                <option value="{{ $type }}" @selected($policy->exit_type === $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="notice_period_days" min="0" value="{{ $policy->notice_period_days }}" class="form-control form-control-sm" required></td>
                                    <td><input type="number" name="fnf_processing_days" min="0" value="{{ $policy->fnf_processing_days }}" class="form-control form-control-sm" required></td>
                                    <td class="text-nowrap">
                                        <label class="mr-2"><input type="checkbox" name="allow_waiver" value="1" @checked($policy->allow_waiver)> W</label>
                                        <label class="mr-2"><input type="checkbox" name="allow_buyout" value="1" @checked($policy->allow_buyout)> B</label>
                                        <label><input type="checkbox" name="allow_immediate_exit" value="1" @checked($policy->allow_immediate_exit)> I</label>
                                    </td>
                                    <td><input type="date" name="effective_from" value="{{ $policy->effective_from ? \Carbon\Carbon::parse($policy->effective_from)->format('Y-m-d') : '' }}" class="form-control form-control-sm"></td>
                                    <td><input type="checkbox" name="is_active" value="1" @checked($policy->is_active)></td>
                                    <td><button class="btn btn-sm btn-outline-primary" type="submit">Save</button></td>
                                </form>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">No policies found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
