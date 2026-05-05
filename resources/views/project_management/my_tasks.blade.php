@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'my_tasks'])

@section('_content')
<div class="container-fluid mt-2 px-4">
    <div class="row">
        <div class="col-12">
            <h4 class="font-weight-bold">My Assigned Tasks</h4>
            <hr>
        </div>
    </div>

    <div class="card p-3 bg-light">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Task</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td>{{ $task->id }}</td>
                            <td>{{ $task->title }}</td>
                            <td class="text-left">
                                <div style="max-height: 80px; overflow-y: auto; font-size: 0.85rem; white-space: pre-line; overflow-wrap: break-word; word-wrap: break-word;">
                                    {{ strip_tags($task->description) }}
                                </div>
                            </td>
                            <td>{{ $task->due_date }}</td>
                            <td>{{ ucfirst($task->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No tasks assigned to you.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
