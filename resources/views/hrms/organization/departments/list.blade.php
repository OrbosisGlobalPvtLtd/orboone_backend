<div class="card p-3">
    <div class="d-flex justify-content-between mb-2">
        <h5>Departments</h5>
        <button class="btn btn-primary">+ Add</button>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Code</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $dept)
            <tr>
                <td>{{ $dept->name }}</td>
                <td>{{ $dept->code }}</td>
                <td>{{ $dept->address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>