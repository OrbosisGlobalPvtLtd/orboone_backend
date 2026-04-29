@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container-fluid py-4">

    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Generate Statutory Report</h5>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('pages.payroll.statutoryreport_view') }}">

                <div class="row align-items-end">

                    <div class="col-md-4">
                        <label class="form-label">Select Month</label>
                        <input type="month" name="month" required class="form-control">
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary mt-3">
                            View Report
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>

</div>
@endsection
