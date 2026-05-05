@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container">
    <h2>Submit Reimbursement Claim</h2>

    <form method="POST" action="{{ route('pages.payroll.claims.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>Category</label>
            <input type="text" name="category" class="form-control" value="{{ old('category') }}">
            @error('category') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Amount</label>
            <input type="number" name="amount" step="0.01" class="form-control" value="{{ old('amount') }}">
            @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="mb-3">
            <label>Bill / Receipt (optional)</label>
            <input type="file" name="file" class="form-control">
            @error('file') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-success">Submit Claim</button>
    </form>
</div>
@endsection
