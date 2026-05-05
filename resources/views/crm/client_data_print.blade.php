@extends('layouts.print')

@section('_content')

<div class="container-fluid mt-2 p-4">
  <div class="row">
    <div class="col-12 text-center">
        <h4 class="font-weight-bold">'Client' Data Print</h4>
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
              <th scope="col" class="table-dark">Cleint Name</th>
              <th scope="col" class="table-dark">Mobile No</th>
              <th scope="col" class="table-dark">Email Id</th>
              <th scope="col" class="table-dark">Bank Account No</th>
              <th scope="col" class="table-dark">IFCE Code</th>
              <th scope="col" class="table-dark">Bank Name Branch</th>
              <th scope="col" class="table-dark">GST In</th>
              <th scope="col" class="table-dark">PAN</th>
              <th scope="col" class="table-dark">Aadhar No</th>
              <th scope="col" class="table-dark">Firm Name</th>
              <th scope="col" class="table-dark">Gst login Id</th>
              <th scope="col" class="table-dark">Gst login Password</th>
              <th scope="col" class="table-dark">Income Tax login Id</th>
              <th scope="col" class="table-dark">Income Tax login Password</th>
              <th scope="col" class="table-dark">E way Bill Id</th>
              <th scope="col" class="table-dark">Eway Bill Password</th>
              <th scope="col" class="table-dark">E Invoice Id</th>
              <th scope="col" class="table-dark">E Invoice Password</th>

            </tr>
          </thead>
          <tbody>
            @foreach ($client_data as $client_data)
            <tr>
              <td>{{ $client_data->id }}</td>
              <td>{{ $client_data->cleint_name }}</td>
              <td>{{ $client_data->mobile_no }}</td>
              <td>{{ $client_data->email_id }}</td>
              <td>{{ $client_data->bank_account_no }}</td>
              <td>{{ $client_data->ifce_code }}</td>
              <td>{{ $client_data->bank_name_branch }}</td>
              <td>{{ $client_data->gst_in }}</td>
              <td>{{ $client_data->pan }}</td>
              <td>{{ $client_data->aadhar }}</td>
              <td>{{ $client_data->firm_name }}</td>
              <td>{{ $client_data->gst_login_id }}</td>
              <td>{{ $client_data->gst_login_password }}</td>
              <td>{{ $client_data->income_tax_login_id }}</td>
              <td>{{ $client_data->income_tax_login_password }}</td>
              <td>{{ $client_data->e_way_bill_id }}</td>
              <td>{{ $client_data->e_way_bill_password }}</td>
              <td>{{ $client_data->e_invoice_id }}</td>
              <td>{{ $client_data->e_invoice_password }}</td>
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