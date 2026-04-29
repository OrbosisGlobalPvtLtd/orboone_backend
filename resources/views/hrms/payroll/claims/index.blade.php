@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container">
    <h2>Employee Claims</h2>

    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Employee</th>
            <th>Category</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Bill</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($claims as $c)
            <tr>
                <td>{{ $c->employee->name }}</td>
                <td>{{ $c->category }}</td>
                <td>{{ $c->amount }}</td>
                <td>{{ ucfirst($c->status) }}</td>
                <td>
                    @if($c->file)
                        <a href="{{ asset('storage/'.$c->file) }}" target="_blank">View</a>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($c->status == 'pending')
                        <form method="POST" action="" style="display:inline-block;">
                            @csrf
                            <button class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form method="POST" action="" style="display:inline-block;">
                            @csrf
                            <button class="btn btn-sm btn-danger">Reject</button>
                        </form>

                         {{-- <form method="POST" action="{{ route('pages.payroll.claims.approve', $c->id) }}" style="display:inline-block;">
                            @csrf
                            <button class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('pages.payroll.claims.reject', $c->id) }}" style="display:inline-block;">
                            @csrf
                            <button class="btn btn-sm btn-danger">Reject</button>
                        </form> --}}
                    @else
                        <span class="text-muted">Processed</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6">No claims.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
