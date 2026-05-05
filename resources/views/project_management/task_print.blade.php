@extends('layouts.print')

@section('_content')
<div class="container-fluid mt-2 p-4">
  <div class="row">
    <div class="col-12 text-center">
        <h4 class="font-weight-bold">Task Management</h4>
        <hr>
    </div>
  </div>
  <div class="row">
    <div class="col-12 mb-3">
      <div class="bg-light text-dark card p-3 overflow-auto">
        <table class="table table-light table-striped table-hover table-bordered text-center">
          <thead>
          <tr>
              <th scope="col" class="table-dark">ID</th>
              <th scope="col" class="table-dark">Task</th>
              <th scope="col" class="table-dark">Task Information</th>
              <th scope="col" class="table-dark">Due Date</th>
              <th scope="col" class="table-dark">Employee Name</th>
              <th scope="col" class="table-dark">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($task as $task)
            <tr>
              <td>{{ $task->id }}</td>
              <td>{{ $task->title }}</td>
              <td class="text-left">{{ strip_tags($task->description) }}</td>
              <td>{{ $task->due_date }}</td>
              <td>{{ $task->employee_name }}</td>
              <td>{{ $task->status }}</td>
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