@extends('layouts.print')

@section('_content')

<div class="container-fluid mt-2 p-4">
  <div class="row">
    <div class="col-12 text-center">
        <h4 class="font-weight-bold">'Customer' Data Print</h4>
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
                <th scope="col" class="table-dark"> Name</th>
                <th scope="col" class="table-dark">Email</th>
                <th scope="col" class="table-dark">Phone</th>
                <th scope="col" class="table-dark">GST Number</th>
                <th scope="col" class="table-dark">Pan Card</th>
                <th scope="col" class="table-dark">Company Name</th>
                <th scope="col" class="table-dark">Address</th>
                
               
                <th scope="col" class="table-dark">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($customer_data as $customer_data)
              <tr>
                <td>{{ $customer_data->id }}</td>
                <td>{{ $customer_data->name }}</td>
                <td>{{ $customer_data->email }}</td>
                <td>{{ $customer_data->phone }}</td>
                <td>{{ $customer_data->gst_number }}</td>
                <td>{{ $customer_data->pan_card }}</td>
                <td>{{ $customer_data->display_name }}</td>
                <td>{{ $customer_data->company_name }}</td>
                <td>{{ $customer_data->address }}</td>
                <td>
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