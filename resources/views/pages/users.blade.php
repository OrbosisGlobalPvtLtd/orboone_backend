@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'accounts'])

@section('_content')
<div class="container-fluid mt-2 px-4">
  <div class="row">
    <div class="col-12">
        <h4 class="font-weight-bold">Users</h4>
        <hr>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-3">
      <div class="bg-light text-dark card p-3 overflow-auto">

        {{-- BUTTONS --}}
        <div class="d-flex justify-content-between">

          {{-- Check if access exists first --}}
          @php
            $menuAccess = collect($accesses)->where('menu_id', 10)->first();
          @endphp

          @if ($menuAccess && $menuAccess->status == 2)
            <a href="{{ route('employees-data.create') }}" class="btn btn-outline-dark mb-3 w-25">
              <i class="fas fa-plus mr-1"></i>
              <span>Create</span>
            </a>
          @endif

          <a href="{{ route('users.print') }}" class="btn btn-outline-dark mb-3 w-25" target="_blank">
            <i class="fas fa-print mr-1"></i>
            <span>Print</span>
          </a>
        </div>

        {{-- SUCCESS MESSAGE --}}
        @if (session('status'))
          <div class="alert alert-success">
            {{ session('status') }}
          </div>
        @endif

        {{-- USERS TABLE --}}
        <table class="table table-light table-striped table-hover table-bordered text-center">
          <thead>
            <tr>
              <th class="table-dark">#</th>
              <th class="table-dark">Name</th>
              <th class="table-dark">Email</th>
              <th class="table-dark">Active</th>
              <th class="table-dark">Role</th>
            </tr>
          </thead>

          <tbody>
            @foreach ($users as $user)
            <tr>
              <td>{{ $loop->iteration + $users->firstItem() - 1 }}</td>

              {{-- If employee exists, show link; if not, show plain text --}}
              <td class="w-25">
                @if ($user->employee)
                  <a href="{{ route('employees-data.show', ['employee' => $user->employee->id]) }}">
                    {{ $user->name }}
                  </a>
                @else
                  {{ $user->name }} <span class="text-muted">(No employee)</span>
                @endif
              </td>

              <td class="w-25">{{ $user->email }}</td>

              {{-- ACTIVE CHECKBOX --}}
              <td>
                <input type="checkbox" disabled {{ $user->is_active ? 'checked' : '' }}>
              </td>

              {{-- ROLE CHECK --}}
              <td>
                @if ($user->role)
                  <a href="{{ route('roles.show', ['role' => $user->role_id]) }}">
                    {{ $user->role->name }}
                  </a>
                @else
                  <span class="text-muted">No Role</span>
                @endif
              </td>

            </tr>
            @endforeach
          </tbody>
        </table>

        {{-- PAGINATION --}}
        {{ $users->links() }}

      </div>
    </div>
  </div>
</div>
@endsection
