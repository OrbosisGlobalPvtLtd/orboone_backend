@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'data'])

@section('_content')
<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <form method="POST" id="editForm">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Customer</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="text" name="name" id="edit_name" class="form-control mb-2" required placeholder="Name">
          <input type="email" name="email" id="edit_email" class="form-control mb-2" placeholder="Email">
          <input type="text" name="phone" id="edit_phone" class="form-control mb-2" placeholder="Phone">
          <input type="text" name="gst_number" id="edit_gst_number" class="form-control mb-2" placeholder="GST Number">
          <input type="text" name="pan_card" id="edit_pan_card" class="form-control mb-2" placeholder="PAN Card">
          <input type="text" name="display_name" id="edit_display_name" class="form-control mb-2" placeholder="Display Name">
          <input type="text" name="company_name" id="edit_company_name" class="form-control mb-2" placeholder="Company Name">
          <textarea name="address" id="edit_address" class="form-control mb-2" placeholder="Address"></textarea>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Customer Details</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body" id="modal-body-content">
        <div class="table-responsive">
          <table class="table table-bordered"></table>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Page Content -->
<div class="container-fluid mt-2 px-4">
  <div class="row">
    <div class="col-12">
      <h4 class="font-weight-bold">Customer's Data</h4>
     
      <hr>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-3">
      <div class="bg-light text-dark card p-3 overflow-auto">
        <div class="d-flex justify-content-between">
          <a href="{{ route('pages.customer_add') }}" class="btn btn-outline-dark mb-3 w-25">
            <i class="fas fa-plus mr-1"></i> Add customer
          </a>
          <a href="{{ route('pages.customer_print') }}" class="btn btn-outline-dark mb-3 w-25" target="_blank">
            <i class="fas fa-print mr-1"></i> Print
          </a>
        </div>
      </div>

      @if (session('success'))
      <div class="alert alert-success mt-3">
        {{ session('success') }}
      </div>
      @endif
      <br>
      <form method="GET" action="{{ route('customer_data') }}" class="mb-3">
        <input type="text" name="search" value="{{ request()->get('search') }}" class="form-control" placeholder="Search by Name">
      </form>
      <table class="table table-light table-striped table-hover table-bordered text-center">
        <thead>
          <tr>
            <th class="table-dark">ID</th>
            <th class="table-dark">Name</th>
            <th class="table-dark">Email</th>
            <th class="table-dark">Phone</th>
            <th class="table-dark">GST</th>
            <th class="table-dark">PAN</th>
            <th class="table-dark">Display Name</th>
            <th class="table-dark">Company</th>
            <th class="table-dark">Address</th>
            <th class="table-dark">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($customer_data as $customer)
          <tr>
            <td>{{ $customer->id }}</td>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->email }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ $customer->gst_number }}</td>
            <td>{{ $customer->pan_card }}</td>
            <td>{{ $customer->display_name }}</td>
            <td>{{ $customer->company_name }}</td>
            <td>{{ $customer->address }}</td>
            <td>
                <button class="btn btn-info btn-sm view-btn"
                data-id="{{ $customer->id }}" data-toggle="modal" data-target="#viewModal">
                View
              </button>
              

              <button class="btn btn-warning btn-sm edit-btn"
                data-id="{{ $customer->id }}" data-toggle="modal" data-target="#editModal">Edit</button>

              <form method="POST" action="{{ route('customers.destroy', $customer->id) }}" class="d-inline" onsubmit="return confirm('Are you sure?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <div class="d-flex justify-content-center">
        {{ $customer_data->withQueryString()->links() }}
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script>
$(document).ready(function () {
  $('.view-btn').on('click', function () {
    const data = $(this).data();
    const content = `
      <table class="table">
        <tr><td><strong>Name</strong></td><td>${data.name}</td></tr>
        <tr><td><strong>Email</strong></td><td>${data.email}</td></tr>
        <tr><td><strong>Phone</strong></td><td>${data.phone}</td></tr>
        <tr><td><strong>GST</strong></td><td>${data.gst_number}</td></tr>
        <tr><td><strong>PAN</strong></td><td>${data.pan_card}</td></tr>
        <tr><td><strong>Display Name</strong></td><td>${data.display_name}</td></tr>
        <tr><td><strong>Company Name</strong></td><td>${data.company_name}</td></tr>
        <tr><td><strong>Address</strong></td><td>${data.address}</td></tr>
      </table>`;
    $('#modal-body-content').html(content);
  });

  $('.edit-btn').on('click', function () {
    const id = $(this).data('id');
    $.get(`/customers/${id}`, function (data) {
      $('#editForm').attr('action', `/customers/${id}`);
      $('#edit_name').val(data.name);
      $('#edit_email').val(data.email);
      $('#edit_phone').val(data.phone);
      $('#edit_gst_number').val(data.gst_number);
      $('#edit_pan_card').val(data.pan_card);
      $('#edit_display_name').val(data.display_name);
      $('#edit_company_name').val(data.company_name);
      $('#edit_address').val(data.address);
    });
  });
});

$(document).ready(function () {

  // VIEW - Fetch & Display Modal
  $('.view-btn').on('click', function () {
    const id = $(this).data('id');
    $.get(`/customers/${id}`, function (data) {
      const content = `
        <table class="table">
          <tr><td><strong>Name</strong></td><td>${data.name}</td></tr>
          <tr><td><strong>Email</strong></td><td>${data.email}</td></tr>
          <tr><td><strong>Phone</strong></td><td>${data.phone}</td></tr>
          <tr><td><strong>GST</strong></td><td>${data.gst_number}</td></tr>
          <tr><td><strong>PAN</strong></td><td>${data.pan_card}</td></tr>
          <tr><td><strong>Display Name</strong></td><td>${data.display_name}</td></tr>
          <tr><td><strong>Company Name</strong></td><td>${data.company_name}</td></tr>
          <tr><td><strong>Address</strong></td><td>${data.address}</td></tr>
        </table>`;
      $('#modal-body-content').html(content);
    });
  });

  // EDIT - Fetch & Populate Form
  $('.edit-btn').on('click', function () {
    const id = $(this).data('id');
    $.get(`/customers/${id}`, function (data) {
      $('#editForm').attr('action', `/customers/${id}`);
      $('#edit_name').val(data.name);
      $('#edit_email').val(data.email);
      $('#edit_phone').val(data.phone);
      $('#edit_gst_number').val(data.gst_number);
      $('#edit_pan_card').val(data.pan_card);
      $('#edit_display_name').val(data.display_name);
      $('#edit_company_name').val(data.company_name);
      $('#edit_address').val(data.address);
    });
  });
});
</script>

@endsection
