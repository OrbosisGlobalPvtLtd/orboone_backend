@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container-fluid py-4">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Statutory & Tax Configuration</h5>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('pages.payroll.statutorysettings.save') }}">
                @csrf

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">PF Percentage (%)</label>
                        <input type="number" step="0.01" name="pf_percent"
                               value="{{ old('pf_percent', $settings->pf_percent ?? '') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">ESI Percentage (%)</label>
                        <input type="number" step="0.01" name="esi_percent"
                               value="{{ old('esi_percent', $settings->esi_percent ?? '') }}"
                               class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Professional Tax (%)</label>
                        <input type="number" step="0.01" name="pt_percent"
                               value="{{ old('pt_percent', $settings->pt_percent ?? '') }}"
                               class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">TDS Slabs (JSON Format)</label>
                        <textarea name="tds_slabs" rows="5"
                                  class="form-control"
                                  placeholder='Example: [{"from":0,"to":250000,"rate":0}]'>{{ old('tds_slabs', json_encode($settings->tds_slabs ?? [], JSON_PRETTY_PRINT)) }}</textarea>
                    </div>

                </div>

                <div class="mt-4 text-end">
                    <button class="btn btn-success px-4">
                        Save Settings
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
