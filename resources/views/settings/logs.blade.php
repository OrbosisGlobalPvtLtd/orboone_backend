@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'logs'])

@section('_content')
<div class="container-fluid mt-2 px-4">
  <div class="row">
    <div class="col-12">
        <h4 class="font-weight-bold">Logs</h4>
        <hr>
    </div>
  </div>
  
  <div class="row">
    <div class="col-12 mb-3">
      <div class="bg-light text-dark card p-3 overflow-auto">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <a href="{{ route('logs.print') }}" class="btn btn-outline-dark" target="_blank">
            <i class="fas fa-print mr-1"></i>
              <span> Print Audit Logs</span>
          </a>
          <a href="{{ route('log-viewer.index') }}" class="btn btn-primary">
            <i class="fas fa-terminal mr-1"></i>
              <span> Open Laravel Log Viewer</span>
          </a>
        </div>

        <table class="table table-light table-striped table-hover table-bordered text-center">
          <thead>
            <tr>
              <th scope="col" class="table-dark">Description</th>
              <th scope="col" class="table-dark">Date</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($logs as $log)
            <tr>
              <td class="w-75">{{ $log->description }}</td>
              <td>{{ $log->created_at }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        {{ $logs->links() }}  
      </div>
    </div>
  </div>
</div>
@endsection