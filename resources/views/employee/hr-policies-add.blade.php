@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
    <div class="card p-4 col-lg-6 mx-auto">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h5 class="mb-3">Upload Policy</h5>

        <form method="POST" enctype="multipart/form-data" action="{{ route('policies.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Policy Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload File (PDF)</label>
                <input type="file" name="file" class="form-control" required>
            </div>

            <button class="btn btn-success w-100">Save</button>
        </form>
    </div>
@endsection
