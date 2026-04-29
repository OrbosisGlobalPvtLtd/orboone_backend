@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])
@section('_content')
<div class="container-fluid py-4">

<div class="card shadow-sm">
    <div class="card-header fw-bold">HR Policy Documents</div>

    <div class="card-body table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Visible To</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
                @foreach($docs as $doc)
                <tr>
                    <td>{{ $doc->title }}</td>
                    <td>{{ implode(',', $doc->visible_to ?? []) }}</td>
                    <td>
                        <a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-info btn-sm">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

</div>
@endsection
