@extends('layouts.print')

@section('_content')
<div class="container-fluid mt-2 px-4">
  <div class="row">
    <div class="col-12 text-center">
        <h4 class="font-weight-bold">Attendance Report</h4>
        <hr>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-3">
      <div class="bg-light text-dark card p-3 overflow-auto">
        <table class="table table-bordered text-center">
          <thead class="thead-dark">
            <tr>
              <th>#</th>
              <th>Employee Name</th>
              <th>Date</th>
              <th>Clock-In</th>
              <th>Clock-Out</th>
              <th>Working Hours</th>
              <th>Status</th>
              <th>Type</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($attendances as $attendance)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $attendance->user->name ?? 'N/A' }}</td>
              <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d M, Y') }}</td>
              <td>{{ $attendance->clock_in ?? '--:--' }}</td>
              <td>{{ $attendance->clock_out ?? '--:--' }}</td>
              <td>{{ $attendance->working_hours }}</td>
              <td>{{ $attendance->status }}</td>
              <td>{{ $attendance->work_type }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@section('_script')
    <script>
      window.onload = function () {
        window.print();
      }
    </script>
@endsection