<div class="card p-3">
    <div class="d-flex justify-content-between mb-2">
        <h5>Designations</h5>
        <button class="btn btn-primary">+ Add</button>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Department</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($designations as $desg)
            <tr>
                <td>{{ $desg->name }}</td>
                <td>{{ $desg->department_name }}</td>
                <td>
                    {{ $desg->is_active ? 'Active' : 'Inactive' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>